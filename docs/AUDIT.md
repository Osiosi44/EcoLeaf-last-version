# EcoLeaf upgrade findings and changes

This audit covers the local source and local test deployment. It is not a penetration-test certification or verification that the new package is live.

| Finding | Impact | Change |
| --- | --- | --- |
| Mutating forms lacked cross-site request protection; logout accepted GET. | An authenticated browser could be induced to submit unwanted actions. | Session-bound CSRF tokens on all mutating routes; POST-only logout; SameSite and HTTP-only cookies. |
| Administrator fragments did not consistently start sessions or enforce roles. | Sensitive customer data could be requested outside the main dashboard. | Database-backed role verification on every admin entry and action; guarded legacy URLs. |
| Customer names and emails were rendered without consistent escaping in admin tables. | Stored markup could execute in an administrator's browser. | Centralized HTML escaping, safe DOM text insertion, and a restrictive Content Security Policy. |
| Profile changes accepted weak input and replaced password hashes even for a blank password field. | Invalid records, unexpected password changes, and weak account protection. | Shared validation, blank-password preservation, password rehashing, and current-password verification for email or password changes. |
| Human-text limits counted UTF-8 bytes while the forms described character limits. | Names, addresses, reviews, or service descriptions in scripts such as Ethiopic could be rejected prematurely. | Unicode code-point lengths for human text and rejection of malformed UTF-8. Password limits remain byte-based for bcrypt. |
| Account deletion depended on a supplied/session username and lacked confirmation safeguards. | Weak ownership and accidental account loss. | Session user ID only, CSRF, current password, typed confirmation, transactional dependent-record removal, and last-administrator protection. |
| Repeated authentication attempts had no persistent request limit. | Password guessing and registration abuse. | Atomic database-backed request counters; login, registration, booking, review, and admin limits. |
| Registration duplicate checks were separate from the write. | Concurrent requests could create duplicate identities on older schemas. | Serialized account writes and duplicate checks inside a transaction; ambiguous legacy usernames are rejected at login. |
| Bookings looked up options by names and accepted past pickup dates. | Ambiguous item lookup and invalid bookings. | Numeric option IDs, bounded whole-number quantities, pickup date validation, decimal prices calculated in cents, and authoritative server totals. |
| Retried bookings could create another order. | Duplicate orders after a repeat click or uncertain response. | Session-bound booking tokens return the original receipt on a repeated submission. Transactional order and item writes are retained. |
| Order state changes were not constrained to a workflow. | Invalid or reversed fulfillment states. | Row-locked allowed transitions. Customers can cancel only their own scheduled orders. |
| Reviews lacked robust validation and eligibility checks. | Invalid ratings and unqualified or repeated submissions. | Rating/comment validation, completed-order eligibility, CSRF, and request limits. |
| Admin controls such as Add Service were incomplete. | Administrators could not complete tasks shown in the interface. | Working service creation, catalog/option-price editing, order transitions, customer search, pagination, and password-confirmed administrator creation. |
| The old interface used a hidden booking overlay, broad globals, blocking alerts, and large unused images. | Fragile interactions, poor mobile usability, and unnecessary transfers. | Dedicated booking and account pages, scoped JavaScript, inline feedback, responsive navigation, local assets, and an explicit deployment allowlist. |
| Error handlers displayed database errors or inserted exception text into scripts. | Internal information exposure and unsafe error output. | Server-side logging, generic public failures, escaped field-level errors, protected internal paths, and a custom 404 page. |
| The initial redesigned admin navigation overflowed at phone widths, and a table lacked keyboard scroll access. | Mobile and keyboard usability regression. | Constrained grid children, contained horizontal scrolling, and a focusable named table region; verified in the browser suite. |

## Verification

`tests/integration.py` creates a disposable database and web container and removes them afterward. It covers ownership, authorization, CSRF, validation, concurrent signup, server pricing, repeated submissions, order transitions, catalog editing, review eligibility, account changes, and throttling. The existing local database is not reset.

`tests/browser.cjs` checks public and authenticated pages at 1440, 768, 390, and 320 pixels, checks WCAG A/AA rules through axe, captures screenshots, and completes a mobile booking. It exercises mobile navigation, password visibility, reduced motion, illustration controls, and the WebGL fallback. Reports and screenshots are written under ignored `artifacts/qa/`.

## Limits

- Browser checks use local headless Chromium, not physical phones or Safari. Automated accessibility checks do not replace a full assistive-technology review.
- Email verification, password recovery, payment processing, outbound notifications, and real pickup dispatch are not implemented. No provider or credentials were supplied for these services; the interface does not advertise them.
- The upgrade cannot safely infer or repair historically incorrect item prices or quantities. Existing orders are preserved.
- Existing hosting settings and administrator choices remain operational responsibilities. Import the non-destructive upgrade SQL before uploading, preserve the private database config, and verify the public host afterward.
