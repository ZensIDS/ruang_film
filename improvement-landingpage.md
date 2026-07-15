# Landing, Submission Settings, and General-Buyer Revision Plan

## Summary
- Move the homepage away from auto-derived landing data for the “What Happened Last Year” grid, counters, catalog file, and competition timeline so admin manages them from `Setting Submission`.
- Restore legacy landing/program content from commit `3a79629135679ba411fe82bbb18a0eb9f76bd57b` where requested: Special Feature footer details, exact FAQ copy/accordion on `/program`, `Coming Soon` CTA, and the missing collaborator/partner sections.
- Add a real non-participant buyer flow with a new `umum` user role, buyer-friendly auth options, inline checkout biodata for buyers, and landing-styled biodata management outside the dashboard theme.

## Key Changes
- **`SubmissionSetting` schema and admin UI**
  - Add `last_year_featured_film_ids` as ordered JSON film IDs.
  - Add `timeline_items` as JSON objects with `period`, `title`, `description`, and `icon`.
  - Add four manual counter columns with fixed legacy labels: `last_year_stat_film_submitted`, `last_year_stat_special_films`, `last_year_stat_audience`, `last_year_stat_participants`.
  - Add `last_year_catalog_file` for uploaded catalog storage; keep `last_year_catalog_url` only as backward-compat fallback, not as the primary admin input.
  - Extend `fillable`, `casts`, factory defaults, clone-on-new-period behavior, validation, and the submission-setting form to manage the new fields.
  - In the setting form, replace the catalog URL text field with a file input, add a multi-select for featured films, add 4 numeric stat inputs, and add a repeater for timeline items.

- **Landing/program data flow**
  - Update `LandingController` so the first “What Happened Last Year” slide uses admin-selected films in saved order and the four manual counters instead of deriving them from the last closed period.
  - Keep the winner carousel slides sourced from the latest closed submission period exactly as today; only the first featured-films slide becomes admin-controlled.
  - Replace DB-driven landing competition categories with a fixed legacy 3-card dataset matching the old copy, images, and routes: `Umum Nasional`, `Pelajar Se - Jawa Timur`, and `Ekshibisi Lokal Pacitan`.
  - Replace `buildTimelineItems()` runtime generation with saved admin-managed timeline items; seed first/default values from the existing 5-step structure so old behavior has a starting point.
  - Make `/download/ekatalog` serve the current setting’s uploaded catalog file when present, while preserving the legacy fallback PDF and download logging.

- **Homepage rendering rules**
  - Restore the missing Special Feature footer content from the legacy version: the descriptive footer strip and the single stat block at the bottom of the card.
  - Source that restored Special Feature stat block from the current setting’s film submission count so the legacy `Film Submitted` footer is live data again.
  - On the homepage only, when the current submission period is fully closed, hide the block from `SPECIAL FEATURE PROGRAM` through the home merchandise section, then render the existing 4-card `PROGRAM HIGHLIGHT` section in that gap.
  - Do not apply that hide/swap behavior on `/program`, and do not apply it during “before open” countdown state.
  - Change the Festival Experience CTA copy from `Lihat Program` to `Coming Soon`, matching the legacy design.
  - Restore the `OFFICIAL COLLABORATOR` and `OFFICIAL PARTNERS` sections on the homepage using the legacy assets/layout.

- **Program page**
  - Keep the program page’s timeline, hardcoded category cards, and jury section.
  - Replace the simplified FAQ block with the full legacy accordion content and intro copy from the referenced commit, including the 8 existing Q&A items.
  - Reuse the same hardcoded category data and admin-managed timeline data as the homepage so both pages stay aligned.

- **Auth, roles, and buyer flow**
  - Introduce a new `users.role` value: `umum`.
  - Update login/register pages to expose a clear `Peserta` vs `Umum` choice.
  - Registration rules: `peserta` still requires an active submission period and a `category_id`; `umum` requires no category and is allowed even when submission is closed.
  - Authentication redirect rules: honor intended URLs first; otherwise send `umum` users to the landing-side commerce flow instead of `/dashboard`.
  - Update landing navbar/authenticated actions so `umum` users are not pushed toward the dashboard UX.
  - Guard film-submission flows so `umum` users cannot enter participant submission screens by mistake.

- **Biodata and checkout**
  - Convert `/biodata` to landing styling and make the form role-aware.
  - `peserta` keeps the existing participant fields and category-based wilayah restrictions.
  - `umum` gets a shorter buyer profile form focused on shipping data, still persisted in `user_details`.
  - Remove the dead standalone `/merchandise/biodata` view flow by redirecting it to `/biodata`.
  - In `checkout.show`, if the user role is `umum`, render inline biodata/shipping inputs directly on the checkout page instead of redirecting away.
  - In `checkout.store`, validate and save/update buyer biodata for `umum` before creating the order; keep the current participant checkout behavior intact.
  - Change the merchandise card cart CTA copy to a clearer explicit cart action instead of the current minimal `Tambah`, without redesigning cart mechanics.

- **Payment-proof path fix**
  - Add an `Order` URL helper for payment proof paths that safely handles stored relative paths, historical `storage/...`-style values, and full URLs.
  - Use that helper in both customer and admin invoice views so “Lihat bukti transfer” always opens the correct file.
  - Keep storage on the `public` disk under `payment-proofs`, and continue deleting/replacing prior files through normalized paths.

## Public Interfaces / Behavior Changes
- `users.role` now supports `umum`.
- Register form adds account-type input and makes `category_id` conditional.
- Checkout for `umum` accepts inline biodata fields in addition to existing expedition/order fields.
- `SubmissionSetting` gains admin-facing landing fields for featured film IDs, timeline items, 4 manual stats, and catalog file upload.
- `/download/ekatalog` becomes dynamic to the active/current landing setting’s uploaded catalog file.

## Test Plan
- Add feature coverage for admin saving landing featured films, manual stats, timeline items, and catalog uploads on submission settings.
- Update submission-setting clone tests so new periods inherit the new landing-managed fields.
- Update landing tests so the homepage shows admin-selected featured films and manual counters instead of auto-derived ones.
- Add homepage visibility tests for the closed-period state: hidden Special Feature/timeline/category/merchandise block, visible Program Highlight, visible collaborator/partner sections.
- Add program-page tests for hardcoded category cards, admin-managed timeline items, and restored legacy FAQ content.
- Add auth tests for `umum` registration without category during closed submission, and for `peserta` registration still requiring open submission plus category.
- Add checkout tests for `umum` inline biodata persistence and order creation, alongside regression coverage for current participant checkout.
- Add payment-proof URL regression tests so stored proof links resolve correctly in both customer and admin invoice screens.

## Assumptions
- Chosen defaults for this plan are: a new `umum` role, featured films selectable from any existing film record, and inline checkout biodata only for `umum` users with `/biodata` retained for later edits.
- The winner slides after the first “What Happened Last Year” slide stay dynamic from the latest closed submission period unless you later want those made manual too.
- RajaOngkir integration is explicitly out of scope until an API key is available.
- The future multi-kurator / multi-juri scoring model is out of scope for this batch; no review-schema changes are included here.
