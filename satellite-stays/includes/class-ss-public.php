<?php
/**
 * Guest-facing side of Satellite Stays.
 *
 *  [ss_availability]  Date search. Shows each accommodation type with how
 *                     many are free for the whole stay, the price for the
 *                     stay (or "Rates on request") and a booking button.
 *                     compact="1" shows only the search box; action="/stay/"
 *                     sends the search to another page.
 *
 *  [ss_rooms]         Cards for every bookable accommodation type, read live
 *                     from the database (photo, guests, nightly rate).
 *
 *  [ss_booking_form]  Booking request. Saves to the bookings table, holds the
 *                     room while staff confirm, emails the resort and the guest.
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

class SS_Public {

	const RATE_LIMIT  = 4;   // Booking requests per visitor per hour.
	const DAYS_AHEAD  = 365; // Furthest check-in date accepted.
	const MAX_ADULTS  = 30;
	const MAX_CHILDREN = 20;

	private static $errors = array();
	private static $values = array();
	private static $images = array();

	public static function init() {
		add_shortcode( 'ss_availability', array( __CLASS__, 'availability' ) );
		add_shortcode( 'ss_booking_form', array( __CLASS__, 'booking_form' ) );
		add_shortcode( 'ss_rooms', array( __CLASS__, 'rooms' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_booking' ) );
	}

	public static function register_assets() {
		wp_register_style( 'ss-public', SS_URL . 'assets/public.css', array(), SS_VERSION );
		wp_register_script(
			'ss-calendar',
			SS_URL . 'assets/calendar.js',
			array(),
			SS_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_register_script(
			'ss-dates',
			SS_URL . 'assets/dates.js',
			array(),
			SS_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/* =================================================================
	 * Availability search
	 * ================================================================= */

	public static function availability( $atts ) {
		$atts = shortcode_atts(
			array(
				'compact'      => '0',
				'action'       => '',
				'booking_page' => '/book-your-stay/',
			),
			$atts,
			'ss_availability'
		);
		wp_enqueue_style( 'ss-public' );
		wp_enqueue_script( 'ss-dates' );

		$q       = self::query_from_get();
		$compact = '1' === (string) $atts['compact'];
		$action  = $atts['action'] ? home_url( $atts['action'] ) : '';

		ob_start();
		?>
		<div class="ss-wrap">
			<form class="ss-search<?php echo $compact ? ' is-compact' : ''; ?>" method="get" action="<?php echo esc_url( $action ); ?>#ss-results" data-ss-dates>
				<?php self::date_fields( $q, 'ss-s' ); ?>
				<div class="ss-field ss-field-guests">
					<label class="ss-label" for="ss-s-adults">Adults</label>
					<input type="number" id="ss-s-adults" name="adults" min="1" max="<?php echo (int) self::MAX_ADULTS; ?>" value="<?php echo (int) $q['adults']; ?>">
				</div>
				<div class="ss-field ss-field-guests">
					<label class="ss-label" for="ss-s-children">Children</label>
					<input type="number" id="ss-s-children" name="children" min="0" max="<?php echo (int) self::MAX_CHILDREN; ?>" value="<?php echo (int) $q['children']; ?>">
				</div>
				<div class="ss-field ss-field-submit">
					<button type="submit" class="ss-btn">Check availability</button>
				</div>
			</form>
			<?php
			if ( ! $compact && $q['check_in'] && $q['check_out'] ) {
				self::results( $q, $atts['booking_page'] );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function results( $q, $booking_page ) {
		echo '<div id="ss-results" class="ss-results-wrap" aria-live="polite">';

		$error = self::validate_dates( $q['check_in'], $q['check_out'] );
		if ( $error ) {
			echo '<p class="ss-message">' . esc_html( $error ) . '</p></div>';
			return;
		}

		$nights = self::nights( $q['check_in'], $q['check_out'] );
		$guests = $q['adults'] + $q['children'];
		$rows   = SS_DB::availability( $q['check_in'], $q['check_out'] );
		?>
		<p class="ss-results-head">
			<strong><?php echo esc_html( self::date_label( $q['check_in'] ) . ' to ' . self::date_label( $q['check_out'] ) ); ?></strong>
			<span><?php echo esc_html( sprintf( _n( '%d night', '%d nights', $nights, 'satellite-stays' ), $nights ) . ', ' . sprintf( _n( '%d guest', '%d guests', $guests, 'satellite-stays' ), $guests ) ); ?></span>
		</p>
		<ul class="ss-results">
			<?php foreach ( $rows as $r ) : ?>
				<?php
				$free     = (int) $r->units_free;
				$fits     = $guests <= (int) $r->max_guests;
				$img      = self::image_url( $r->image );
				$price    = null === $r->price_per_night ? null : (float) $r->price_per_night;
				$book_url = add_query_arg(
					array(
						'type'      => (int) $r->id,
						'check_in'  => $q['check_in'],
						'check_out' => $q['check_out'],
						'adults'    => $q['adults'],
						'children'  => $q['children'],
					),
					home_url( $booking_page )
				) . '#ss-booking';
				?>
				<li class="ss-result<?php echo $free ? '' : ' is-full'; ?>">
					<div class="ss-result-media"<?php echo $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?>></div>
					<div class="ss-result-body">
						<h3 class="ss-result-name"><?php echo esc_html( $r->name ); ?></h3>
						<p class="ss-result-desc"><?php echo esc_html( $r->description ); ?></p>
						<p class="ss-result-meta">Sleeps up to <?php echo (int) $r->max_guests; ?></p>
					</div>
					<div class="ss-result-side">
						<?php if ( null !== $price ) : ?>
							<p class="ss-price"><strong><?php echo esc_html( ss_money( $price * $nights ) ); ?></strong><span><?php echo esc_html( ss_money( $price ) ); ?> per night</span></p>
						<?php else : ?>
							<p class="ss-price"><strong>Rates on request</strong><span>We reply with the price</span></p>
						<?php endif; ?>

						<?php if ( ! $free ) : ?>
							<span class="ss-pill is-full">Fully booked</span>
							<a class="ss-link" href="<?php echo esc_url( self::wa_link( "Hello Satellite Holiday Resort, is any " . $r->name . ' free near ' . self::date_label( $q['check_in'] ) . '?' ) ); ?>">Ask about other dates</a>
						<?php elseif ( ! $fits ) : ?>
							<span class="ss-pill is-warn">Sleeps up to <?php echo (int) $r->max_guests; ?></span>
							<a class="ss-link" href="<?php echo esc_url( self::wa_link( "Hello Satellite Holiday Resort, we are {$guests} guests. Can we book more than one " . $r->name . '?' ) ); ?>">Ask about more than one</a>
						<?php else : ?>
							<span class="ss-pill is-free"><?php echo 1 === $free ? 'Last one left' : 'Available'; ?></span>
							<a class="ss-btn" href="<?php echo esc_url( $book_url ); ?>">Request to book</a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="ss-footnote">Check-in from <?php echo esc_html( self::time_label( ss_setting( 'check_in_time' ) ) ); ?>, check-out by <?php echo esc_html( self::time_label( ss_setting( 'check_out_time' ) ) ); ?>. Your room is held while our team confirms the booking.</p>
		<?php
		echo '</div>';
	}

	/**
	 * Accommodation cards.
	 *
	 * @param array $atts link="/stay/" where "Check dates" points.
	 */
	public static function rooms( $atts ) {
		$atts  = shortcode_atts( array( 'link' => '/stay/' ), $atts, 'ss_rooms' );
		$types = SS_DB::get_types( true );
		wp_enqueue_style( 'ss-public' );
		if ( empty( $types ) ) {
			return '';
		}
		ob_start();
		?>
		<div class="ss-wrap">
			<ul class="ss-rooms">
				<?php foreach ( $types as $t ) : ?>
					<?php $img = self::image_url( $t->image ); ?>
					<li class="ss-room">
						<div class="ss-room-media"<?php echo $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?>></div>
						<div class="ss-room-body">
							<h3 class="ss-room-name"><?php echo esc_html( $t->name ); ?></h3>
							<p class="ss-room-desc"><?php echo esc_html( $t->description ); ?></p>
							<p class="ss-room-foot">
								<span>Sleeps up to <?php echo (int) $t->max_guests; ?></span>
								<strong><?php echo esc_html( null === $t->price_per_night ? 'Rates on request' : ss_money( $t->price_per_night ) . ' / night' ); ?></strong>
							</p>
							<a class="ss-link" href="<?php echo esc_url( home_url( $atts['link'] ) . '#ss-results' ); ?>">Check your dates</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
		return ob_get_clean();
	}

	/* =================================================================
	 * Booking request
	 * ================================================================= */

	public static function handle_booking() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['ss_booking'] ) ) {
			return;
		}

		$page_url = get_permalink( get_queried_object_id() );
		if ( ! $page_url ) {
			$page_url = home_url( '/' );
		}

		if ( ! empty( $_POST['ss_website'] ) ) { // Spam trap.
			wp_safe_redirect( $page_url );
			exit;
		}

		$v = array(
			'type_id'          => absint( $_POST['type_id'] ?? 0 ),
			'check_in'         => self::post_date( 'check_in' ),
			'check_out'        => self::post_date( 'check_out' ),
			'adults'           => min( self::MAX_ADULTS, max( 1, absint( $_POST['adults'] ?? 1 ) ) ),
			'children'         => min( self::MAX_CHILDREN, absint( $_POST['children'] ?? 0 ) ),
			'guest_name'       => mb_substr( self::post_text( 'guest_name' ), 0, 120 ),
			'phone'            => mb_substr( preg_replace( '/[^0-9+\s-]/', '', self::post_text( 'phone' ) ), 0, 30 ),
			'email'            => mb_substr( sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ), 0, 120 ),
			'special_requests' => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['special_requests'] ?? '' ) ), 0, 600 ),
			'consent'          => ! empty( $_POST['consent'] ),
		);
		self::$values = $v;

		if ( ! isset( $_POST['ss_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ss_nonce'] ), 'ss_booking' ) ) {
			self::$errors[] = 'This form expired. Please check your details and send it again.';
			return;
		}
		if ( ! ss_setting( 'bookings_open' ) ) {
			self::$errors[] = 'Online booking is paused right now. Please contact us on WhatsApp or by phone.';
			return;
		}
		$rate_key = 'ss_rl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		if ( (int) get_transient( $rate_key ) >= self::RATE_LIMIT ) {
			self::$errors[] = 'We have received several requests from you already. Please contact us on WhatsApp for more bookings.';
			return;
		}

		$type = $v['type_id'] ? SS_DB::get_type( $v['type_id'] ) : null;
		if ( ! $type || ! (int) $type->is_active ) {
			self::$errors[] = 'Choose where you would like to stay.';
		}
		$date_error = self::validate_dates( $v['check_in'], $v['check_out'] );
		if ( $date_error ) {
			self::$errors[] = $date_error;
		}
		if ( $type && ( $v['adults'] + $v['children'] ) > (int) $type->max_guests ) {
			self::$errors[] = sprintf( '%s sleeps up to %d guests. Choose a larger option, or contact us to book more than one.', $type->name, (int) $type->max_guests );
		}
		if ( '' === $v['guest_name'] ) {
			self::$errors[] = 'Enter your name.';
		}
		$digits = strlen( preg_replace( '/\D/', '', $v['phone'] ) );
		if ( $digits < 9 || $digits > 15 ) {
			self::$errors[] = 'Enter a phone number we can call or WhatsApp, for example 0772 448 961 or +44 7700 900123.';
		}
		if ( '' !== trim( wp_unslash( $_POST['email'] ?? '' ) ) && ! is_email( $v['email'] ) ) {
			self::$errors[] = 'Check your email address, or leave it empty.';
		}
		if ( ! $v['consent'] ) {
			self::$errors[] = 'Tick the box to agree that we can store your details for this booking.';
		}
		if ( self::$errors ) {
			return;
		}

		if ( SS_DB::free_count( $type->id, $v['check_in'], $v['check_out'] ) < 1 ) {
			self::$errors[] = sprintf( 'Sorry, %s is now fully booked for those dates. Please try other dates or another option.', $type->name );
			return;
		}

		$id = SS_DB::insert_booking(
			array(
				'type_id'          => $type->id,
				'unit_id'          => 0,
				'guest_name'       => $v['guest_name'],
				'phone'            => $v['phone'],
				'email'            => $v['email'],
				'check_in'         => $v['check_in'],
				'check_out'        => $v['check_out'],
				'adults'           => $v['adults'],
				'children'         => $v['children'],
				'channel'          => 'website',
				'status'           => 'pending',
				'special_requests' => $v['special_requests'],
				'staff_note'       => '',
			)
		);
		if ( ! $id ) {
			self::$errors[] = 'We could not save your request. Please try again or contact us on WhatsApp.';
			return;
		}

		set_transient( $rate_key, (int) get_transient( $rate_key ) + 1, HOUR_IN_SECONDS );

		$booking = SS_DB::get_booking( $id );
		self::email_resort( $booking );
		if ( $booking->email ) {
			self::email_guest( $booking, 'received' );
		}

		wp_safe_redirect( add_query_arg( 'booked', ss_reference( $id ), $page_url ) . '#ss-booking' );
		exit;
	}

	public static function booking_form() {
		wp_enqueue_style( 'ss-public' );

		ob_start();
		echo '<div id="ss-booking" class="ss-wrap ss-booking-wrap">';

		$ref = isset( $_GET['booked'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['booked'] ) ) ) : '';
		if ( preg_match( '/^SR-\d{4,}$/', $ref ) ) {
			?>
			<div class="ss-success" role="status">
				<h3>Booking request received</h3>
				<p>Your reference is <strong><?php echo esc_html( $ref ); ?></strong>. We are holding your room while our team confirms the booking and the price. We will contact you by phone or WhatsApp, and by email if you gave one.</p>
				<p class="ss-actions">
					<a class="ss-btn ss-btn-wa" href="<?php echo esc_url( self::wa_link( 'Hello Satellite Holiday Resort, I just sent booking request ' . $ref . '.' ) ); ?>">Send my reference on WhatsApp</a>
					<a class="ss-btn ss-btn-ghost" href="<?php echo esc_url( remove_query_arg( 'booked' ) . '#ss-booking' ); ?>">Make another booking</a>
				</p>
			</div>
			<?php
			echo '</div>';
			return ob_get_clean();
		}

		$types = SS_DB::get_types( true );
		if ( ! ss_setting( 'bookings_open' ) || empty( $types ) ) {
			?>
			<div class="ss-message">
				<p><strong>Online booking is paused right now.</strong> You can still book by WhatsApp or phone.</p>
				<p><a class="ss-btn ss-btn-wa" href="<?php echo esc_url( self::wa_link( 'Hello Satellite Holiday Resort, I would like to book a stay.' ) ); ?>">Book on WhatsApp</a></p>
			</div>
			<?php
			echo '</div>';
			return ob_get_clean();
		}

		wp_enqueue_script( 'ss-dates' );
		wp_enqueue_script( 'ss-calendar' );
		$v = self::$values ? self::$values : array_merge( self::query_from_get(), array( 'type_id' => absint( $_GET['type'] ?? 0 ) ) );

		if ( self::$errors ) :
			?>
			<div class="ss-errors" role="alert">
				<p><strong>Please fix the following:</strong></p>
				<ul>
					<?php foreach ( self::$errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		endif;
		?>
		<form class="ss-booking" method="post" action="<?php echo esc_url( remove_query_arg( 'booked' ) . '#ss-booking' ); ?>" data-ss-dates>
			<input type="hidden" name="ss_booking" value="1">
			<?php wp_nonce_field( 'ss_booking', 'ss_nonce', false ); ?>

			<div class="ss-field">
				<label class="ss-label" for="ss-type">Where would you like to stay?</label>
				<select id="ss-type" name="type_id" required>
					<option value="">Choose accommodation</option>
					<?php foreach ( $types as $t ) : ?>
						<option value="<?php echo (int) $t->id; ?>" <?php selected( (int) ( $v['type_id'] ?? 0 ), (int) $t->id ); ?>>
							<?php echo esc_html( $t->name . ' (sleeps up to ' . (int) $t->max_guests . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="ss-cal" data-ss-cal data-endpoint="<?php echo esc_url( rest_url( 'satellite-stays/v1/availability' ) ); ?>" hidden></div>

			<div class="ss-row">
				<?php self::date_fields( $v, 'ss-b' ); ?>
			</div>
			<p class="ss-help ss-nights" aria-live="polite"></p>

			<div class="ss-row">
				<div class="ss-field">
					<label class="ss-label" for="ss-b-adults">Adults</label>
					<input type="number" id="ss-b-adults" name="adults" min="1" max="<?php echo (int) self::MAX_ADULTS; ?>" value="<?php echo (int) ( $v['adults'] ?? 2 ); ?>" required>
				</div>
				<div class="ss-field">
					<label class="ss-label" for="ss-b-children">Children</label>
					<input type="number" id="ss-b-children" name="children" min="0" max="<?php echo (int) self::MAX_CHILDREN; ?>" value="<?php echo (int) ( $v['children'] ?? 0 ); ?>">
				</div>
			</div>

			<div class="ss-field">
				<label class="ss-label" for="ss-name">Full name</label>
				<input type="text" id="ss-name" name="guest_name" value="<?php echo esc_attr( $v['guest_name'] ?? '' ); ?>" autocomplete="name" maxlength="120" required>
			</div>
			<div class="ss-row">
				<div class="ss-field">
					<label class="ss-label" for="ss-phone">Phone or WhatsApp</label>
					<input type="tel" id="ss-phone" name="phone" value="<?php echo esc_attr( $v['phone'] ?? '' ); ?>" autocomplete="tel" maxlength="30" placeholder="0772 448 961" required>
				</div>
				<div class="ss-field">
					<label class="ss-label" for="ss-email">Email <span class="ss-optional">(optional)</span></label>
					<input type="email" id="ss-email" name="email" value="<?php echo esc_attr( $v['email'] ?? '' ); ?>" autocomplete="email" maxlength="120">
				</div>
			</div>
			<div class="ss-field">
				<label class="ss-label" for="ss-requests">Special requests <span class="ss-optional">(optional)</span></label>
				<textarea id="ss-requests" name="special_requests" rows="3" maxlength="600" placeholder="Arrival time, dietary needs, celebrating a honeymoon or birthday, activities you would like to book…"><?php echo esc_textarea( $v['special_requests'] ?? '' ); ?></textarea>
			</div>

			<div class="ss-hp" aria-hidden="true">
				<label for="ss-website">Leave this field empty</label>
				<input type="text" id="ss-website" name="ss_website" value="" tabindex="-1" autocomplete="off">
			</div>

			<label class="ss-consent">
				<input type="checkbox" name="consent" value="1" <?php checked( ! empty( $v['consent'] ) ); ?> required>
				<span>I agree that Satellite Holiday Resort can store these details to manage my booking. They are not shared with anyone else.</span>
			</label>

			<button type="submit" class="ss-btn ss-btn-block">Send booking request</button>
			<p class="ss-help">No payment now. We confirm availability and the price first.</p>
		</form>
		<?php
		echo '</div>';
		return ob_get_clean();
	}

	/* =================================================================
	 * Emails
	 * ================================================================= */

	public static function email_resort( $b ) {
		$to = ss_setting( 'notify_email' );
		if ( ! is_email( $to ) ) {
			return false;
		}
		$ref     = ss_reference( $b->id );
		$subject = sprintf( 'New booking request %s: %s, %s to %s', $ref, $b->type_name, self::date_label( $b->check_in ), self::date_label( $b->check_out ) );
		$body    = implode(
			"\n",
			array(
				'A new booking request came in through the website. The room is held until you confirm or cancel it.',
				'',
				'Reference:  ' . $ref,
				'Stay:       ' . $b->type_name,
				'Check-in:   ' . self::date_label( $b->check_in ),
				'Check-out:  ' . self::date_label( $b->check_out ) . ' (' . (int) $b->nights . ' nights)',
				'Guests:     ' . (int) $b->adults . ' adults, ' . (int) $b->children . ' children',
				'Estimate:   ' . ( null === $b->price_per_night ? 'Rates on request' : ss_money( $b->price_per_night * $b->nights ) ),
				'Name:       ' . $b->guest_name,
				'Phone:      ' . $b->phone,
				'WhatsApp:   https://wa.me/' . ss_whatsapp_number( $b->phone ),
				'Email:      ' . ( $b->email ? $b->email : '(not given)' ),
				'Requests:   ' . ( $b->special_requests ? $b->special_requests : '(none)' ),
				'',
				'Confirm, assign a room or cancel here:',
				admin_url( 'admin.php?page=ss-bookings&action=edit&id=' . (int) $b->id ),
			)
		);
		$headers = array();
		if ( $b->email ) {
			$headers[] = 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $b->guest_name ) . ' <' . $b->email . '>';
		}
		return wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * @param string $type received | confirmed | cancelled
	 */
	public static function email_guest( $b, $type ) {
		if ( ! $b->email || ! is_email( $b->email ) ) {
			return false;
		}
		$ref = ss_reference( $b->id );
		switch ( $type ) {
			case 'confirmed':
				$subject = "Your stay is confirmed ($ref)";
				$intro   = 'Great news: your stay at Satellite Holiday Resort is confirmed. We look forward to welcoming you to Kabale.';
				break;
			case 'cancelled':
				$subject = "Your booking has been cancelled ($ref)";
				$intro   = 'Your booking has been cancelled. If this is a mistake, or you would like other dates, please contact us.';
				break;
			default:
				$subject = "We received your booking request ($ref)";
				$intro   = 'Thank you. We are holding your room and will confirm your booking and the price shortly.';
		}
		$lines = array(
			'Hello ' . $b->guest_name . ',',
			'',
			$intro,
			'',
			'Reference:  ' . $ref,
			'Stay:       ' . $b->type_name . ( $b->unit_name ? ' (' . $b->unit_name . ')' : '' ),
			'Check-in:   ' . self::date_label( $b->check_in ) . ', from ' . self::time_label( ss_setting( 'check_in_time' ) ),
			'Check-out:  ' . self::date_label( $b->check_out ) . ', by ' . self::time_label( ss_setting( 'check_out_time' ) ),
			'Guests:     ' . (int) $b->adults . ' adults, ' . (int) $b->children . ' children',
			'Where:      Satellite Holiday Resort, Muhanga, Kabale–Mbarara Road, Kabale',
			'',
			'To change or cancel, reply to this email or WhatsApp us on +256 772 448 961.',
			'',
			'Satellite Holiday Resort & Great Lakes Museum',
		);
		$headers = array();
		$reply   = ss_setting( 'notify_email' );
		if ( is_email( $reply ) ) {
			$headers[] = 'Reply-To: Satellite Holiday Resort <' . $reply . '>';
		}
		return wp_mail( $b->email, $subject, implode( "\n", $lines ), $headers );
	}

	/* =================================================================
	 * Helpers
	 * ================================================================= */

	/** Check-in / check-out inputs shared by the search and booking forms. */
	private static function date_fields( $q, $prefix ) {
		$today = current_datetime();
		$min   = $today->format( 'Y-m-d' );
		$max   = $today->modify( '+' . self::DAYS_AHEAD . ' days' )->format( 'Y-m-d' );
		?>
		<div class="ss-field">
			<label class="ss-label" for="<?php echo esc_attr( $prefix ); ?>-in">Check-in</label>
			<input type="date" id="<?php echo esc_attr( $prefix ); ?>-in" name="check_in" value="<?php echo esc_attr( $q['check_in'] ?? '' ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" required>
		</div>
		<div class="ss-field">
			<label class="ss-label" for="<?php echo esc_attr( $prefix ); ?>-out">Check-out</label>
			<input type="date" id="<?php echo esc_attr( $prefix ); ?>-out" name="check_out" value="<?php echo esc_attr( $q['check_out'] ?? '' ); ?>" min="<?php echo esc_attr( $min ); ?>" required>
		</div>
		<?php
	}

	private static function validate_dates( $in, $out ) {
		if ( ! $in || ! $out ) {
			return 'Choose your check-in and check-out dates.';
		}
		$today = current_time( 'Y-m-d' );
		if ( $in < $today ) {
			return 'Check-in cannot be in the past.';
		}
		if ( $out <= $in ) {
			return 'Check-out must be at least one day after check-in.';
		}
		if ( $in > gmdate( 'Y-m-d', strtotime( $today . ' +' . self::DAYS_AHEAD . ' days' ) ) ) {
			return 'We take bookings up to a year ahead.';
		}
		$max = (int) ss_setting( 'max_nights' );
		if ( self::nights( $in, $out ) > $max ) {
			return sprintf( 'Online bookings are for up to %d nights. Contact us for a longer stay.', $max );
		}
		return '';
	}

	/** Read the search from the URL, safely. */
	private static function query_from_get() {
		return array(
			'check_in'  => self::valid_date( wp_unslash( $_GET['check_in'] ?? '' ) ),
			'check_out' => self::valid_date( wp_unslash( $_GET['check_out'] ?? '' ) ),
			'adults'    => min( self::MAX_ADULTS, max( 1, absint( $_GET['adults'] ?? 2 ) ) ),
			'children'  => min( self::MAX_CHILDREN, absint( $_GET['children'] ?? 0 ) ),
		);
	}

	private static function valid_date( $value ) {
		$value = sanitize_text_field( (string) $value );
		$d     = DateTime::createFromFormat( '!Y-m-d', $value );
		return ( $d && $d->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	private static function post_date( $key ) {
		return self::valid_date( wp_unslash( $_POST[ $key ] ?? '' ) );
	}

	private static function post_text( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	}

	public static function nights( $in, $out ) {
		return (int) round( ( strtotime( $out ) - strtotime( $in ) ) / DAY_IN_SECONDS );
	}

	public static function date_label( $ymd ) {
		return gmdate( 'D j M Y', strtotime( $ymd ) );
	}

	public static function time_label( $hhmm ) {
		list( $h, $m ) = array_map( 'intval', explode( ':', (string) $hhmm ) + array( 0, 0 ) );
		return sprintf( '%d:%02d %s', ( $h % 12 ) ? $h % 12 : 12, $m, $h < 12 ? 'am' : 'pm' );
	}

	public static function wa_link( $text, $number = null ) {
		$number = $number ? $number : ss_setting( 'whatsapp' );
		return 'https://wa.me/' . preg_replace( '/\D/', '', (string) $number ) . '?text=' . rawurlencode( $text );
	}

	/**
	 * URL of a Media Library image by file name (e.g. cottage.jpg), so the
	 * resort's photos can be swapped in the Media Library without code.
	 */
	public static function image_url( $file ) {
		if ( ! $file ) {
			return '';
		}
		if ( isset( self::$images[ $file ] ) ) {
			return self::$images[ $file ];
		}
		global $wpdb;
		$id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s ) ORDER BY post_id DESC LIMIT 1",
				// Match "cottage.jpg", "cottage.jpeg/.png/.webp" and WordPress's "cottage-scaled.jpg" for large photos.
				'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%',
				'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '-scaled.%',
				$wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%'
			)
		);
		$url = $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
		if ( ! $url ) {
			/** Lets a theme supply a placeholder until the real photo is uploaded. */
			$url = (string) apply_filters( 'ss_image_fallback', '', $file );
		}
		self::$images[ $file ] = $url;
		return self::$images[ $file ];
	}
}
