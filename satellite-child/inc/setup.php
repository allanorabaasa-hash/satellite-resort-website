<?php
/**
 * Satellite Holiday Resort one-click site setup.
 *
 * Appearance → Resort setup. Builds or refreshes every page from the theme's
 * page designs, sets the homepage and the Travel Notes (blog) page, switches to
 * "Post name" permalinks, moves WordPress sample content to the trash and adds
 * a keyword-targeted travel article.
 *
 * Safe to run again: pages are refreshed in place and articles that already
 * exist are left alone.
 *
 * @package satellite-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages: slug => [ title, pattern slug or '' for an empty page ].
 */
function sat_setup_pages() {
	return array(
		'home'               => array( 'Home', 'satellite-child/page-home' ),
		'accommodation'      => array( 'Accommodation', 'satellite-child/page-stay' ),
		'book-your-stay'     => array( 'Book Your Stay', 'satellite-child/page-book' ),
		'adventure'          => array( 'Adventure', 'satellite-child/page-adventure' ),
		'great-lakes-museum' => array( 'Great Lakes Museum', 'satellite-child/page-museum' ),
		'food-and-hall'      => array( 'Food and Hall', 'satellite-child/page-food-hall' ),
		'gallery'            => array( 'Gallery', 'satellite-child/page-gallery' ),
		'contact'            => array( 'Contact', 'satellite-child/page-contact' ),
		'review-your-stay'   => array( 'Review Your Stay', 'satellite-child/page-review' ),
		'travel-notes'       => array( 'Travel Notes', '' ),
	);
}

/**
 * Starter article (keyword: "things to do in Kabale").
 */
function sat_setup_posts() {
	return array(
		array(
			'slug'    => 'things-to-do-in-kabale',
			'title'   => 'Things to Do in Kabale: A Weekend at Satellite Holiday Resort',
			'image'   => 'blog-kabale.jpg',
			'excerpt' => 'Planning a trip to Kabale? Here is how to spend a weekend in the hills: wooden cottages, zip-lining, the Great Lakes Museum, good food and easy trips to the lakes and forests of southwestern Uganda.',
			'content' => <<<'HTML'
<!-- wp:paragraph -->
<p>Kabale is often called the Switzerland of Africa, and the first drive in explains why. Terraced hills fold into one another in every direction, the air is cool, and mornings often begin in mist. If you are looking for things to do in Kabale, this guide shows how to spend a relaxed weekend based at Satellite Holiday Resort, on the Kabale–Mbarara Road at Muhanga.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Why Kabale is worth more than a stopover</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Many travellers see Kabale only through a car window on the way to the lakes and forests of the southwest. That is a shame. The town and the hills around it make a calm, affordable base, with a cooler climate than most of Uganda and scenery that changes with the light. Staying a night or two lets you break a long drive, rest properly and see a side of the region that most visitors miss.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day one: settle in and explore the grounds</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Arrive in the afternoon and check in to a wooden cottage. The eco-friendly cottages sit among gardens with views of the hills, and they suit couples on honeymoon as well as families who want a quiet base. Families and groups can choose the guest house or group accommodation, and campers can pitch a tent on the grounds.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Spend the rest of the afternoon walking the grounds. The emerald Tree of Life statue makes a striking photo backdrop, and the statues and gardens around it make an easy, unhurried loop. In the evening, eat at the restaurant, either inside or out on the terrace as the hills turn dark.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day two: adventure in the morning, history in the afternoon</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Start with the adventure park while the air is fresh. The zip-line carries you across the valley, the pirate ship and the ninja-style obstacle course keep children (and adults) busy, and there are mountain biking tracks and hill climbs for anyone who wants to stretch their legs. Younger children have their own kids' park and games area.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>After lunch, slow down at the Great Lakes Museum. Its artefacts and displays tell the story of the clans and tribes of the Great Lakes region, which gives real context to the landscape you have been looking at all weekend. Before you leave, visit the gift shop for original art and work by local artisans: pottery, paintings, weavings, carved wood and jewellery make good souvenirs.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Trips beyond the resort</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Kabale is a natural base for exploring the southwest. Lake Bunyonyi, with its many islands and calm water, is a short drive from town and is popular for canoe trips and lakeside walks. Further on, Bwindi Impenetrable National Park is famous for mountain gorilla trekking, which must be booked in advance with the Uganda Wildlife Authority. Many visitors spend a night in Kabale before or after these trips to break up the journey.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Practical tips for visiting Kabale</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item -->
<li><strong>Pack a warm layer.</strong> Evenings and early mornings in the hills can be cool, even when the days are sunny.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Book ahead for weekends and holidays.</strong> Cottages can fill up around public holidays, weddings and school breaks.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Plan activities with the team.</strong> Ask about adventure park times, prices and age limits when you arrive, or message us before your trip.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Bring good shoes.</strong> Hill walks and the climbing trails are far more enjoyable in proper footwear.</li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Book your weekend in Kabale</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Whether it is a honeymoon, a family holiday or a night on the road to the lakes, Satellite Holiday Resort puts the best things to do in Kabale in one place. Check your dates and send a booking request online, or message us on WhatsApp at +256 772 448 961. We hold your room as soon as you ask, and confirm the details with you before you travel.</p>
<!-- /wp:paragraph -->
HTML,
		),
	);
}

/**
 * Find a Media Library image by its file name (e.g. health_tips_1.jpg).
 *
 * @param string $file File name.
 * @return int Attachment ID or 0.
 */
function sat_setup_find_attachment( $file ) {
	global $wpdb;
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s ) ORDER BY post_id DESC LIMIT 1",
			// Match "cottage.jpg", "cottage.jpeg/.png/.webp" and WordPress's "cottage-scaled.jpg" for large photos.
			'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%',
			'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '-scaled.%',
			$wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%'
		)
	);
}

/**
 * Run the setup. Returns a list of what was done, for the report.
 */
function sat_run_site_setup() {
	$log      = array();
	$registry = WP_Block_Patterns_Registry::get_instance();
	$ids      = array();

	// 1. Pages.
	foreach ( sat_setup_pages() as $slug => $page ) {
		list( $title, $pattern ) = $page;

		$content = '';
		if ( $pattern ) {
			$registered = $registry->get_registered( $pattern );
			if ( ! $registered ) {
				$log[] = "Skipped {$title}: pattern {$pattern} not found.";
				continue;
			}
			$content = $registered['content'];
		}

		$existing = get_page_by_path( $slug );
		if ( ! $existing && 'accommodation' === $slug ) {
			$existing = get_page_by_path( 'stay' ); // renamed from Stay in 1.2.0
		}
		$data     = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => $content,
		);

		if ( $existing ) {
			$data['ID'] = $existing->ID;
			$id         = wp_update_post( wp_slash( $data ), true );
			$verb       = 'Refreshed';
		} else {
			$id   = wp_insert_post( wp_slash( $data ), true );
			$verb = 'Created';
		}

		if ( is_wp_error( $id ) ) {
			$log[] = "Could not save {$title}: " . $id->get_error_message();
			continue;
		}

		if ( $pattern ) {
			update_post_meta( $id, '_wp_page_template', 'page-no-title' );
		}
		$ids[ $slug ] = $id;
		$log[]        = "{$verb} page: {$title}";
	}

	// 2. Homepage, blog page and site details.
	if ( isset( $ids['home'], $ids['travel-notes'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		update_option( 'page_for_posts', $ids['travel-notes'] );
		$log[] = 'Set Home as the homepage and Travel Notes as the posts page';
	}
	update_option( 'blogname', 'Satellite Holiday Resort' );
	update_option( 'blogdescription', 'Cottages, adventure park and the Great Lakes Museum in Kabale, Uganda' );
	update_option( 'timezone_string', 'Africa/Kampala' );
	$log[] = 'Set site title, tagline and time zone (Africa/Kampala)';

	// 3. Clean addresses such as /health-tips/.
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules( true );
	$log[] = 'Switched permalinks to "Post name"';

	// 4. Move WordPress sample content to the trash (restorable from Trash).
	foreach ( array( array( 'sample-page', 'page' ), array( 'hello-world', 'post' ) ) as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $post && 'trash' !== $post->post_status ) {
			wp_trash_post( $post->ID );
			$log[] = 'Moved to trash: ' . $post->post_title;
		}
	}

	// 5. Starter health articles.
	$term = term_exists( 'Travel Notes', 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( 'Travel Notes', 'category', array( 'slug' => 'travel-notes-articles' ) );
	}
	$cat_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;

	foreach ( sat_setup_posts() as $article ) {
		if ( get_page_by_path( $article['slug'], OBJECT, 'post' ) ) {
			$log[] = 'Already exists, left unchanged: ' . $article['title'];
			continue;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_title'    => $article['title'],
					'post_name'     => $article['slug'],
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_excerpt'  => $article['excerpt'],
					'post_content'  => $article['content'],
					'post_category' => $cat_id ? array( $cat_id ) : array(),
				)
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			$log[] = 'Could not add article: ' . $article['title'];
			continue;
		}
		$image = sat_setup_find_attachment( $article['image'] );
		if ( $image ) {
			set_post_thumbnail( $id, $image );
		}
		$log[] = 'Added article: ' . $article['title'] . ( $image ? '' : ' (no featured image found for ' . $article['image'] . ')' );
	}

	return $log;
}

/* -------------------------------------------------------------------------
 * Admin screen
 * ---------------------------------------------------------------------- */

add_action(
	'admin_menu',
	function () {
		add_theme_page( "Resort setup", "Resort setup", 'manage_options', 'sat-setup', 'sat_setup_screen' );
	}
);

add_action(
	'admin_post_sat_run_setup',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'satellite-child' ), 403 );
		}
		check_admin_referer( 'sat_run_setup' );

		set_transient( 'sat_setup_log_' . get_current_user_id(), sat_run_site_setup(), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'themes.php?page=sat-setup&done=1' ) );
		exit;
	}
);

function sat_setup_screen() {
	$log = get_transient( 'sat_setup_log_' . get_current_user_id() );
	?>
	<div class="wrap">
		<h1>Resort setup</h1>

		<?php if ( isset( $_GET['done'] ) && is_array( $log ) ) : ?>
			<div class="notice notice-success"><p><strong>Setup finished.</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>">View the site</a></p></div>
			<ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php delete_transient( 'sat_setup_log_' . get_current_user_id() ); ?>
		<?php endif; ?>

		<p>This builds the whole site from the theme's designs in one step:</p>
		<ul style="list-style:disc;padding-left:1.5em">
			<li>Creates or refreshes the pages Home, Stay, Book Your Stay, Adventure, Great Lakes Museum, Food and Hall, Gallery, Contact, Review Your Stay and Travel Notes.</li>
			<li>Sets Home as the homepage and Travel Notes as the blog page.</li>
			<li>Sets the site title, tagline, time zone and "Post name" permalinks.</li>
			<li>Moves the WordPress "Hello world!" post and "Sample Page" to the trash.</li>
			<li>Adds the article "Things to Do in Kabale" (600+ words, for SEO), using blog-kabale.jpg from the Media Library as its picture.</li>
		</ul>
		<p><strong>Note:</strong> refreshing a page replaces its content with the latest design. Edits made directly to those pages will be overwritten. Existing articles are never changed.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="sat_run_setup">
			<?php wp_nonce_field( 'sat_run_setup' ); ?>
			<?php submit_button( 'Build the site', 'primary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}
