<?php
/**
 * Verified guest reviews.
 *
 * How it stays honest:
 *  - Only a guest whose booking is checked out can write a review.
 *  - Their review link carries a signed token (HMAC of the booking, keyed with
 *    the site's secret salt), so links can't be guessed or edited.
 *  - One review per stay (UNIQUE booking_id in the database).
 *  - Nothing is published until staff approve it.
 *
 *  [ss_review_form]  The page guests reach from their review link.
 *  [ss_reviews]      Approved reviews with average rating, plus schema.org
 *                    structured data so search engines can show the stars.
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

class SS_Reviews {

	const PAGE = '/review-your-stay/';

	const STATUSES = array(
		'pending'  => 'Waiting',
		'approved' => 'Published',
		'hidden'   => 'Hidden',
	);

	private static $errors = array();
	private static $values = array();

	public static function init() {
		add_shortcode( 'ss_review_form', array( __CLASS__, 'form' ) );
		add_shortcode( 'ss_reviews', array( __CLASS__, 'reviews' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_submit' ) );
		add_action( 'ss_booking_checked_out', array( __CLASS__, 'email_review_link' ) );
		add_filter( 'ss_booking_row_actions', array( __CLASS__, 'row_action' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_ss_review_status', array( __CLASS__, 'moderate' ) );
	}

	/* =================================================================
	 * Signed review links
	 * ================================================================= */

	public static function token( $booking ) {
		return substr( hash_hmac( 'sha256', 'review|' . (int) $booking->id . '|' . $booking->check_in, wp_salt( 'nonce' ) ), 0, 32 );
	}

	public static function link( $booking ) {
		return add_query_arg(
			array(
				'b' => (int) $booking->id,
				't' => self::token( $booking ),
			),
			home_url( self::PAGE )
		);
	}

	/** The booking behind a review link, if the link is genuine and the stay is over. */
	private static function booking_from_request( $source ) {
		$id    = absint( $source['b'] ?? 0 );
		$token = sanitize_key( $source['t'] ?? '' );
		if ( ! $id || 32 !== strlen( $token ) ) {
			return null;
		}
		$booking = SS_DB::get_booking( $id );
		if ( ! $booking || ! hash_equals( self::token( $booking ), $token ) || 'checked_out' !== $booking->status ) {
			return null;
		}
		return $booking;
	}

	/** Email the guest their review link when staff check them out. */
	public static function email_review_link( $booking_id ) {
		$b = SS_DB::get_booking( $booking_id );
		if ( ! $b || ! $b->email || ! is_email( $b->email ) || SS_DB::review_for_booking( $b->id ) ) {
			return false;
		}
		$body = implode(
			"\n",
			array(
				'Hello ' . $b->guest_name . ',',
				'',
				'Thank you for staying with us at Satellite Holiday Resort. We hope you enjoyed Kabale.',
				'',
				'Would you take a minute to tell other travellers about your stay? Your review helps us improve and helps others choose.',
				'',
				self::link( $b ),
				'',
				'This link is only for your stay (' . ss_reference( $b->id ) . ').',
				'',
				'Satellite Holiday Resort & Great Lakes Museum',
			)
		);
		$headers = array();
		$reply   = ss_setting( 'notify_email' );
		if ( is_email( $reply ) ) {
			$headers[] = 'Reply-To: Satellite Holiday Resort <' . $reply . '>';
		}
		return wp_mail( $b->email, 'How was your stay at Satellite Holiday Resort?', $body, $headers );
	}

	/** "Send review link" on checked-out bookings in the admin list (WhatsApp, for guests without email). */
	public static function row_action( $links, $r ) {
		if ( 'checked_out' === $r->status && ! SS_DB::review_for_booking( $r->id ) ) {
			$msg     = 'Hello ' . $r->guest_name . ', thank you for staying at Satellite Holiday Resort. Please tell us about your stay here: ' . self::link( $r );
			$links[] = sprintf( '<a href="%s" target="_blank" rel="noopener">Send review link</a>', esc_url( SS_Public::wa_link( $msg, ss_whatsapp_number( $r->phone ) ) ) );
		}
		return $links;
	}

	/* =================================================================
	 * Review form
	 * ================================================================= */

	public static function handle_submit() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['ss_review'] ) ) {
			return;
		}
		$booking = self::booking_from_request( $_POST );
		if ( ! $booking ) {
			self::$errors[] = 'This review link is not valid. Please use the link we sent you.';
			return;
		}
		if ( ! isset( $_POST['ss_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ss_review_nonce'] ), 'ss_review_' . $booking->id ) ) {
			self::$errors[] = 'This form expired. Please send it again.';
			return;
		}
		if ( SS_DB::review_for_booking( $booking->id ) ) {
			self::$errors[] = 'Thank you, we already have your review for this stay.';
			return;
		}

		$v = array(
			'rating'       => absint( $_POST['rating'] ?? 0 ),
			'title'        => mb_substr( sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ), 0, 120 ),
			'body'         => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) ), 0, 1500 ),
			'display_name' => mb_substr( sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) ), 0, 60 ),
			'consent'      => ! empty( $_POST['consent'] ),
		);
		self::$values = $v;

		if ( $v['rating'] < 1 || $v['rating'] > 5 ) {
			self::$errors[] = 'Choose a star rating from 1 to 5.';
		}
		if ( mb_strlen( $v['title'] ) < 3 ) {
			self::$errors[] = 'Give your review a short title.';
		}
		if ( mb_strlen( $v['body'] ) < 20 ) {
			self::$errors[] = 'Tell us a little more about your stay (at least 20 characters).';
		}
		if ( '' === $v['display_name'] ) {
			self::$errors[] = 'Enter the name to show with your review.';
		}
		if ( ! $v['consent'] ) {
			self::$errors[] = 'Tick the box to let us publish your review.';
		}
		if ( self::$errors ) {
			return;
		}

		$id = SS_DB::insert_review(
			array(
				'booking_id'   => $booking->id,
				'type_id'      => $booking->type_id,
				'display_name' => $v['display_name'],
				'rating'       => $v['rating'],
				'title'        => $v['title'],
				'body'         => $v['body'],
				'stayed_on'    => $booking->check_in,
			)
		);
		if ( ! $id ) {
			self::$errors[] = 'Thank you, we already have your review for this stay.';
			return;
		}

		$to = ss_setting( 'notify_email' );
		if ( is_email( $to ) ) {
			wp_mail(
				$to,
				sprintf( 'New %d-star review waiting for approval (%s)', $v['rating'], ss_reference( $booking->id ) ),
				$v['display_name'] . ' wrote: "' . $v['title'] . "\"\n\n" . $v['body'] . "\n\nApprove or hide it here:\n" . admin_url( 'admin.php?page=ss-reviews' )
			);
		}

		wp_safe_redirect( add_query_arg( 'thanks', '1', home_url( self::PAGE ) ) );
		exit;
	}

	public static function form() {
		wp_enqueue_style( 'ss-public' );
		ob_start();
		echo '<div id="ss-review" class="ss-wrap ss-review-wrap">';

		if ( isset( $_GET['thanks'] ) ) {
			echo '<div class="ss-success" role="status"><h3>Thank you for your review</h3><p>We read every review. Yours will appear on our website once our team has checked it.</p></div></div>';
			return ob_get_clean();
		}

		$booking = self::booking_from_request( ! empty( $_POST['ss_review'] ) ? $_POST : $_GET ); // A sent form carries its own link details.
		if ( ! $booking ) {
			echo '<div class="ss-message"><p><strong>Reviews are for guests who have stayed with us.</strong> Please use the personal link we sent you after check-out.</p></div></div>';
			return ob_get_clean();
		}
		if ( SS_DB::review_for_booking( $booking->id ) ) {
			echo '<div class="ss-success"><h3>Thank you</h3><p>We already have your review for this stay.</p></div></div>';
			return ob_get_clean();
		}

		$parts   = preg_split( '/\s+/', trim( $booking->guest_name ) );
		$default = $parts[0] . ( count( $parts ) > 1 ? ' ' . mb_substr( end( $parts ), 0, 1 ) . '.' : '' );
		$v       = self::$values + array(
			'rating'       => 0,
			'title'        => '',
			'body'         => '',
			'display_name' => $default,
			'consent'      => false,
		);

		if ( self::$errors ) {
			echo '<div class="ss-errors" role="alert"><p><strong>Please fix the following:</strong></p><ul>';
			foreach ( self::$errors as $e ) {
				echo '<li>' . esc_html( $e ) . '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<p class="ss-review-stay">Your stay: <strong><?php echo esc_html( $booking->type_name ); ?></strong>, <?php echo esc_html( SS_Public::date_label( $booking->check_in ) . ' to ' . SS_Public::date_label( $booking->check_out ) ); ?></p>
		<form class="ss-booking ss-review-form" method="post" action="<?php echo esc_url( home_url( self::PAGE ) ); ?>">
			<input type="hidden" name="ss_review" value="1">
			<input type="hidden" name="b" value="<?php echo (int) $booking->id; ?>">
			<input type="hidden" name="t" value="<?php echo esc_attr( self::token( $booking ) ); ?>">
			<?php wp_nonce_field( 'ss_review_' . $booking->id, 'ss_review_nonce', false ); ?>

			<fieldset class="ss-stars-input">
				<legend class="ss-label">Your rating</legend>
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<input type="radio" id="ss-star-<?php echo (int) $i; ?>" name="rating" value="<?php echo (int) $i; ?>" <?php checked( (int) $v['rating'], $i ); ?> required>
					<label for="ss-star-<?php echo (int) $i; ?>" title="<?php echo (int) $i; ?> stars"><span class="screen-reader-text"><?php echo (int) $i; ?> stars</span></label>
				<?php endfor; ?>
			</fieldset>

			<div class="ss-field">
				<label class="ss-label" for="ss-r-title">Title</label>
				<input type="text" id="ss-r-title" name="title" value="<?php echo esc_attr( $v['title'] ); ?>" maxlength="120" placeholder="e.g. Peaceful cottage, great buffet" required>
			</div>
			<div class="ss-field">
				<label class="ss-label" for="ss-r-body">Your review</label>
				<textarea id="ss-r-body" name="body" rows="6" maxlength="1500" required><?php echo esc_textarea( $v['body'] ); ?></textarea>
			</div>
			<div class="ss-field">
				<label class="ss-label" for="ss-r-name">Name to show</label>
				<input type="text" id="ss-r-name" name="display_name" value="<?php echo esc_attr( $v['display_name'] ); ?>" maxlength="60" required>
			</div>
			<label class="ss-consent">
				<input type="checkbox" name="consent" value="1" <?php checked( ! empty( $v['consent'] ) ); ?> required>
				<span>Satellite Holiday Resort may publish my review on its website with the name above.</span>
			</label>
			<button type="submit" class="ss-btn ss-btn-block">Send my review</button>
		</form>
		<?php
		echo '</div>';
		return ob_get_clean();
	}

	/* =================================================================
	 * Public reviews
	 * ================================================================= */

	/**
	 * @param array $atts heading, limit, class (extra wrapper classes from the theme).
	 */
	public static function reviews( $atts ) {
		$atts    = shortcode_atts(
			array(
				'heading' => 'What our guests say',
				'limit'   => 6,
				'class'   => '',
			),
			$atts,
			'ss_reviews'
		);
		$rows    = SS_DB::get_public_reviews( max( 1, min( 24, (int) $atts['limit'] ) ) );
		$summary = SS_DB::review_summary();
		if ( empty( $rows ) ) {
			return ''; // No section at all until there are real, approved reviews.
		}
		wp_enqueue_style( 'ss-public' );

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'LodgingBusiness',
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'aggregateRating' => array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $summary->average,
				'reviewCount' => (int) $summary->total,
				'bestRating'  => 5,
			),
			'review'          => array(),
		);
		foreach ( $rows as $r ) {
			$schema['review'][] = array(
				'@type'         => 'Review',
				'author'        => array(
					'@type' => 'Person',
					'name'  => $r->display_name,
				),
				'name'          => $r->title,
				'reviewBody'    => $r->body,
				'datePublished' => $r->stayed_on,
				'reviewRating'  => array(
					'@type'       => 'Rating',
					'ratingValue' => (int) $r->rating,
					'bestRating'  => 5,
				),
			);
		}

		ob_start();
		?>
		<section class="ss-wrap ss-reviews-band <?php echo esc_attr( $atts['class'] ); ?>">
			<div class="ss-reviews-inner">
				<div class="ss-reviews-head">
					<h2><?php echo esc_html( $atts['heading'] ); ?></h2>
					<p class="ss-reviews-score">
						<span class="ss-stars" style="--ss-rating:<?php echo esc_attr( (string) (float) $summary->average ); ?>" aria-hidden="true"></span>
						<strong><?php echo esc_html( number_format( (float) $summary->average, 1 ) ); ?> out of 5</strong>
						<span>from <?php echo esc_html( sprintf( _n( '%d verified stay', '%d verified stays', (int) $summary->total, 'satellite-stays' ), (int) $summary->total ) ); ?></span>
					</p>
				</div>
				<ul class="ss-reviews">
					<?php foreach ( $rows as $r ) : ?>
						<li class="ss-review">
							<span class="ss-stars" style="--ss-rating:<?php echo (int) $r->rating; ?>" role="img" aria-label="<?php echo esc_attr( (int) $r->rating . ' out of 5 stars' ); ?>"></span>
							<h3><?php echo esc_html( $r->title ); ?></h3>
							<p class="ss-review-body"><?php echo esc_html( $r->body ); ?></p>
							<p class="ss-review-by"><strong><?php echo esc_html( $r->display_name ); ?></strong><br><span class="ss-verified">Verified stay: <?php echo esc_html( $r->type_name . ', ' . gmdate( 'F Y', strtotime( $r->stayed_on ) ) ); ?></span></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ); ?></script>
		</section>
		<?php
		return ob_get_clean();
	}

	/* =================================================================
	 * Moderation (wp-admin)
	 * ================================================================= */

	public static function menu() {
		$waiting = (int) ( SS_DB::stats()->reviews_waiting ?? 0 );
		$label   = 'Reviews' . ( $waiting ? ' <span class="awaiting-mod">' . $waiting . '</span>' : '' );
		add_submenu_page( 'ss-overview', 'Guest reviews', $label, SS_Admin::CAP, 'ss-reviews', array( __CLASS__, 'page' ) );
	}

	public static function moderate() {
		if ( ! current_user_can( SS_Admin::CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-stays' ), 403 );
		}
		$id = absint( $_GET['id'] ?? 0 );
		$to = sanitize_key( $_GET['to'] ?? '' );
		check_admin_referer( 'ss_review_' . $id . '_' . $to );

		if ( 'delete' === $to ) {
			SS_DB::delete_review( $id );
			$msg = 'deleted';
		} elseif ( isset( self::STATUSES[ $to ] ) ) {
			SS_DB::set_review_status( $id, $to );
			$msg = 'review_' . $to;
		} else {
			$msg = 'error';
		}
		wp_safe_redirect( admin_url( 'admin.php?page=ss-reviews&ss_msg=' . $msg ) );
		exit;
	}

	public static function page() {
		$views = array( 'pending' => 'Waiting', 'approved' => 'Published', 'hidden' => 'Hidden', '' => 'All' );
		$view  = isset( $_GET['view'] ) && isset( $views[ $_GET['view'] ] ) ? sanitize_key( $_GET['view'] ) : 'pending';
		$rows  = SS_DB::get_reviews( $view );
		$msgs  = array(
			'review_approved' => 'Review published.',
			'review_hidden'   => 'Review hidden from the website.',
			'review_pending'  => 'Review moved back to waiting.',
			'deleted'         => 'Review deleted.',
		);
		$msg   = sanitize_key( $_GET['ss_msg'] ?? '' );
		?>
		<div class="wrap ss">
			<h1>Guest reviews</h1>
			<?php if ( isset( $msgs[ $msg ] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msgs[ $msg ] ); ?></p></div>
			<?php endif; ?>
			<p class="description">Only guests who have checked out can write a review, using the personal link emailed to them at check-out (or sent by WhatsApp from <a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-bookings&view=past' ) ); ?>">past bookings</a>). Nothing appears on the website until you publish it.</p>
			<ul class="subsubsub">
				<?php
				$out = array();
				foreach ( $views as $key => $label ) {
					$out[] = sprintf( '<li><a href="%s" class="%s">%s</a>', esc_url( admin_url( 'admin.php?page=ss-reviews&view=' . $key ) ), $view === $key ? 'current' : '', esc_html( $label ) );
				}
				echo implode( ' |</li>', $out ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
				?>
			</ul>
			<table class="widefat striped" style="clear:both">
				<thead><tr><th>Rating</th><th>Review</th><th>Stay</th><th>Status</th><th></th></tr></thead>
				<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="5">No reviews here.</td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><strong><?php echo str_repeat( '★', (int) $r->rating ) . str_repeat( '☆', 5 - (int) $r->rating ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed characters. ?></strong></td>
						<td><strong><?php echo esc_html( $r->title ); ?></strong><br><?php echo esc_html( $r->body ); ?><br><small>Shown as: <?php echo esc_html( $r->display_name ); ?></small></td>
						<td><?php echo esc_html( $r->type_name ); ?><br><small><?php echo esc_html( gmdate( 'j M Y', strtotime( $r->stayed_on ) ) ); ?></small>
							<?php if ( $r->booking_id ) : ?><br><small><a href="<?php echo esc_url( admin_url( 'admin.php?page=ss-bookings&action=edit&id=' . (int) $r->booking_id ) ); ?>"><?php echo esc_html( ss_reference( $r->booking_id ) . ', ' . $r->guest_name ); ?></a></small><?php endif; ?>
						</td>
						<td><span class="ss-badge ss-rv-<?php echo esc_attr( $r->status ); ?>"><?php echo esc_html( self::STATUSES[ $r->status ] ?? $r->status ); ?></span></td>
						<td class="ss-actions">
							<?php
							$acts = array();
							foreach ( array( 'approved' => 'Publish', 'hidden' => 'Hide', 'delete' => 'Delete' ) as $to => $label ) {
								if ( $to === $r->status ) {
									continue;
								}
								$acts[] = sprintf(
									'<a href="%s"%s>%s</a>',
									esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_review_status&id=' . (int) $r->id . '&to=' . $to ), 'ss_review_' . (int) $r->id . '_' . $to ) ),
									'delete' === $to ? ' class="ss-delete" onclick="return confirm(\'Delete this review?\');"' : '',
									esc_html( $label )
								);
							}
							echo implode( ' | ', $acts ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
