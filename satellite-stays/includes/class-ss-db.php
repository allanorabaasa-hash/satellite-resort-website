<?php
/**
 * Database layer for Satellite Stays (accommodation booking module).
 *
 * Tables (prefix wp_ss_):
 *   accommodation_types  1 ──< units          (a type has many rooms/cottages)
 *   accommodation_types  1 ──< bookings       (a guest books a type)
 *   units                1 ──< bookings       (staff assign a unit on confirmation)
 *   bookings             1 ──  reviews        (one verified review per stay)
 *
 * Every query containing user input goes through $wpdb->prepare().
 * Table names come from the site's own prefix, never from user input.
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

class SS_DB {

	/** Booking statuses that hold a room (count towards availability). */
	const HOLDING = "'pending','confirmed','checked_in'";

	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'ss_' . $name;
	}

	/* =================================================================
	 * Installation
	 * ================================================================= */

	public static function install() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		$t       = self::table( 'accommodation_types' );
		$u       = self::table( 'units' );
		$b       = self::table( 'bookings' );

		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $t (
				id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
				name            VARCHAR(120)     NOT NULL,
				description     VARCHAR(600)     DEFAULT NULL,
				max_guests      TINYINT UNSIGNED NOT NULL DEFAULT 2,
				price_per_night DECIMAL(12,2)    DEFAULT NULL,
				image           VARCHAR(255)     DEFAULT NULL,
				sort_order      SMALLINT         NOT NULL DEFAULT 0,
				is_active       TINYINT(1)       NOT NULL DEFAULT 1,
				created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_type_name (name)
			) ENGINE=InnoDB $charset"
		);

		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $u (
				id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				type_id    BIGINT UNSIGNED NOT NULL,
				name       VARCHAR(80)     NOT NULL,
				is_active  TINYINT(1)      NOT NULL DEFAULT 1,
				notes      VARCHAR(255)    DEFAULT NULL,
				created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_unit_name (name),
				KEY idx_unit_type (type_id),
				CONSTRAINT fk_ss_unit_type FOREIGN KEY (type_id) REFERENCES $t (id)
					ON UPDATE CASCADE ON DELETE RESTRICT
			) ENGINE=InnoDB $charset"
		);

		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $b (
				id               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
				type_id          BIGINT UNSIGNED  NOT NULL,
				unit_id          BIGINT UNSIGNED  DEFAULT NULL,
				guest_name       VARCHAR(120)     NOT NULL,
				phone            VARCHAR(30)      NOT NULL,
				email            VARCHAR(120)     DEFAULT NULL,
				check_in         DATE             NOT NULL,
				check_out        DATE             NOT NULL,
				adults           TINYINT UNSIGNED NOT NULL DEFAULT 1,
				children         TINYINT UNSIGNED NOT NULL DEFAULT 0,
				channel          VARCHAR(20)      NOT NULL DEFAULT 'website',
				status           VARCHAR(20)      NOT NULL DEFAULT 'pending',
				special_requests VARCHAR(600)     DEFAULT NULL,
				staff_note       VARCHAR(600)     DEFAULT NULL,
				created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY idx_booking_dates (check_in, check_out),
				KEY idx_booking_status (status),
				KEY idx_booking_type (type_id),
				KEY idx_booking_unit (unit_id),
				CONSTRAINT fk_ss_booking_type FOREIGN KEY (type_id) REFERENCES $t (id)
					ON UPDATE CASCADE ON DELETE RESTRICT,
				CONSTRAINT fk_ss_booking_unit FOREIGN KEY (unit_id) REFERENCES $u (id)
					ON UPDATE CASCADE ON DELETE SET NULL,
				CONSTRAINT chk_ss_booking_dates CHECK (check_out > check_in)
			) ENGINE=InnoDB $charset"
		);

		$r = self::table( 'reviews' );
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS $r (
				id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
				booking_id   BIGINT UNSIGNED  DEFAULT NULL,
				type_id      BIGINT UNSIGNED  NOT NULL,
				display_name VARCHAR(60)      NOT NULL,
				rating       TINYINT UNSIGNED NOT NULL,
				title        VARCHAR(120)     NOT NULL,
				body         VARCHAR(1500)    NOT NULL,
				stayed_on    DATE             NOT NULL,
				status       VARCHAR(20)      NOT NULL DEFAULT 'pending',
				created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_review_booking (booking_id),
				KEY idx_review_status (status),
				CONSTRAINT fk_ss_review_booking FOREIGN KEY (booking_id) REFERENCES $b (id)
					ON UPDATE CASCADE ON DELETE SET NULL,
				CONSTRAINT fk_ss_review_type FOREIGN KEY (type_id) REFERENCES $t (id)
					ON UPDATE CASCADE ON DELETE RESTRICT,
				CONSTRAINT chk_ss_review_rating CHECK (rating BETWEEN 1 AND 5)
			) ENGINE=InnoDB $charset"
		);

		// Starter accommodation from the resort's public website. Guest numbers,
		// unit counts and prices must be confirmed with the resort.
		if ( 0 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t" ) ) {
			$starter = array(
				array( 'Wooden Cottage', 'Eco-friendly wooden cottage among the Kabale hills, with garden views.', 2, 'Cottage 1', 'cottage.jpg' ),
				array( 'Room', 'Comfortable en-suite room close to the restaurant and terrace.', 2, 'Room 1', 'room.jpg' ),
				array( 'Guest House', 'A private guest house for families or small groups.', 6, 'Guest House', 'guest-house.jpg' ),
				array( 'Group Accommodation', 'Shared accommodation for school trips, teams and large groups.', 20, 'Group Block', 'group.jpg' ),
				array( 'Camping', 'Pitch a tent on the grounds and wake up to the hills. Tents available on request.', 4, 'Camp Pitch 1', 'camping.jpg' ),
			);
			foreach ( $starter as $i => $row ) {
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO $t (name, description, max_guests, image, sort_order) VALUES (%s, %s, %d, %s, %d)",
						$row[0],
						$row[1],
						$row[2],
						$row[4],
						$i
					)
				);
				$type_id = (int) $wpdb->insert_id;
				$wpdb->query( $wpdb->prepare( "INSERT INTO $u (type_id, name) VALUES (%d, %s)", $type_id, $row[3] ) );
			}
		}

		self::install_roles();
		update_option( 'ss_db_version', SS_VERSION );
	}

	/** "Resort Staff" role: can manage bookings and accommodation, nothing else. */
	public static function install_roles() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'ss_manage' );
		}
		if ( ! get_role( 'resort_staff' ) ) {
			add_role(
				'resort_staff',
				'Resort Staff',
				array(
					'read'      => true,
					'ss_manage' => true,
				)
			);
		}
	}

	/* =================================================================
	 * Availability
	 * ================================================================= */

	/**
	 * Every active accommodation type with how many units are free for the
	 * whole stay. Two stays overlap when one starts before the other ends.
	 *
	 * free = active units of the type − holding bookings of the type that overlap.
	 */
	public static function availability( $check_in, $check_out ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.*,
				        COALESCE(units.total, 0) AS units_total,
				        COALESCE(taken.booked, 0) AS units_booked,
				        GREATEST(COALESCE(units.total, 0) - COALESCE(taken.booked, 0), 0) AS units_free
				   FROM $t AS t
				   LEFT JOIN (
				        SELECT type_id, COUNT(*) AS total FROM $u WHERE is_active = 1 GROUP BY type_id
				   ) AS units ON units.type_id = t.id
				   LEFT JOIN (
				        SELECT type_id, COUNT(*) AS booked FROM $b
				         WHERE status IN (" . self::HOLDING . ")
				           AND check_in < %s AND check_out > %s
				         GROUP BY type_id
				   ) AS taken ON taken.type_id = t.id
				  WHERE t.is_active = 1
				  ORDER BY t.sort_order ASC, t.name ASC",
				$check_out,
				$check_in
			)
		);
	}

	/**
	 * Free rooms of one type for every night in [from, to).
	 * One query fetches the overlapping bookings;
	 * the per-night count is worked out in PHP.
	 *
	 * @return array<int, array{date:string, free:int}>
	 */
	public static function nightly_free( $type_id, $from, $to ) {
		global $wpdb;
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );

		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $u WHERE type_id = %d AND is_active = 1", $type_id ) );
		$stays = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT check_in, check_out FROM $b
				  WHERE type_id = %d AND status IN (" . self::HOLDING . ")
				    AND check_in < %s AND check_out > %s",
				$type_id,
				$to,
				$from
			)
		);

		$out = array();
		for ( $day = $from; $day < $to; $day = gmdate( 'Y-m-d', strtotime( $day . ' +1 day' ) ) ) {
			$taken = 0;
			foreach ( $stays as $s ) {
				if ( $s->check_in <= $day && $s->check_out > $day ) {
					++$taken;
				}
			}
			$out[] = array(
				'date' => $day,
				'free' => max( 0, $total - $taken ),
			);
		}
		return $out;
	}

	/** Free units of one type for a stay (used just before saving a request). */
	public static function free_count( $type_id, $check_in, $check_out, $exclude_booking = 0 ) {
		global $wpdb;
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );

		$total  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $u WHERE type_id = %d AND is_active = 1", $type_id ) );
		$booked = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $b
				  WHERE type_id = %d AND id <> %d AND status IN (" . self::HOLDING . ")
				    AND check_in < %s AND check_out > %s",
				$type_id,
				$exclude_booking,
				$check_out,
				$check_in
			)
		);
		return max( 0, $total - $booked );
	}

	/** Units of a type with no overlapping holding booking (for assigning a room). */
	public static function free_units( $type_id, $check_in, $check_out, $exclude_booking = 0 ) {
		global $wpdb;
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.* FROM $u AS u
				  WHERE u.type_id = %d AND u.is_active = 1
				    AND NOT EXISTS (
				        SELECT 1 FROM $b AS bk
				         WHERE bk.unit_id = u.id AND bk.id <> %d
				           AND bk.status IN (" . self::HOLDING . ")
				           AND bk.check_in < %s AND bk.check_out > %s
				    )
				  ORDER BY u.name ASC",
				$type_id,
				$exclude_booking,
				$check_out,
				$check_in
			)
		);
	}

	/* =================================================================
	 * Accommodation types
	 * ================================================================= */

	/** READ (list) — LEFT JOINs count units and bookings per type. */
	public static function get_types( $active_only = false ) {
		global $wpdb;
		$t     = self::table( 'accommodation_types' );
		$u     = self::table( 'units' );
		$b     = self::table( 'bookings' );
		$where = $active_only ? 'WHERE t.is_active = 1' : '';

		return $wpdb->get_results(
			"SELECT t.*,
			        (SELECT COUNT(*) FROM $u WHERE type_id = t.id AND is_active = 1) AS unit_count,
			        (SELECT COUNT(*) FROM $b WHERE type_id = t.id) AS booking_count
			   FROM $t AS t
			   $where
			  ORDER BY t.sort_order ASC, t.name ASC"
		);
	}

	public static function get_type( $id ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE id = %d", $id ) );
	}

	public static function insert_type( array $d ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		return $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $t (name, description, max_guests, price_per_night, image, sort_order, is_active)
				 VALUES (%s, %s, %d, NULLIF(%s, ''), NULLIF(%s, ''), %d, %d)",
				$d['name'],
				$d['description'],
				$d['max_guests'],
				$d['price_per_night'],
				$d['image'],
				$d['sort_order'],
				$d['is_active']
			)
		);
	}

	public static function update_type( $id, array $d ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE $t
				    SET name = %s, description = %s, max_guests = %d, price_per_night = NULLIF(%s, ''),
				        image = NULLIF(%s, ''), sort_order = %d, is_active = %d
				  WHERE id = %d",
				$d['name'],
				$d['description'],
				$d['max_guests'],
				$d['price_per_night'],
				$d['image'],
				$d['sort_order'],
				$d['is_active'],
				$id
			)
		);
	}

	/** DELETE. Refused by the foreign keys while units or bookings use the type. */
	public static function delete_type( $id ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $t WHERE id = %d", $id ) );
	}

	/* =================================================================
	 * Units (individual rooms, cottages, pitches)
	 * ================================================================= */

	/** READ (list) — INNER JOIN shows each unit's type. */
	public static function get_units() {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		$u = self::table( 'units' );
		return $wpdb->get_results(
			"SELECT u.*, t.name AS type_name
			   FROM $u AS u
			  INNER JOIN $t AS t ON t.id = u.type_id
			  ORDER BY t.sort_order ASC, u.name ASC"
		);
	}

	public static function get_unit( $id ) {
		global $wpdb;
		$u = self::table( 'units' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $u WHERE id = %d", $id ) );
	}

	public static function insert_unit( array $d ) {
		global $wpdb;
		$u = self::table( 'units' );
		return $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $u (type_id, name, is_active, notes) VALUES (%d, %s, %d, NULLIF(%s, ''))",
				$d['type_id'],
				$d['name'],
				$d['is_active'],
				$d['notes']
			)
		);
	}

	public static function update_unit( $id, array $d ) {
		global $wpdb;
		$u = self::table( 'units' );
		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE $u SET type_id = %d, name = %s, is_active = %d, notes = NULLIF(%s, '') WHERE id = %d",
				$d['type_id'],
				$d['name'],
				$d['is_active'],
				$d['notes'],
				$id
			)
		);
	}

	/** DELETE. Bookings that used the unit keep their record (unit_id becomes NULL). */
	public static function delete_unit( $id ) {
		global $wpdb;
		$u = self::table( 'units' );
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $u WHERE id = %d", $id ) );
	}

	/* =================================================================
	 * Bookings
	 * ================================================================= */

	/**
	 * READ (list) — joins type and (optional) unit.
	 *
	 * @param string $view upcoming | requests | in_house | arrivals | departures | past | all
	 */
	public static function get_bookings( $view = 'upcoming' ) {
		global $wpdb;
		$t     = self::table( 'accommodation_types' );
		$u     = self::table( 'units' );
		$b     = self::table( 'bookings' );
		$today = current_time( 'Y-m-d' );

		switch ( $view ) {
			case 'requests':
				$where = "WHERE b.status = 'pending'";
				$order = 'b.check_in ASC';
				break;
			case 'in_house':
				$where = "WHERE b.status = 'checked_in'";
				$order = 'b.check_out ASC';
				break;
			case 'arrivals':
				$where = $wpdb->prepare( "WHERE b.check_in = %s AND b.status IN ('pending','confirmed')", $today );
				$order = 'b.guest_name ASC';
				break;
			case 'departures':
				$where = $wpdb->prepare( "WHERE b.check_out = %s AND b.status = 'checked_in'", $today );
				$order = 'b.guest_name ASC';
				break;
			case 'past':
				$where = $wpdb->prepare( 'WHERE b.check_out < %s', $today );
				$order = 'b.check_in DESC';
				break;
			case 'all':
				$where = '';
				$order = 'b.check_in DESC';
				break;
			default:
				$where = $wpdb->prepare( 'WHERE b.check_out >= %s AND b.status IN (' . self::HOLDING . ')', $today );
				$order = 'b.check_in ASC';
		}

		return $wpdb->get_results(
			"SELECT b.*, t.name AS type_name, t.price_per_night, u.name AS unit_name,
			        DATEDIFF(b.check_out, b.check_in) AS nights
			   FROM $b AS b
			  INNER JOIN $t AS t ON t.id = b.type_id
			   LEFT JOIN $u AS u ON u.id = b.unit_id
			   $where
			  ORDER BY $order
			  LIMIT 300"
		);
	}

	public static function get_booking( $id ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*, t.name AS type_name, t.price_per_night, u.name AS unit_name,
				        DATEDIFF(b.check_out, b.check_in) AS nights
				   FROM $b AS b
				  INNER JOIN $t AS t ON t.id = b.type_id
				   LEFT JOIN $u AS u ON u.id = b.unit_id
				  WHERE b.id = %d",
				$id
			)
		);
	}

	/** CREATE. Returns the new ID, or false. */
	public static function insert_booking( array $d ) {
		global $wpdb;
		$b  = self::table( 'bookings' );
		$ok = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $b (type_id, unit_id, guest_name, phone, email, check_in, check_out, adults, children,
				                 channel, status, special_requests, staff_note)
				 VALUES (%d, NULLIF(%d, 0), %s, %s, NULLIF(%s, ''), %s, %s, %d, %d, %s, %s, NULLIF(%s, ''), NULLIF(%s, ''))",
				$d['type_id'],
				$d['unit_id'],
				$d['guest_name'],
				$d['phone'],
				$d['email'],
				$d['check_in'],
				$d['check_out'],
				$d['adults'],
				$d['children'],
				$d['channel'],
				$d['status'],
				$d['special_requests'],
				$d['staff_note']
			)
		);
		return $ok ? (int) $wpdb->insert_id : false;
	}

	/** UPDATE. */
	public static function update_booking( $id, array $d ) {
		global $wpdb;
		$b = self::table( 'bookings' );
		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE $b
				    SET type_id = %d, unit_id = NULLIF(%d, 0), guest_name = %s, phone = %s, email = NULLIF(%s, ''),
				        check_in = %s, check_out = %s, adults = %d, children = %d, channel = %s, status = %s,
				        special_requests = NULLIF(%s, ''), staff_note = NULLIF(%s, '')
				  WHERE id = %d",
				$d['type_id'],
				$d['unit_id'],
				$d['guest_name'],
				$d['phone'],
				$d['email'],
				$d['check_in'],
				$d['check_out'],
				$d['adults'],
				$d['children'],
				$d['channel'],
				$d['status'],
				$d['special_requests'],
				$d['staff_note'],
				$id
			)
		);
	}

	public static function set_booking_status( $id, $status, $unit_id = null ) {
		global $wpdb;
		$b = self::table( 'bookings' );
		if ( null === $unit_id ) {
			return $wpdb->query( $wpdb->prepare( "UPDATE $b SET status = %s WHERE id = %d", $status, $id ) );
		}
		return $wpdb->query( $wpdb->prepare( "UPDATE $b SET status = %s, unit_id = NULLIF(%d, 0) WHERE id = %d", $status, $unit_id, $id ) );
	}

	public static function delete_booking( $id ) {
		global $wpdb;
		$b = self::table( 'bookings' );
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $b WHERE id = %d", $id ) );
	}

	/** Is this unit already taken for these nights by another holding booking? */
	public static function unit_taken( $unit_id, $check_in, $check_out, $exclude_booking = 0 ) {
		global $wpdb;
		$b = self::table( 'bookings' );
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $b
				  WHERE unit_id = %d AND id <> %d AND status IN (" . self::HOLDING . ")
				    AND check_in < %s AND check_out > %s",
				$unit_id,
				$exclude_booking,
				$check_out,
				$check_in
			)
		);
	}

	/**
	 * Bookings that touch a date range, with unit and type (for the occupancy calendar).
	 */
	public static function bookings_between( $from, $to ) {
		global $wpdb;
		$t = self::table( 'accommodation_types' );
		$u = self::table( 'units' );
		$b = self::table( 'bookings' );
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.id, b.type_id, b.unit_id, b.guest_name, b.check_in, b.check_out, b.status,
				        t.name AS type_name, u.name AS unit_name
				   FROM $b AS b
				  INNER JOIN $t AS t ON t.id = b.type_id
				   LEFT JOIN $u AS u ON u.id = b.unit_id
				  WHERE b.status IN (" . self::HOLDING . ") AND b.check_in < %s AND b.check_out > %s",
				$to,
				$from
			)
		);
	}

	/* =================================================================
	 * Reviews (verified: each one belongs to a checked-out booking)
	 * ================================================================= */

	/** Approved reviews for the website — JOIN shows which accommodation was stayed in. */
	public static function get_public_reviews( $limit = 6 ) {
		global $wpdb;
		$r = self::table( 'reviews' );
		$t = self::table( 'accommodation_types' );
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.id, r.display_name, r.rating, r.title, r.body, r.stayed_on, t.name AS type_name
				   FROM $r AS r
				  INNER JOIN $t AS t ON t.id = r.type_id
				  WHERE r.status = 'approved'
				  ORDER BY r.stayed_on DESC, r.id DESC
				  LIMIT %d",
				$limit
			)
		);
	}

	/** Average rating and count of approved reviews. */
	public static function review_summary() {
		global $wpdb;
		$r = self::table( 'reviews' );
		return $wpdb->get_row( "SELECT COUNT(*) AS total, ROUND(AVG(rating), 1) AS average FROM $r WHERE status = 'approved'" );
	}

	/** All reviews for moderation — LEFT JOIN to the booking (which may have been purged). */
	public static function get_reviews( $status = '' ) {
		global $wpdb;
		$r     = self::table( 'reviews' );
		$t     = self::table( 'accommodation_types' );
		$b     = self::table( 'bookings' );
		$where = $status ? $wpdb->prepare( 'WHERE r.status = %s', $status ) : '';
		return $wpdb->get_results(
			"SELECT r.*, t.name AS type_name, b.guest_name, b.phone
			   FROM $r AS r
			  INNER JOIN $t AS t ON t.id = r.type_id
			   LEFT JOIN $b AS b ON b.id = r.booking_id
			   $where
			  ORDER BY r.created_at DESC
			  LIMIT 300"
		);
	}

	public static function review_for_booking( $booking_id ) {
		global $wpdb;
		$r = self::table( 'reviews' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $r WHERE booking_id = %d", $booking_id ) );
	}

	/** CREATE. Returns the new ID, or false (e.g. this stay was already reviewed). */
	public static function insert_review( array $d ) {
		global $wpdb;
		$r  = self::table( 'reviews' );
		$ok = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $r (booking_id, type_id, display_name, rating, title, body, stayed_on, status)
				 VALUES (%d, %d, %s, %d, %s, %s, %s, 'pending')",
				$d['booking_id'],
				$d['type_id'],
				$d['display_name'],
				$d['rating'],
				$d['title'],
				$d['body'],
				$d['stayed_on']
			)
		);
		return $ok ? (int) $wpdb->insert_id : false;
	}

	/** UPDATE (moderation). */
	public static function set_review_status( $id, $status ) {
		global $wpdb;
		$r = self::table( 'reviews' );
		return $wpdb->query( $wpdb->prepare( "UPDATE $r SET status = %s WHERE id = %d", $status, $id ) );
	}

	public static function delete_review( $id ) {
		global $wpdb;
		$r = self::table( 'reviews' );
		return $wpdb->query( $wpdb->prepare( "DELETE FROM $r WHERE id = %d", $id ) );
	}

	public static function stats() {
		global $wpdb;
		$b     = self::table( 'bookings' );
		$u     = self::table( 'units' );
		$today = current_time( 'Y-m-d' );
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					(SELECT COUNT(*) FROM $b WHERE check_in = %s AND status IN ('pending','confirmed')) AS arrivals,
					(SELECT COUNT(*) FROM $b WHERE check_out = %s AND status = 'checked_in') AS departures,
					(SELECT COUNT(*) FROM $b WHERE status = 'checked_in') AS in_house,
					(SELECT COUNT(*) FROM $b WHERE status = 'pending') AS requests,
					(SELECT COUNT(*) FROM " . self::table( 'reviews' ) . " WHERE status = 'pending') AS reviews_waiting,
					(SELECT COUNT(*) FROM $u WHERE is_active = 1) AS units,
					(SELECT COUNT(*) FROM $b WHERE status IN ('confirmed','checked_in') AND check_in <= %s AND check_out > %s) AS occupied_tonight",
				$today,
				$today,
				$today,
				$today
			)
		);
	}

	/** Delete finished (checked-out/cancelled) bookings older than N days. */
	public static function purge( $days ) {
		global $wpdb;
		$b = self::table( 'bookings' );
		return (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $b WHERE status IN ('checked_out','cancelled') AND check_out < DATE_SUB(%s, INTERVAL %d DAY)",
				current_time( 'Y-m-d' ),
				$days
			)
		);
	}
}
