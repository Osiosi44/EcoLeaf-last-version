# EcoLeaf

EcoLeaf is a PHP/MySQL laundry booking application with customer accounts, a service catalog, pickup scheduling, order tracking, reviews, and an administrator workspace.

**Live demo:** [https://ecoleaf.wuaze.com/](https://ecoleaf.wuaze.com/)

The public demo is updated through a manual upload. Changes in this checkout do not automatically deploy to InfinityFree.

## Features

- **Services and prices:** Browse the live catalog and item options before booking.
- **Booking:** Choose quantities, preview a running total, enter a pickup date and address, and confirm an order. Prices are recalculated on the server; repeated submissions reuse the saved receipt.
- **Order management:** View pickup details, item prices, quantities, and status. Customers can cancel their own scheduled bookings.
- **Accounts:** Register, sign in, edit contact details, change passwords, and permanently delete an account after password confirmation.
- **Reviews:** Customers with completed orders can publish a rating and comment.
- **Administration:** Search customers, process orders through allowed statuses, edit catalog and option prices, create bookable services, and create additional administrators with password confirmation.
- **Interface:** Responsive green branding, local fonts and assets, keyboard navigation, inline feedback, and a procedural 3D washer with pause, rotation, reduced-motion support, and an image fallback.

The demo does not process payments, send email, or arrange real pickups. Use test details.

## Architecture

| Layer | Implementation |
| --- | --- |
| Frontend | Server-rendered HTML, CSS, and vanilla JavaScript |
| Illustration | Locally vendored Three.js 0.169.0; procedural geometry |
| Backend | PHP with MySQLi and mysqlnd |
| Database | MySQL-compatible relational database |
| Local services | PHP 8.4 / Apache and MariaDB 11.4 through Docker Compose |
| Demo hosting | InfinityFree shared PHP hosting |
| Development checks | Python HTTP integration tests, Playwright, and axe |

Production hosting does not need Node.js, a frontend build server, or remote font/CDN requests. Node.js and Python are development tools only.

```text
EcoLeaf/
|-- admin/                     # Guarded administrator pages and actions
|-- assets/                    # 3D scene, vendor library, local font, optimized photographs
|-- components/layout.php      # Shared layout, navigation, metadata, and UI helpers
|-- database/init.sql          # Fresh-install schema and service seed data
|-- database/migrations/       # Non-destructive upgrades for existing databases
|-- db/app.php                 # Auth, CSRF, validation, rate limiting, and data helpers
|-- db/                        # Authentication, profile, review, and order action handlers
|-- docs/                      # Deployment, audit, design brief, and asset provenance
|-- scripts/                   # Admin creation and deployment packaging
|-- tests/                     # Isolated integration and browser regression checks
|-- booking.php                # Service selection, pickup details, and order summary
|-- process_order.php          # Authoritative pricing and transactional order writes
|-- my_orders.php              # Customer order history and reviews
|-- profile.php                # Account details and account deletion
|-- compose.yaml               # Local application and database services
`-- styles.css                 # Shared responsive design system
```

The six business tables are `users`, `services`, `service_options`, `orders`, `order_items`, and `reviews`. `security_limits` stores temporary request counters and serializes account writes. The upgrade preserves existing business records and does not rewrite historical item prices.

## Local development

### Requirements

- Git and Docker Desktop with Linux containers and Docker Compose.
- For the regression suite: Python 3.10+ and Node.js 22+.

```bash
git clone https://github.com/Osiosi44/EcoLeaf-last-version.git
cd EcoLeaf-last-version
docker compose up -d --build
```

Open [http://localhost:8080](http://localhost:8080).

On the first startup, MariaDB imports `database/init.sql` into an empty database, including the catalog and options. Accounts are not seeded; register through the website. Database records persist in a Docker volume. Editing the initialization SQL does not update an existing volume.

For a local database created before this upgrade, apply `database/migrations/20261118_security.sql` to that database before using the upgraded authentication and action handlers.

```bash
# Stop services while retaining data
docker compose stop

# Restart services
docker compose up -d

# Inspect recent logs
docker compose logs --tail=100 web database
```

The web service binds to `127.0.0.1:8080`; the database has no published host port. Credentials in `compose.yaml` are development defaults.

### Create a local administrator

Set `ADMIN_USERNAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in the current shell environment, using a unique password of at least 12 characters, then run:

```bash
docker compose exec -e ADMIN_USERNAME -e ADMIN_EMAIL -e ADMIN_PASSWORD web php scripts/create-admin.php
```

Clear the password environment variable afterward. On shared hosting without CLI access, register an account, change its `role` to `admin` in phpMyAdmin, and sign in again. Further administrators can be created from the protected Admin account screen.

## Configuration

Copy `db/config.example.php` to `db/config.local.php` for shared hosting and enter the host-provided database details. The file is ignored by Git and excluded from deployment packages. The standard Docker setup supplies settings through environment variables and does not require a local config file.

| Variable | Purpose | Default |
| --- | --- | --- |
| `DB_HOST` | Database hostname | `localhost` |
| `DB_NAME` | Database name | `ecoleaf` |
| `DB_USER` | Database username | `ecoleaf` |
| `DB_PASSWORD` | Database password | Empty |
| `DB_PORT` | Database port | `3306` |
| `APP_TIMEZONE` | Booking-date timezone | `Africa/Addis_Ababa` |
| `SESSION_SECURE` | Force secure cookies behind a trusted HTTPS-terminating proxy | Unset |

Local config values override environment settings. Sessions expire after 30 minutes of inactivity. Email or password changes require the current password. Passwords are hashed, form writes require session-bound CSRF tokens, and admin permissions are checked against the current database record.

## Verification

Install the development dependencies and browser once:

```bash
npm ci
npx playwright install chromium
```

With the Compose database running and the web image built:

```bash
# HTTP integration checks plus authenticated browser checks
python tests/integration.py

# Public pages and responsive/accessibility checks against localhost:8080
npm run test:browser
```

Integration checks create a disposable database and a temporary web container on port 8081, then remove them. They do not reset the existing EcoLeaf database. Override `ECOLEAF_TEST_PORT` if that port is occupied. The integration runner includes authenticated browser checks when the Node development dependencies are installed.

Checks cover authorization, ownership, CSRF, validation, concurrent registration, decimal pricing, duplicate submissions, order transitions, account changes, reviews, throttling, and a full mobile booking. Browser checks cover 1440, 768, 390, and 320 pixel widths, WCAG A/AA rules through axe, mobile navigation, reduced motion, 3D controls, and the WebGL fallback. Reports and screenshots are written to ignored `artifacts/qa/`.

See [the audit](docs/AUDIT.md) for findings, changes, and verification limits. Chromium checks are not a substitute for physical-device, Safari, or assistive-technology testing.

## Deployment

Generate the hosting package from Windows:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/package-hosting.ps1
```

| Artifact | Use |
| --- | --- |
| `artifacts/ecoleaf-hosting.zip` | Active application files, assets, and bundled asset licenses |
| `artifacts/ecoleaf-upgrade.sql` | Non-destructive migration for an existing database |
| `artifacts/ecoleaf-database.sql` | Schema and seed data for a new, empty database only |
| `artifacts/DEPLOYMENT.md` | Upload, verification, and rollback instructions |

**For the existing demo:** back up the current files and database, import `ecoleaf-upgrade.sql`, then upload the extracted website contents into `htdocs`. Preserve the hosted `db/config.local.php`. Do not import the fresh-install SQL into the existing database.

See [deployment instructions](docs/DEPLOYMENT.md) for the complete process. The explicit package allowlist excludes credentials, Git metadata, test tools, and unused legacy assets. Production hosting needs PHP 8.1+ with MySQLi/mysqlnd, a MySQL-compatible database, and Apache-compatible `.htaccess` support.

## Design and assets

The redesign retains the original leaf logo and green EcoLeaf identity. Guidance was drawn from [Open 3D Skills](https://github.com/Moh4696/open-3d-skills), [Impeccable](https://github.com/pbakaus/impeccable), [Taste Skill](https://github.com/Leonxlnx/taste-skill), and Anthropic's [frontend-design and webapp-testing skills](https://github.com/anthropics/skills). Their readable guidance informed the work; their separate skill-specific launchers and design gates were not run.

See [the design system](DESIGN.md), [the design brief](docs/redesign-brief.md), and [asset provenance](docs/asset-provenance.md). Manrope and Three.js licenses are included with their shipped files. The original repository images remain unchanged. Active content imagery now uses distinct AI-generated illustrations for the five canonical services, the home story, and About; the 3D hero has its own captured poster fallback. [Exact image prompts](docs/image-prompts.json) document the generated assets.
