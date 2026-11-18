# Upgrade the existing InfinityFree demo

The upgraded application uses the same PHP/MySQL hosting and existing customer records. It adds one small database table for request throttling. The public demo is https://ecoleaf.wuaze.com/.

## Before uploading

1. Export the current database through phpMyAdmin and download a copy of the current `htdocs` files. Keep both backups private.
2. In phpMyAdmin, select `if0_43466263_ecoleaf` and import **`ecoleaf-upgrade.sql`**. This only creates the request-throttling table if it does not already exist. It does not delete accounts, orders, reviews, or services. Re-running it is safe.
3. Do **not** import `ecoleaf-database.sql` into the existing database. That file creates and seeds tables for a new installation only.

## Upload the application

1. Extract `ecoleaf-hosting.zip` locally.
2. Upload its contents into the existing `htdocs` directory, preserving the paths and replacing matching files. Include `.htaccess` and the new `assets` directory.
3. Keep the hosted **`db/config.local.php`** unchanged. It contains your working database settings and is intentionally absent from the archive.
4. Confirm `index.php` is directly inside `htdocs`. No Node.js server or build process is needed on InfinityFree.
5. Refresh the website. Existing open forms should be reloaded after the upgrade because form protection is now required.

The package contains only active application files and assets. Old image files may remain on the host, but the new pages do not load them. Old internal component and MVC URLs are denied by `.htaccess`; legacy admin entry URLs now enforce authorization and redirect to the current dashboard.

## Verify the deployed site

- The header and tab display the leaf logo. Home, Services, About, account forms, and the mobile menu load correctly.
- The home illustration renders. If WebGL is unavailable, its dedicated washer poster remains visible and booking still works. Pause and rotate controls are available when the model loads.
- Register using test details, sign in, and confirm a booking with more than one quantity. Confirm that the total matches the listed prices.
- Open My orders. Confirm pickup details and cancel a scheduled booking. In-care or completed orders cannot be cancelled by the customer.
- Update a profile without changing its password; the original password must still work. Email or password changes require the current password.
- Sign in as an administrator, inspect Orders, Customers, Services, and Admin account, and update a test order through its allowed statuses.
- Submit a review after a completed order. Reviews appear publicly under the customer's username.
- Sign out. Order and admin URLs must require authorization.

## Operational notes

- The upgraded demo does not process payments, send email, or arrange real pickups.
- Inactive sessions expire after 30 minutes. A 403 form error means the page needs to be reloaded before retrying. A 429 response means the request limit has been reached; wait before retrying.
- If login or registration fails after uploading, confirm that `ecoleaf-upgrade.sql` was imported into the same database named in `db/config.local.php`. Detailed failures go to the host's PHP error log.
- Existing usernames and password hashes remain compatible. New registrations require 3–32 letters, numbers, underscores, or hyphens for usernames and passwords of 10–72 bytes.
- Existing order-item prices are preserved. Catalog updates affect future bookings. A legacy booking whose old code stored an incorrect line price cannot be reconstructed safely without the original submitted quantities and prices; the upgrade does not rewrite historical records.

## Rollback

Restore the previous application files from the private backup while keeping the working database config. The added `security_limits` table can remain; the previous version does not use it. No customer data migration needs to be reversed. Restore a database backup only if there is a separate reason to undo later data changes.
