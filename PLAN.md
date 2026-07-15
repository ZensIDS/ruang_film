# Merchandise Commerce + Multi-Period Submission Workflow

## Summary
- Replace the hard-coded merchandise frontend with database-driven catalog data shared by the home page and the dedicated merchandise page.
- Add a real cart, checkout, invoice, manual bank-transfer payment proof flow, and the admin CRUD needed to manage merchandise, banks, and fixed-fee expeditions.
- Upgrade submission scheduling from one global row to many submission periods, then wire the role flow so `kurator` filters submissions and `juri` scores approved works and assigns final ranks.

## Key Changes
- Merchandise domain: add dedicated `merchandise_categories`, `merchandises`, `expeditions`, `carts`, `cart_items`, `orders`, `order_items`, `bank_accounts`, and a single-row general settings store for `payment_due_hours`.
- Merchandise fields: category relation, name, slug, image, price, optional discount field, weight in grams, stock qty, description/summary, active flag. Client pages will hide discount output even if the field exists.
- Expedition fields: label/service name, fixed fee, active flag. Because RajaOngkir is out of scope for this phase, checkout uses admin-managed static expedition options with manual fees.
- Order fields: invoice number, user, shipping snapshot from biodata, expedition + shipping fee, subtotal, total, status, payment deadline, payment proof path, verification metadata, rejection note. Use statuses `waiting_payment`, `waiting_verification`, `paid`, `payment_rejected`, and `expired`.
- Cart behavior: use a DB-backed cart per authenticated user. Guests can browse merchandise, but add-to-cart and checkout require login. Validate stock on add/update and reserve stock at checkout; restore stock only when an order is rejected or expires unpaid.
- Landing routes: replace the `/` and `/merchandise` closures with controller actions that load merchandise from the DB. Extract shared Blade partials for the merchandise section/card so home and merchandise pages use one source of markup and one source of query logic.
- Merchandise page behavior: server-side filter/search/sort with query params such as `q`, `category`, `sort`, and `page`; paginate 12 items per page so the desktop view shows 3 rows; change the badge from `NEW/LIMITED` to the merchandise category name; display `berat` and `qty_stock`; hide discount pricing on the client page.
- Cart/payment UI: add cart page, checkout page, invoice detail page, and a payment modal/pop-up on the invoice detail. The modal shows active Ruang Film bank accounts and lets the buyer upload `bukti transfer` before the computed deadline.
- Admin commerce UI: add CRUD for merchandise categories, merchandise, expeditions, and bank accounts, plus an admin order/invoice screen for payment verification and receipt preview.
- Submission periods: evolve `submission_settings` from “single current row” to “many periods” by adding a period label/month name and using active-date helpers instead of `first()`. Enforce no overlapping active periods.
- Film linkage: add `submission_setting_id` and `category_id` to `films` so each submission is tied to a specific period and keeps a snapshot of the competition category. This also fixes the current mismatch where film views reference `category_id` that is not in the migration.
- Participant submission flow: keep representative-only submission. One user account submits on behalf of the group; no new team-member table is added in this phase. Submission remains blocked unless biodata exists and there is an active submission period.
- Review flow: move away from numeric “magic” statuses in views/controllers. Add explicit curator status `pending/approved/rejected` plus optional curator note. Add per-jury optional score records in a separate table and store final `winner_rank` on the film for `Juara 1`, `Juara 2`, `Juara 3`, and beyond. Enforce unique ranks within a submission period.
- Roles/auth: fix login redirect and navigation so `admin`, `adminsub`, `kurator`, `juri`, and `peserta` can all access valid dashboards/screens. Replace the broken `users.index.kurator` path with real curator/jury submission review screens or add the missing controller method if that list still needs to exist.

## Test Plan
- Set up a stable DB-backed test environment first. Preferred path: enable sqlite `:memory:` in `phpunit.xml`; otherwise document and use a dedicated testing database.
- Add factories for submission periods, films, user details, merchandise categories, merchandise, expeditions, bank accounts, carts, orders, order items, and jury scores.
- Feature tests: merchandise listing pagination/filter/sort; cart add/update/remove with stock guards; checkout creates invoice totals, shipping fee, and payment deadline from settings; payment proof upload succeeds before deadline and fails after expiry; admin approve/reject payment updates order status and stock correctly.
- Feature tests: admin CRUD for merchandise, expeditions, and bank accounts; submission creation only during active period; curator-only approve/reject actions; jury scoring and unique winner rank enforcement; login redirect/menu visibility for every role.
- Regression tests: current submission countdown helpers resolve the correct active period, and home/merchandise pages both render DB merchandise instead of hard-coded arrays.

## Assumptions
- Merchandise categories stay separate from the current film/participant `categories` table.
- The home page uses the shared merchandise partial to show a preview subset, while the full merchandise page uses paginated data.
- Discount data can be stored for future use, but it remains hidden on client-facing merchandise screens now.
- RajaOngkir integration, guest checkout, shipping tracking, and explicit team-member records are out of scope for this phase.
