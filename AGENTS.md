# Repository Guidelines

## Project Structure & Module Organization
This is a Laravel 8 application. Core PHP code lives in `app/`, with controllers in `app/Http/Controllers`, Eloquent models in `app/Models`, and middleware/providers handling request flow and bootstrapping. Web and API routes belong in `routes/web.php` and `routes/api.php`. Blade templates live in `resources/views` and are grouped by feature, including `landing/`, `film/`, `category/`, `user/`, and `user-detail/`. Frontend source files are in `resources/js` and `resources/css`; Laravel Mix compiles them to local build artifacts in `public/js` and `public/css`. Database work belongs in `database/migrations`, `database/factories`, and `database/seeders`. Tests live in `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
- `composer install` installs PHP dependencies.
- `npm install` installs the Mix build toolchain.
- `php artisan serve` runs the app locally.
- `php artisan migrate` applies schema changes.
- `npm run dev` builds CSS and JS once.
- `npm run watch` rebuilds assets during frontend work.
- `npm run prod` creates production-ready assets.
- `php artisan test` or `./vendor/bin/phpunit` runs the PHPUnit suites.

## Coding Style & Naming Conventions
Follow `.editorconfig`: UTF-8, LF line endings, 4-space indentation, and 2 spaces in YAML. Match the Laravel StyleCI preset from `.styleci.yml`. Use PSR-4 class names in `StudlyCase` such as `FilmController` or `DownloadLog`, and keep methods, properties, and variables in `camelCase`. Name Blade views by feature and purpose, for example `resources/views/film/index.blade.php`. Keep controllers focused on request handling; move reusable query or domain logic into models or dedicated classes when a controller grows.

## Testing Guidelines
Put HTTP, route, and integration coverage in `tests/Feature`; keep isolated logic in `tests/Unit`. Name files with the `*Test.php` suffix. There is no enforced coverage threshold, so new work should include regression tests for changed behavior, especially authentication, validation, downloads, and schema-backed model updates.

## Commit & Pull Request Guidelines
The current history uses short conventional subjects like `refactor: slicing landing page`; follow that style with prefixes such as `feat:`, `fix:`, `refactor:`, or `test:` plus a concise imperative summary. PRs should describe the user-facing change, note any migration or `.env` updates, link related issues, and include screenshots for Blade or landing-page changes. Call out asset-build or database-impacting changes clearly.

## Security & Configuration Tips
Never commit `.env`, local database credentials, or generated build output. `vendor/`, `node_modules/`, and compiled `public/` assets are local artifacts. Treat `ffh.sql` as a local snapshot for reference or restore work, not a replacement for migrations.
