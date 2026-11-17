# EcoLeaf

EcoLeaf is a PHP and MySQL web application for laundry service booking. Customers can browse services, create an account, schedule pickups, and track orders. An administrator dashboard provides access to orders, customer records, and the service catalog.

**Live demo:** [https://ecoleaf.wuaze.com/](https://ecoleaf.wuaze.com/)

## Features

- **Customer accounts:** Registration, login, logout, profile updates, and account deletion.
- **Service catalog:** Laundry services with item-specific options and prices.
- **Booking:** Service selection, quantities, pickup dates, and pickup addresses.
- **Order history:** Customers can view their orders, item details, totals, and statuses.
- **Reviews:** Authenticated customers can submit ratings and comments.
- **Administration:** Administrators can view orders, update order statuses, and access customer and service records.

Booking totals are calculated on the server using catalog prices. Orders and their line items are saved in a database transaction.

## Technology stack

| Layer | Implementation |
| --- | --- |
| Frontend | HTML, CSS, JavaScript, Swiper, Font Awesome |
| Backend | PHP with the MySQLi extension |
| Database | MySQL-compatible relational database |
| Local environment | Docker Compose, PHP 8.4 with Apache, MariaDB 11.4 |
| Demo hosting | InfinityFree |

## Project structure

```text
EcoLeaf/
|-- admin/                 # Administrator dashboard and views
|-- components/            # Shared header and footer
|-- database/init.sql      # Database schema and service seed data
|-- db/                    # Database access, authentication, and profile handlers
|-- docker/                # Local Apache configuration
|-- images/                # Website assets
|-- scripts/               # Administrator creation and deployment packaging
|-- compose.yaml           # Local application and database services
|-- Dockerfile             # PHP/Apache image with MySQLi
|-- index.php              # Default entry point
|-- home.php               # Landing page
|-- package.php            # Service catalog and booking interface
|-- process_order.php      # Booking validation and persistence
`-- my_orders.php          # Customer order history
```

PHP renders the pages and shared components. Form handlers process account and profile changes, while the booking interface submits requests using JavaScript. Authentication code includes separate model, view, and controller helper files under `db/`.

The database contains six tables: `users`, `services`, `service_options`, `orders`, `order_items`, and `reviews`. Foreign keys connect customers to orders and reviews, and orders to their line items.

## Local development

### Prerequisites

- Git
- Docker Desktop with Docker Compose and Linux containers enabled

### Start the application

```bash
git clone https://github.com/Osiosi44/EcoLeaf-last-version.git
cd EcoLeaf-last-version
docker compose up -d --build
```

Open [http://localhost:8080](http://localhost:8080).

On the first startup, MariaDB imports `database/init.sql` into an empty database. The import creates the schema and seeds the service catalog and options. Customer and administrator accounts are not seeded; register a customer through the application.

Database records persist in a Docker volume. Initialization runs only when that volume is empty; changes to `init.sql` are not automatically applied to an existing database.

```bash
# Stop the application without removing its data
docker compose stop

# Start it again
docker compose up -d

# Inspect application and database logs
docker compose logs --tail=100 web database
```

The web service binds to `127.0.0.1:8080`. The database is accessible within the Compose network and has no published host port. Credentials in `compose.yaml` are development defaults.

### Create a local administrator

Set `ADMIN_USERNAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in the current shell environment. Use a unique password of at least 12 characters, then run:

```bash
docker compose exec -e ADMIN_USERNAME -e ADMIN_EMAIL -e ADMIN_PASSWORD web php scripts/create-admin.php
```

The CLI script creates a new administrator and rejects an existing username or email. Clear the password environment variable afterward.

## Configuration

Database settings can be supplied through environment variables or an untracked `db/config.local.php` file.

| Variable | Purpose | Default |
| --- | --- | --- |
| `DB_HOST` | Database hostname | `localhost` |
| `DB_NAME` | Database name | `ecoleaf` |
| `DB_USER` | Database username | `ecoleaf` |
| `DB_PASSWORD` | Database password | Empty |
| `DB_PORT` | Database port | `3306` |
| `SESSION_SECURE` | Set to `1` when a trusted proxy terminates HTTPS | Unset |

For file-based configuration, copy `db/config.example.php` to `db/config.local.php` and enter the database credentials. Values in the local file override environment settings. Docker Compose supplies its own database settings, so this file is unnecessary for the standard local setup.

Session cookies use the current hostname, are HTTP-only, and use `SameSite=Lax`. The secure flag follows the request's HTTPS status or the `SESSION_SECURE` setting.

## Deployment

The live demo runs at [ecoleaf.wuaze.com](https://ecoleaf.wuaze.com/) on InfinityFree. The application requires PHP with MySQLi, a MySQL-compatible database, and Apache-compatible hosting for the included `.htaccess` rules.

To prepare a deployment package on Windows, run from the repository root:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/package-hosting.ps1
```

The script creates:

- `artifacts/ecoleaf-hosting.zip` - Application files and website assets.
- `artifacts/ecoleaf-database.sql` - Schema and service seed data for an empty database.

Packaging excludes Git metadata, local infrastructure, and credentials. Large JPEG and PNG images are scaled to a maximum dimension of 1600 pixels in the archive; source images remain unchanged. If `db/config.local.php` exists, the script stops to prevent packaging credentials.

### Deploy to InfinityFree

1. Create a hosting account, choose a subdomain, and create a MySQL database.
2. Import `ecoleaf-database.sql` into the empty database through phpMyAdmin. The SQL does not create or select a database; select the host-assigned database before importing.
3. Extract the website archive locally and upload its contents, including `.htaccess`, into the site's `htdocs` directory. Place `index.php` directly inside `htdocs`.
4. On the host, copy `db/config.example.php` to `db/config.local.php` and enter the database details from the hosting control panel.
5. Configure HTTPS and verify registration, login, booking, order history, and logout.

For an administrator account on shared hosting, register an account, change that account's `role` to `admin` in the `users` table through phpMyAdmin, and log in again.

## Verification

Local HTTP integration checks have covered public pages, registration, login and session persistence, booking quantities and decimal prices, server-calculated totals, order history, reviews, administrator access, and logout. Temporary test accounts and related records were removed after verification. Registration and login have also been confirmed on the hosted demo.

## Troubleshooting

- **Database unavailable:** Check the hostname, database name, username, and password against the hosting control panel. Confirm the SQL import completed. Connection details are logged to PHP's error log rather than displayed publicly.
- **Missing tables or services:** Import `database/init.sql` into the selected empty database. With Docker, initialization only runs on a new database volume.
- **Homepage missing:** Confirm `index.php` is directly inside the document root and that `.htaccess` was uploaded.

Use test data on the public demo. Keep `db/config.local.php`, environment files, and database credentials out of version control.
