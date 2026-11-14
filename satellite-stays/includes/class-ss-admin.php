<?php
/**
 * wp-admin screens for Satellite Stays.
 *
 * Security on every request: capability check (ss_manage, or manage_options
 * for Settings), nonce check, sanitised input, escaped output.
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

class SS_Admin {

	const CAP = 'ss_manage';

	const STATUSES = array(
		'pending'     => 'Request',
		'confirmed'   => 'Confirmed',
		'checked_in'  => 'Checked in',
		'checked_out' => 'Checked out',
		'cancelled'   => 'Cancelled',
	);

	const CHANNELS = array(
		'website'  => 'Website',
		'whatsapp' => 'WhatsApp',
		'phone'    => 'Phone call',
		'email'    => 'Email',
		'walk-in'  => 'Walk-in',
		'agent'    => 'Tour agent',
	);

	const KEEP_DAYS = 365;
	const CALENDAR_DAYS = 14;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		foreach ( array( 'booking_save', 'booking_status', 'booking_delete', 'type_save', 'type_delete', 'unit_save', 'unit_delete', 'settings_save', 'purge' ) as $action ) {
			add_action( 'admin_post_ss_' . $action, array( $this, $action ) );
		}
	}

	public function menu() {
		add_menu_page( 'Bookings', 'Bookings', self::CAP, 'ss-overview', array( $this, 'page_overview' ), 'dashicons-palmtree', 26 );
		add_submenu_page( 'ss-overview', 'Overview', 'Overview', self::CAP, 'ss-overview', array( $this, 'page_overview' ) );
		add_submenu_page( 'ss-overview', 'All bookings', 'All bookings', self::CAP, 'ss-bookings', array( $this, 'page_bookings' ) );
		add_submenu_page( 'ss-overview', 'Accommodation', 'Accommodation', self::CAP, 'ss-accommodation', array( $this, 'page_types' ) );
		add_submenu_page( 'ss-overview', 'Rooms & cottages', 'Rooms & cottages', self::CAP, 'ss-units', array( $this, 'page_units' ) );
		add_submenu_page( 'ss-overview', 'Settings', 'Settings', 'manage_options', 'ss-settings', array( $this, 'page_settings' ) );
	}

	public function assets( $hook ) {
		if ( false !== strpos( $hook, 'ss-' ) ) {
			wp_enqueue_style( 'ss-admin', SS_URL . 'assets/admin.css', array(), SS_VERSION );
		}
	}

	/* =================================================================
	 * Bookings: handlers
	 * ================================================================= */

	public function booking_save() {
		$this->guard( 'ss_booking_save' );
		$id = absint( $_POST['id'] ?? 0 );

		$d = array(
			'type_id'          => absint( $_POST['type_id'] ?? 0 ),
			'unit_id'          => absint( $_POST['unit_id'] ?? 0 ),
			'guest_name'       => mb_substr( $this->text( 'guest_name' ), 0, 120 ),
			'phone'            => mb_substr( preg_replace( '/[^0-9+\s-]/', '', $this->text( 'phone' ) ), 0, 30 ),
			'email'            => mb_substr( sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ), 0, 120 ),
			'check_in'         => $this->date( 'check_in' ),
			'check_out'        => $this->date( 'check_out' ),
			'adults'           => min( 50, max( 1, absint( $_POST['adults'] ?? 1 ) ) ),
			'children'         => min( 50, absint( $_POST['children'] ?? 0 ) ),
			'channel'          => $this->pick( 'channel', self::CHANNELS, 'phone' ),
			'status'           => $this->pick( 'status', self::STATUSES, 'confirmed' ),
			'special_requests' => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['special_requests'] ?? '' ) ), 0, 600 ),
			'staff_note'       => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['staff_note'] ?? '' ) ), 0, 600 ),
		);

		$type = $d['type_id'] ? SS_DB::get_type( $d['type_id'] ) : null;
		if ( ! $type || '' === $d['guest_name'] || '' === $d['phone'] || ! $d['check_in'] || ! $d['check_out'] ) {
			$this->back( 'ss-bookings', 'required', $id );
		}
		if ( $d['check_out'] <= $d['check_in'] ) {
			$this->back( 'ss-bookings', 'dates', $id );
		}

		$holding = in_array( $d['status'], array( 'pending', 'confirmed', 'checked_in' ), true );
		if ( $d['unit_id'] ) {
			$unit = SS_DB::get_unit( $d['unit_id'] );
			if ( ! $unit || (int) $unit->type_id !== (int) $type->id ) {
				$this->back( 'ss-bookings', 'unit_type', $id );
			}
			if ( $holding && SS_DB::unit_taken( $d['unit_id'], $d['check_in'], $d['check_out'], $id ) ) {
				$this->back( 'ss-bookings', 'unit_taken', $id );
			}
		}
		if ( $holding && SS_DB::free_count( $type->id, $d['check_in'], $d['check_out'], $id ) < 1 ) {
			$this->back( 'ss-bookings', 'full', $id );
		}

		$before = $id ? SS_DB::get_booking( $id ) : null;
		$result = $id ? SS_DB::update_booking( $id, $d ) : SS_DB::insert_booking( $d );
		if ( false === $result ) {
			$this->back( 'ss-bookings', 'error', $id );
		}
		if ( $before && $before->status !== $d['status'] && in_array( $d['status'], array( 'confirmed', 'cancelled' ), true ) ) {
			SS_Public::email_guest( SS_DB::get_booking( $id ), $d['status'] );
		}
		$this->back( 'ss-bookings', $id ? 'updated' : 'added' );
	}

	/** One-click workflow: confirm (assigns a free room if none), check in, check out, cancel. */
	public function booking_status() {
		$this->guard_get();
		$id   = absint( $_GET['id'] ?? 0 );
		$to   = sanitize_key( $_GET['to'] ?? '' );
		$page = sanitize_key( $_GET['back'] ?? 'ss-bookings' );
		$page = in_array( $page, array( 'ss-overview', 'ss-bookings' ), true ) ? $page : 'ss-bookings';
		check_admin_referer( 'ss_status_' . $id . '_' . $to );

		$b = SS_DB::get_booking( $id );
		if ( ! $b || ! isset( self::STATUSES[ $to ] ) ) {
			$this->back( $page, 'error' );
		}

		$unit = null;
		if ( in_array( $to, array( 'confirmed', 'checked_in' ), true ) && ! $b->unit_id ) {
			$free = SS_DB::free_units( $b->type_id, $b->check_in, $b->check_out, $id );
			if ( empty( $free ) ) {
				$this->back( $page, 'no_unit' );
			}
			$unit = (int) $free[0]->id;
		}
		SS_DB::set_booking_status( $id, $to, $unit );

		$emailed = false;
		if ( in_array( $to, array( 'confirmed', 'cancelled' ), true ) ) {
			$emailed = SS_Public::email_guest( SS_DB::get_booking( $id ), $to );
		}
		$this->back( $page, $emailed ? 'emailed' : $to );
	}

	public function booking_delete() {
		$this->guard_get();
		$id = absint( $_GET['id'] ?? 0 );
		check_admin_referer( 'ss_booking_delete_' . $id );
		$this->back( 'ss-bookings', false === SS_DB::delete_booking( $id ) ? 'error' : 'deleted' );
	}

	/* =================================================================
	 * Accommodation types and units: handlers
	 * ================================================================= */

	public function type_save() {
		$this->guard( 'ss_type_save' );
		$id    = absint( $_POST['id'] ?? 0 );
		$price = trim( $this->text( 'price_per_night' ) );
		$d     = array(
			'name'            => mb_substr( $this->text( 'name' ), 0, 120 ),
			'description'     => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ), 0, 600 ),
			'max_guests'      => min( 100, max( 1, absint( $_POST['max_guests'] ?? 2 ) ) ),
			'price_per_night' => '' === $price ? '' : (string) max( 0, round( (float) $price, 2 ) ),
			'image'           => mb_substr( sanitize_file_name( $this->text( 'image' ) ), 0, 255 ),
			'sort_order'      => (int) ( $_POST['sort_order'] ?? 0 ),
			'is_active'       => empty( $_POST['is_active'] ) ? 0 : 1,
		);
		if ( '' === $d['name'] ) {
			$this->back( 'ss-accommodation', 'required', $id );
		}
		$this->quiet();
		$result = $id ? SS_DB::update_type( $id, $d ) : SS_DB::insert_type( $d );
		$this->back( 'ss-accommodation', false === $result ? 'duplicate' : ( $id ? 'updated' : 'added' ), false === $result ? $id : 0 );
	}

	public function type_delete() {
		$this->guard_get();
		$id = absint( $_GET['id'] ?? 0 );
		check_admin_referer( 'ss_type_delete_' . $id );
		$this->quiet();
		$this->back( 'ss-accommodation', false === SS_DB::delete_type( $id ) ? 'type_used' : 'deleted' );
	}

	public function unit_save() {
		$this->guard( 'ss_unit_save' );
		$id = absint( $_POST['id'] ?? 0 );
		$d  = array(
			'type_id'   => absint( $_POST['type_id'] ?? 0 ),
			'name'      => mb_substr( $this->text( 'name' ), 0, 80 ),
			'is_active' => empty( $_POST['is_active'] ) ? 0 : 1,
			'notes'     => mb_substr( $this->text( 'notes' ), 0, 255 ),
		);
		if ( '' === $d['name'] || ! SS_DB::get_type( $d['type_id'] ) ) {
			$this->back( 'ss-units', 'required', $id );
		}
		$this->quiet();
		$result = $id ? SS_DB::update_unit( $id, $d ) : SS_DB::insert_unit( $d );
		$this->back( 'ss-units', false === $result ? 'duplicate' : ( $id ? 'updated' : 'added' ), false === $result ? $id : 0 );
	}

	public function unit_delete() {
		$this->guard_get();
		$id = absint( $_GET['id'] ?? 0 );
		check_admin_referer( 'ss_unit_delete_' . $id );
		$this->back( 'ss-units', false === SS_DB::delete_unit( $id ) ? 'error' : 'deleted' );
	}

	/* =================================================================
	 * Settings
	 * ================================================================= */

	public function settings_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-stays' ), 403 );
		}
		check_admin_referer( 'ss_settings_save' );
		$email = sanitize_email( wp_unslash( $_POST['notify_email'] ?? '' ) );
		$time  = function ( $key, $fallback ) {
			$v = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
			return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $v ) ? $v : $fallback;
		};
		update_option(
			'ss_settings',
			array(
				'notify_email'   => is_email( $email ) ? $email : ss_setting( 'notify_email' ),
				'whatsapp'       => ss_whatsapp_number( $this->text( 'whatsapp' ) ),
				'max_nights'     => min( 90, max( 1, absint( $_POST['max_nights'] ?? 30 ) ) ),
				'bookings_open'  => empty( $_POST['bookings_open'] ) ? 0 : 1,
				'check_in_time'  => $time( 'check_in_time', '14:00' ),
				'check_out_time' => $time( 'check_out_time', '10:00' ),
			)
		);
		$this->back( 'ss-settings', 'saved' );
	}

	public function purge() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-stays' ), 403 );
		}
		check_admin_referer( 'ss_purge' );
		SS_DB::purge( self::KEEP_DAYS );
		$this->back( 'ss-settings', 'purged' );
	}

	/* =================================================================
	 * Pages
	 * ================================================================= */

	public function page_overview() {
		$s        = SS_DB::stats();
		$requests = SS_DB::get_bookings( 'requests' );
		?>
		<div class="wrap ss">
			<h1>Bookings overview</h1>
			<?php $this->notice(); ?>
			<div class="ss-cards">
				<?php
				$this->card( 'New requests to confirm', (int) $s->requests, (int) $s->requests ? 'warn' : '' );
				$this->card( 'Arriving today', (int) $s->arrivals );
				$this->card( 'Leaving today', (int) $s->departures );
				$this->card( 'Guests staying now', (int) $s->in_house );
				$this->card( 'Occupied tonight', (int) $s->occupied_tonight . ' of ' . (int) $s->units );
				?>
			</div>

			<h2>Next <?php echo (int) self::CALENDAR_DAYS; ?> days</h2>
			<?php $this->calendar(); ?>

			<h2>Requests waiting for confirmation</h2>
			<?php $this->bookings_table( $requests, 'ss-overview' ); ?>
		</div>
		<?php
	}

	/** Occupancy grid: one row per room/cottage, one column per night. */
	private function calendar() {
		$start = current_time( 'Y-m-d' );
		$days  = array();
		for ( $i = 0; $i < self::CALENDAR_DAYS; $i++ ) {
			$days[] = gmdate( 'Y-m-d', strtotime( $start . " +$i days" ) );
		}
		$end      = gmdate( 'Y-m-d', strtotime( $start . ' +' . self::CALENDAR_DAYS . ' days' ) );
		$bookings = SS_DB::bookings_between( $start, $end );
		$units    = array_filter( SS_DB::get_units(), fn( $u ) => (int) $u->is_active );
		$types    = array();
		foreach ( $units as $u ) {
			$types[ $u->type_name ][] = $u;
		}
		?>
		<div class="ss-cal-wrap">
			<table class="ss-cal">
				<thead>
					<tr>
						<th scope="col">Room / cottage</th>
						<?php foreach ( $days as $d ) : ?>
							<th scope="col" class="<?php echo in_array( gmdate( 'N', strtotime( $d ) ), array( '6', '7' ), true ) ? 'is-weekend' : ''; ?>"><?php echo esc_html( gmdate( 'D', strtotime( $d ) ) ); ?><br><strong><?php echo esc_html( gmdate( 'j', strtotime( $d ) ) ); ?></strong></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $types as $type_name => $type_units ) : ?>
					<tr class="ss-cal-group"><th colspan="<?php echo count( $days ) + 1; ?>"><?php echo esc_html( $type_name ); ?></th></tr>
					<?php
					$rows = array();
					foreach ( $type_units as $u ) {
						$rows[] = array( $u->name, fn( $b ) => (int) $b->unit_id === (int) $u->id );
					}
					$type_id   = (int) $type_units[0]->type_id;
					$has_unass = (bool) array_filter( $bookings, fn( $b ) => ! $b->unit_id && (int) $b->type_id === $type_id );
					if ( $has_unass ) {
						$rows[] = array( 'Not yet assigned', fn( $b ) => ! $b->unit_id && (int) $b->type_id === $type_id );
					}
					foreach ( $rows as $row ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $row[0] ); ?></th>
							<?php foreach ( $days as $d ) : ?>
								<?php
								$hit = null;
								foreach ( $bookings as $b ) {
									if ( $row[1]( $b ) && $b->check_in <= $d && $b->check_out > $d ) {
										$hit = $b;
										break;
									}
								}
								?>
								<?php if ( $hit ) : ?>
									<td class="ss-cal-<?php echo esc_attr( $hit->status ); ?>" title="<?php echo esc_attr( ss_reference( $hit->id ) . ': ' . $hit->guest_name . ' (' . self::STATUSES[ $hit->status ] . ')' ); ?>">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-bookings&action=edit&id=' . (int) $hit->id ) ); ?>"><?php echo ( $hit->check_in === $d || $start === $d ) ? esc_html( wp_html_excerpt( $hit->guest_name, 10, '…' ) ) : '&nbsp;'; ?></a>
									</td>
								<?php else : ?>
									<td></td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="ss-legend"><span class="ss-key ss-cal-pending"></span> Request (room held) <span class="ss-key ss-cal-confirmed"></span> Confirmed <span class="ss-key ss-cal-checked_in"></span> Checked in</p>
		<?php
	}

	public function page_bookings() {
		$editing = $this->editing( 'booking' );
		$views   = array(
			'upcoming'   => 'Upcoming',
			'requests'   => 'Requests',
			'arrivals'   => 'Arriving today',
			'in_house'   => 'Staying now',
			'departures' => 'Leaving today',
			'past'       => 'Past',
			'all'        => 'All',
		);
		$view    = isset( $_GET['view'], $views[ $_GET['view'] ] ) ? sanitize_key( $_GET['view'] ) : 'upcoming';
		$types   = SS_DB::get_types();
		$units   = SS_DB::get_units();
		$base    = admin_url( 'admin.php?page=ss-bookings' );
		?>
		<div class="wrap ss">
			<h1>All bookings</h1>
			<?php $this->notice(); ?>
			<p class="description">Website requests arrive here automatically. Add bookings made by WhatsApp, phone, email, walk-in or tour agents with the form, so every stay is in one list and the online availability stays correct.</p>
			<div class="ss-layout">
				<div class="ss-form">
					<h2><?php echo $editing ? esc_html( 'Edit ' . ss_reference( $editing->id ) ) : 'Add booking'; ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ss_booking_save">
						<input type="hidden" name="id" value="<?php echo (int) ( $editing->id ?? 0 ); ?>">
						<?php wp_nonce_field( 'ss_booking_save' ); ?>
						<p><label for="ss-type_id">Accommodation *</label>
							<select id="ss-type_id" name="type_id" required>
								<option value="">— Select —</option>
								<?php foreach ( $types as $t ) : ?>
									<option value="<?php echo (int) $t->id; ?>" <?php selected( (int) ( $editing->type_id ?? 0 ), (int) $t->id ); ?>><?php echo esc_html( $t->name ); ?></option>
								<?php endforeach; ?>
							</select></p>
						<p><label for="ss-unit_id">Room / cottage</label>
							<select id="ss-unit_id" name="unit_id">
								<option value="0">Assign later</option>
								<?php foreach ( $units as $u ) : ?>
									<option value="<?php echo (int) $u->id; ?>" <?php selected( (int) ( $editing->unit_id ?? 0 ), (int) $u->id ); ?>><?php echo esc_html( $u->name . ' (' . $u->type_name . ')' ); ?></option>
								<?php endforeach; ?>
							</select></p>
						<?php
						$this->field( 'guest_name', 'Guest name *', $editing->guest_name ?? '', 'text', true );
						$this->field( 'phone', 'Phone / WhatsApp *', $editing->phone ?? '', 'tel', true );
						$this->field( 'email', 'Email', $editing->email ?? '', 'email' );
						$this->field( 'check_in', 'Check-in *', $editing->check_in ?? current_time( 'Y-m-d' ), 'date', true );
						$this->field( 'check_out', 'Check-out *', $editing->check_out ?? gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' +1 day' ) ), 'date', true );
						$this->field( 'adults', 'Adults', $editing->adults ?? 2, 'number' );
						$this->field( 'children', 'Children', $editing->children ?? 0, 'number' );
						$this->select( 'channel', 'Booked through', self::CHANNELS, $editing->channel ?? 'whatsapp' );
						$this->select( 'status', 'Status', self::STATUSES, $editing->status ?? 'confirmed' );
						?>
						<p><label for="ss-special_requests">Guest's requests</label>
							<textarea id="ss-special_requests" name="special_requests" rows="2" maxlength="600"><?php echo esc_textarea( $editing->special_requests ?? '' ); ?></textarea></p>
						<p><label for="ss-staff_note">Staff note (private)</label>
							<textarea id="ss-staff_note" name="staff_note" rows="2" maxlength="600"><?php echo esc_textarea( $editing->staff_note ?? '' ); ?></textarea></p>
						<?php if ( $editing && $editing->email ) : ?>
							<p class="description">Changing the status to Confirmed or Cancelled emails the guest.</p>
						<?php endif; ?>
						<?php submit_button( $editing ? 'Save changes' : 'Add booking', 'primary', 'submit', false ); ?>
						<?php if ( $editing ) : ?>
							<a class="button" href="<?php echo esc_url( $base ); ?>">Cancel</a>
						<?php endif; ?>
					</form>
				</div>
				<div class="ss-list">
					<ul class="subsubsub">
						<?php
						$out = array();
						foreach ( $views as $key => $label ) {
							$out[] = sprintf( '<li><a href="%s" class="%s">%s</a>', esc_url( add_query_arg( 'view', $key, $base ) ), $view === $key ? 'current' : '', esc_html( $label ) );
						}
						echo implode( ' |</li>', $out ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
						?>
					</ul>
					<?php $this->bookings_table( SS_DB::get_bookings( $view ), 'ss-bookings' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function bookings_table( $rows, $back ) {
		?>
		<table class="widefat striped ss-bookings">
			<thead><tr><th>Stay</th><th>Guest</th><th>Accommodation</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="5">No bookings here.</td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $r ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( gmdate( 'D j M', strtotime( $r->check_in ) ) ); ?></strong> → <?php echo esc_html( gmdate( 'D j M', strtotime( $r->check_out ) ) ); ?><br>
						<small><?php echo (int) $r->nights; ?> nights · <?php echo (int) $r->adults; ?> adults<?php echo (int) $r->children ? ', ' . (int) $r->children . ' children' : ''; ?></small><br>
						<small><code><?php echo esc_html( ss_reference( $r->id ) ); ?></code> <?php echo esc_html( self::CHANNELS[ $r->channel ] ?? $r->channel ); ?></small>
					</td>
					<td>
						<strong><?php echo esc_html( $r->guest_name ); ?></strong><br>
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $r->phone ) ); ?>"><?php echo esc_html( $r->phone ); ?></a>
						· <a href="<?php echo esc_url( SS_Public::wa_link( 'Hello ' . $r->guest_name . ', this is Satellite Holiday Resort about your booking ' . ss_reference( $r->id ) . '.', ss_whatsapp_number( $r->phone ) ) ); ?>" target="_blank" rel="noopener">WhatsApp</a>
						<?php if ( $r->email ) : ?>
							<br><a href="<?php echo esc_url( 'mailto:' . $r->email ); ?>"><?php echo esc_html( $r->email ); ?></a>
						<?php endif; ?>
						<?php if ( $r->special_requests ) : ?>
							<br><small><em><?php echo esc_html( $r->special_requests ); ?></em></small>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $r->type_name ); ?><br><small><?php echo $r->unit_name ? esc_html( $r->unit_name ) : 'Room not assigned'; ?></small><br><small><?php echo esc_html( null === $r->price_per_night ? 'Rate on request' : ss_money( $r->price_per_night * $r->nights ) ); ?></small></td>
					<td><span class="ss-badge ss-st-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( self::STATUSES[ $r->status ] ?? $r->status ); ?></span></td>
					<td class="ss-actions"><?php $this->row_actions( $r, $back ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	private function row_actions( $r, $back ) {
		$next = array(
			'pending'    => array( 'confirmed' => 'Confirm', 'cancelled' => 'Cancel' ),
			'confirmed'  => array( 'checked_in' => 'Check in', 'cancelled' => 'Cancel' ),
			'checked_in' => array( 'checked_out' => 'Check out' ),
		);
		$links = array();
		foreach ( $next[ $r->status ] ?? array() as $to => $label ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_booking_status&id=' . (int) $r->id . '&to=' . $to . '&back=' . $back ), 'ss_status_' . (int) $r->id . '_' . $to ) ),
				esc_html( $label )
			);
		}
		$links[] = sprintf( '<a href="%s">Edit</a>', esc_url( admin_url( 'admin.php?page=ss-bookings&action=edit&id=' . (int) $r->id ) ) );
		$links[] = sprintf(
			'<a class="ss-delete" href="%s" onclick="return confirm(\'Delete this booking?\');">Delete</a>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_booking_delete&id=' . (int) $r->id ), 'ss_booking_delete_' . (int) $r->id ) )
		);
		echo implode( ' | ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	}

	public function page_types() {
		$editing = $this->editing( 'type' );
		$rows    = SS_DB::get_types();
		?>
		<div class="wrap ss">
			<h1>Accommodation</h1>
			<?php $this->notice(); ?>
			<p class="description">Each kind of stay guests can book. Add the individual rooms and cottages under <a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-units' ) ); ?>">Rooms &amp; cottages</a>; the number of active ones is how many can be booked at once.</p>
			<div class="ss-layout">
				<div class="ss-form">
					<h2><?php echo $editing ? 'Edit accommodation' : 'Add accommodation'; ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ss_type_save">
						<input type="hidden" name="id" value="<?php echo (int) ( $editing->id ?? 0 ); ?>">
						<?php wp_nonce_field( 'ss_type_save' ); ?>
						<?php $this->field( 'name', 'Name *', $editing->name ?? '', 'text', true ); ?>
						<p><label for="ss-description">Short description</label>
							<textarea id="ss-description" name="description" rows="3" maxlength="600"><?php echo esc_textarea( $editing->description ?? '' ); ?></textarea></p>
						<?php
						$this->field( 'max_guests', 'Sleeps up to (guests)', $editing->max_guests ?? 2, 'number' );
						$this->field( 'price_per_night', 'Price per night in UGX (empty = "Rates on request")', isset( $editing->price_per_night ) ? (float) $editing->price_per_night : '', 'number' );
						$this->field( 'image', 'Photo file name in the Media Library (e.g. cottage.jpg)', $editing->image ?? '' );
						$this->field( 'sort_order', 'Display order', $editing->sort_order ?? 0, 'number' );
						?>
						<p><label><input type="checkbox" name="is_active" value="1" <?php checked( (int) ( $editing->is_active ?? 1 ), 1 ); ?>> Bookable on the website</label></p>
						<?php submit_button( $editing ? 'Save changes' : 'Add', 'primary', 'submit', false ); ?>
						<?php if ( $editing ) : ?>
							<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ss-accommodation' ) ); ?>">Cancel</a>
						<?php endif; ?>
					</form>
				</div>
				<div class="ss-list">
					<table class="widefat striped">
						<thead><tr><th>Accommodation</th><th>Sleeps</th><th>Per night</th><th>Rooms</th><th>Bookings</th><th>Website</th><th></th></tr></thead>
						<tbody>
						<?php foreach ( $rows as $r ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $r->name ); ?></strong><br><small><?php echo esc_html( $r->description ); ?></small></td>
								<td><?php echo (int) $r->max_guests; ?></td>
								<td><?php echo esc_html( ss_money( $r->price_per_night ) ); ?></td>
								<td><?php echo (int) $r->unit_count; ?></td>
								<td><?php echo (int) $r->booking_count; ?></td>
								<td><?php echo (int) $r->is_active ? 'Yes' : '<span class="ss-badge ss-st-cancelled">Hidden</span>'; ?></td>
								<td class="ss-actions">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-accommodation&action=edit&id=' . (int) $r->id ) ); ?>">Edit</a> |
									<a class="ss-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_type_delete&id=' . (int) $r->id ), 'ss_type_delete_' . (int) $r->id ) ); ?>" onclick="return confirm('Delete this accommodation type?');">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function page_units() {
		$editing = $this->editing( 'unit' );
		$rows    = SS_DB::get_units();
		$types   = SS_DB::get_types();
		?>
		<div class="wrap ss">
			<h1>Rooms &amp; cottages</h1>
			<?php $this->notice(); ?>
			<p class="description">Add every room, cottage, tent pitch or house the resort lets. Untick "In use" during repairs so it can't be booked.</p>
			<div class="ss-layout">
				<div class="ss-form">
					<h2><?php echo $editing ? 'Edit' : 'Add room / cottage'; ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ss_unit_save">
						<input type="hidden" name="id" value="<?php echo (int) ( $editing->id ?? 0 ); ?>">
						<?php wp_nonce_field( 'ss_unit_save' ); ?>
						<?php $this->field( 'name', 'Name * (e.g. Cottage 3)', $editing->name ?? '', 'text', true ); ?>
						<p><label for="ss-unit-type">Accommodation *</label>
							<select id="ss-unit-type" name="type_id" required>
								<?php foreach ( $types as $t ) : ?>
									<option value="<?php echo (int) $t->id; ?>" <?php selected( (int) ( $editing->type_id ?? 0 ), (int) $t->id ); ?>><?php echo esc_html( $t->name ); ?></option>
								<?php endforeach; ?>
							</select></p>
						<?php $this->field( 'notes', 'Notes (e.g. lake view, ground floor)', $editing->notes ?? '' ); ?>
						<p><label><input type="checkbox" name="is_active" value="1" <?php checked( (int) ( $editing->is_active ?? 1 ), 1 ); ?>> In use (bookable)</label></p>
						<?php submit_button( $editing ? 'Save changes' : 'Add', 'primary', 'submit', false ); ?>
						<?php if ( $editing ) : ?>
							<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ss-units' ) ); ?>">Cancel</a>
						<?php endif; ?>
					</form>
				</div>
				<div class="ss-list">
					<table class="widefat striped">
						<thead><tr><th>Name</th><th>Accommodation</th><th>Notes</th><th>In use</th><th></th></tr></thead>
						<tbody>
						<?php foreach ( $rows as $r ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $r->name ); ?></strong></td>
								<td><?php echo esc_html( $r->type_name ); ?></td>
								<td><?php echo esc_html( (string) $r->notes ); ?></td>
								<td><?php echo (int) $r->is_active ? 'Yes' : '<span class="ss-badge ss-st-cancelled">Out of use</span>'; ?></td>
								<td class="ss-actions">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-units&action=edit&id=' . (int) $r->id ) ); ?>">Edit</a> |
									<a class="ss-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_unit_delete&id=' . (int) $r->id ), 'ss_unit_delete_' . (int) $r->id ) ); ?>" onclick="return confirm('Delete this room? Past bookings keep their record.');">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function page_settings() {
		?>
		<div class="wrap ss">
			<h1>Booking settings</h1>
			<?php $this->notice(); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ss-form" style="max-width:560px">
				<input type="hidden" name="action" value="ss_settings_save">
				<?php wp_nonce_field( 'ss_settings_save' ); ?>
				<?php
				$this->field( 'notify_email', 'Send new booking alerts to', ss_setting( 'notify_email' ), 'email', true );
				$this->field( 'whatsapp', 'Resort WhatsApp number', ss_setting( 'whatsapp' ), 'tel', true );
				$this->field( 'check_in_time', 'Check-in from', ss_setting( 'check_in_time' ), 'time' );
				$this->field( 'check_out_time', 'Check-out by', ss_setting( 'check_out_time' ), 'time' );
				$this->field( 'max_nights', 'Longest stay bookable online (nights)', ss_setting( 'max_nights' ), 'number' );
				?>
				<p><label><input type="checkbox" name="bookings_open" value="1" <?php checked( (int) ss_setting( 'bookings_open' ), 1 ); ?>> Accept booking requests on the website</label></p>
				<?php submit_button( 'Save settings', 'primary', 'submit', false ); ?>
			</form>
			<h2>Privacy</h2>
			<p>Bookings hold guests' names and phone numbers. Delete checked-out and cancelled bookings older than <?php echo (int) self::KEEP_DAYS; ?> days when you no longer need them.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Delete old finished bookings?');">
				<input type="hidden" name="action" value="ss_purge">
				<?php wp_nonce_field( 'ss_purge' ); ?>
				<?php submit_button( 'Delete old bookings', 'secondary', 'submit', false ); ?>
			</form>
			<h2>Website shortcodes</h2>
			<p><code>[ss_availability]</code> date search with results. <code>[ss_availability compact="1" action="/stay/"]</code> search box only. <code>[ss_booking_form]</code> booking request form.</p>
		</div>
		<?php
	}

	/* =================================================================
	 * Helpers
	 * ================================================================= */

	private function editing( $what ) {
		if ( ! isset( $_GET['action'], $_GET['id'] ) || 'edit' !== $_GET['action'] ) {
			return null;
		}
		$id = absint( $_GET['id'] );
		switch ( $what ) {
			case 'booking':
				return SS_DB::get_booking( $id );
			case 'type':
				return SS_DB::get_type( $id );
			case 'unit':
				return SS_DB::get_unit( $id );
		}
		return null;
	}

	private function field( $name, $label, $value, $type = 'text', $required = false ) {
		$extra = 'number' === $type ? ' min="0" step="' . ( 'price_per_night' === $name ? 'any' : '1' ) . '"' : '';
		printf(
			'<p><label for="ss-%1$s">%2$s</label><input class="regular-text" type="%3$s" id="ss-%1$s" name="%1$s" value="%4$s"%5$s%6$s></p>',
			esc_attr( $name ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( (string) $value ),
			$required ? ' required' : '',
			$extra // Fixed strings only.
		);
	}

	private function select( $name, $label, array $options, $current ) {
		printf( '<p><label for="ss-%1$s">%2$s</label><select id="ss-%1$s" name="%1$s">', esc_attr( $name ), esc_html( $label ) );
		foreach ( $options as $value => $text ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $text ) );
		}
		echo '</select></p>';
	}

	private function card( $label, $value, $tone = '' ) {
		printf( '<div class="ss-card %s"><span class="ss-card-value">%s</span><span class="ss-card-label">%s</span></div>', esc_attr( $tone ), esc_html( (string) $value ), esc_html( $label ) );
	}

	private function notice() {
		$messages = array(
			'added'      => array( 'success', 'Saved.' ),
			'updated'    => array( 'success', 'Changes saved.' ),
			'deleted'    => array( 'success', 'Deleted.' ),
			'required'   => array( 'error', 'Please fill in all required fields.' ),
			'dates'      => array( 'error', 'Check-out must be after check-in.' ),
			'duplicate'  => array( 'error', 'That name is already used. Choose another.' ),
			'type_used'  => array( 'error', 'This accommodation has rooms or bookings. Untick "Bookable" to hide it instead.' ),
			'unit_type'  => array( 'error', 'That room belongs to a different kind of accommodation.' ),
			'unit_taken' => array( 'error', 'That room is already booked for some of those nights. Choose another room or leave it to assign later.' ),
			'full'       => array( 'error', 'Every room of that kind is taken for those nights. Free a room or choose other dates.' ),
			'no_unit'    => array( 'error', 'No free room of that kind is left for those dates, so the booking could not be confirmed.' ),
			'confirmed'  => array( 'success', 'Booking confirmed and a room assigned.' ),
			'checked_in' => array( 'success', 'Guest checked in.' ),
			'checked_out' => array( 'success', 'Guest checked out.' ),
			'cancelled'  => array( 'success', 'Booking cancelled. The room is free again.' ),
			'emailed'    => array( 'success', 'Status updated and the guest was emailed.' ),
			'saved'      => array( 'success', 'Settings saved.' ),
			'purged'     => array( 'success', 'Old bookings deleted.' ),
			'error'      => array( 'error', 'Something went wrong. Please try again.' ),
		);
		$key = isset( $_GET['ss_msg'] ) ? sanitize_key( $_GET['ss_msg'] ) : '';
		if ( isset( $messages[ $key ] ) ) {
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $messages[ $key ][0] ), esc_html( $messages[ $key ][1] ) );
		}
	}

	/** Post/Redirect/Get back to a page with a message. */
	private function back( $page, $msg, $edit_id = 0 ) {
		$args = array(
			'page'   => $page,
			'ss_msg' => $msg,
		);
		if ( $edit_id ) {
			$args['action'] = 'edit';
			$args['id']     = $edit_id;
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	private function guard( $nonce_action ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-stays' ), 403 );
		}
		check_admin_referer( $nonce_action );
	}

	private function guard_get() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-stays' ), 403 );
		}
	}

	/** Show our own message instead of raw database errors (e.g. duplicate names). */
	private function quiet() {
		global $wpdb;
		$wpdb->suppress_errors( true );
	}

	private function text( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	}

	private function date( $key ) {
		$v = $this->text( $key );
		$d = DateTime::createFromFormat( '!Y-m-d', $v );
		return ( $d && $d->format( 'Y-m-d' ) === $v ) ? $v : '';
	}

	private function pick( $key, array $allowed, $fallback ) {
		$v = sanitize_key( $_POST[ $key ] ?? '' );
		return isset( $allowed[ $v ] ) ? $v : $fallback;
	}
}
