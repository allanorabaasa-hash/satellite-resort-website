# Satellite Holiday Resort — Website

Practical exam project for **BIT 2103 Internet Technologies & Advanced Website Development**,
Mountains of the Moon University (BCS, Year 2, Semester I 2026/27).

- **Client:** Satellite Holiday Resort & Great Lakes Museum, Muhanga, Kabale–Mbarara Road, Kabale
- **Developer:** Allan
- **Platform:** WordPress (CMS) with a custom child theme and a custom booking plugin
- **Local environment:** Kali Linux, Apache, MariaDB, PHP
- **Live server:** cPanel hosting on the project's own domain (to be added)

## Why a rebuild

The resort's current site (WordPress + Elementor) has no online booking, no rates, inconsistent
links and, most seriously, **injected spam**: hidden links to online casinos and betting sites,
which points to a compromised site and damages search ranking and trust. This project rebuilds
the site cleanly, adds real online booking, and hardens it (see the security report).

## Why WordPress

| Client requirement | How WordPress meets it |
|---|---|
| Staff with little technical experience must update content and manage bookings | Block editor, custom admin screens, a limited "Resort Staff" role |
| Take booking requests online without double-booking | Custom plugin `satellite-stays` with its own MySQL tables |
| Showcase cottages, adventure park, museum, food and hall with lots of photos | Media Library; photos swap in by file name with no code |
| Fast, secure, findable on Google | Caching, security and SEO plugins; regular updates |
| Low running cost | Runs on any standard cPanel host |

## Repository layout

```
satellite-resort-website/
├── satellite-child/            Child theme of Twenty Twenty-Five
│   ├── style.css               Theme header + all custom CSS
│   ├── theme.json              Highland palette, Bricolage Grotesque + Figtree, layout
│   ├── functions.php           Assets, photo lookup by file name, WhatsApp button
│   ├── inc/setup.php           Appearance → Resort setup (one-click page build)
│   ├── parts/, templates/      Custom header, footer and Travel Notes (blog) template
│   ├── patterns/               Hero slider + every page design
│   └── assets/                 Fonts (SIL OFL), slider.js, contour-line motif, placeholder
├── satellite-stays/            Custom plugin: accommodation booking module
│   ├── satellite-stays.php     Bootstrap, settings, activation/upgrade
│   ├── includes/class-ss-db.php      All SQL (prepared statements)
│   ├── includes/class-ss-public.php  Availability search, room cards, booking form, emails
│   ├── includes/class-ss-admin.php   Overview calendar, bookings, accommodation, rooms, settings
│   ├── assets/                 public.css, admin.css, dates.js
│   └── uninstall.php           Drops the module's tables and role
└── README.md
```

## Custom module: Satellite Stays

Three related MySQL tables (prefix `wp_ss_`):

```
accommodation_types (id, name, description, max_guests, price_per_night, image, sort_order, is_active)
    1 ──< units    (id, type_id → accommodation_types.id, name, is_active, notes)
    1 ──< bookings (id, type_id → accommodation_types.id, unit_id → units.id (nullable),
                    guest_name, phone, email, check_in, check_out, adults, children,
                    channel, status, special_requests, staff_note)
```

- Foreign keys: a type can't be deleted while rooms or bookings use it (`RESTRICT`); deleting a room
  keeps its past bookings (`SET NULL`); a `CHECK` makes check-out later than check-in.
- **Availability:** two stays overlap when `check_in < other.check_out AND check_out > other.check_in`.
  Free rooms = active rooms of the type − overlapping bookings that hold a room
  (`pending`, `confirmed`, `checked_in`). The site can't overbook, but back-to-back stays are allowed.
- **JOINs:** availability (types + aggregated units + aggregated overlapping bookings), booking lists
  (bookings + types + units), room list (units + types), occupancy calendar (bookings + types + units).
- **Full CRUD** on all three tables in wp-admin, every query with `$wpdb->prepare()`.

| Who | What they do |
|---|---|
| Resort owner (Administrator) | Everything, including Settings |
| Resort Staff (custom role) | Bookings, accommodation and rooms only (capability `ss_manage`) |
| Guests (website) | **Read** live availability; **create** booking requests |
| Examiner | Temporary administrator account, deleted after marking |

**Booking flow:** guest searches dates → sees what's free and the price (or "Rates on request") →
sends a request → room is held as `pending` → resort is emailed → staff **Confirm** (a free room is
assigned automatically and the guest is emailed) → **Check in** → **Check out**. WhatsApp, phone,
walk-in and tour-agent bookings are added by staff so the online availability stays correct.

**Security:** prepared statements, capability and nonce checks on every admin action, sanitised
input and escaped output, a hidden spam trap, a per-visitor rate limit, a consent checkbox, and a
purge tool for old guest records.

## Website shortcodes

`[ss_availability]` date search with results · `[ss_availability compact="1" action="/stay/"]` search
box only · `[ss_rooms]` accommodation cards · `[ss_booking_form]` booking request form.

## Local setup (Kali)

```bash
# one-time
sudo mkdir -p /opt/satellite-resort-website && sudo chown $USER:$USER /opt/satellite-resort-website
git clone https://github.com/allanorabaasa-hash/satellite-resort-website.git /opt/satellite-resort-website
WP=/var/www/html/satellite/wp-content
sudo ln -s /opt/satellite-resort-website/satellite-child $WP/themes/satellite-child
sudo ln -s /opt/satellite-resort-website/satellite-stays $WP/plugins/satellite-stays

# every update
cd /opt/satellite-resort-website && git pull
```

Then activate the theme and plugin, and click **Appearance → Resort setup → Build the site**.

## Photos

Upload these to the Media Library with exactly these names (the site finds them automatically):
`hero-1.jpg` `hero-2.jpg` `hero-3.jpg` · `cottage.jpg` `room.jpg` `guest-house.jpg` `group.jpg`
`camping.jpg` · `zip-lining.jpg` `pirate-ship.jpg` `obstacle-course.jpg` `mountain-biking.jpg`
`mountain-climbing.jpg` `kids-park.jpg` `games.jpg` · `museum.jpg` `tree-of-life.jpg` `statues.jpg`
`gardens.jpg` `gift-shop.jpg` · `restaurant.jpg` `hall.jpg` · `gallery-1.jpg` … `gallery-9.jpg` ·
`blog-kabale.jpg`.

## Status

- [x] Booking module: availability, booking requests, emails, staff calendar and workflow
- [x] Child theme and every page design; one-click setup; 600+ word SEO article
- [ ] Client consent, requirements meeting and lecturer approval
- [ ] Real photos, room counts, guest limits and rates from the resort
- [ ] Deployment to cPanel, SSL, hardening, SEO and speed tests
