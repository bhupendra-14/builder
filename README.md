# CMS / Page Builder

A complete admin backend, visual page builder, and asset manager for managing a single-page dynamic website. Built to satisfy the spec in `WebsiteBackend.docx`.

## What it does

- **Visual page builder** with 14 section types (hero, CTA, video, carousel, feature grid, testimonials, accordion/FAQ, stats counter, promo banner, and more)
- **Inline editing** — click any text or image on the rendered page to edit it
- **Drag-and-drop section ordering** with duplicate, enable/disable, and soft-delete
- **Three-stage publishing pipeline** — `draft → dark preview → live`
- **Asset manager** with auto webp compression, folders, tags, and metadata editing
- **Role-based access control** (admin, editor) via spatie/laravel-permission
- **Audit log** of every content/user/asset/setting change
- **Scheduled publishing** via Laravel's scheduler
- **Section version history** with rollback (20 versions per section)
- **Dynamic top navigation** built from sections you flag as "show in nav"
- **Two-column dynamic footer** powered by global settings
- **Per-section content validation** (server + client side)

## Tech stack

| Layer | Tech |
|---|---|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Vue 3, Vite, Pinia, Vue Router, Tailwind CSS v4 |
| Inline editor | TipTap |
| Auth | Laravel Sanctum |
| RBAC | spatie/laravel-permission |
| Image processing | Intervention Image v4 |
| HTML sanitization | mews/purifier (PHP) + dompurify (JS) |
| Tests | PHPUnit 10 |
| Database | MySQL 8 |

---

## Prerequisites

Before you start, you'll need:

| Tool | Min version | Notes |
|---|---|---|
| **PHP** | 8.2+ | with extensions: `pdo_mysql`, `gd`, `intl`, `bcmath`, `zip`, `mbstring`, `xml`, `curl`, `openssl`, `fileinfo` |
| **Composer** | 2.x | https://getcomposer.org |
| **Node.js** | 20+ | https://nodejs.org |
| **MySQL** | 8.0+ | (or MariaDB 10.6+) |
| **Git** | any | for cloning |

If you're on Windows, **WAMP / Laragon / XAMPP** ships PHP and MySQL together — that's fine. The project was developed on WAMP with PHP 8.3 and MySQL 8.

---

## Installation (clone → running in 10 minutes)

### 1. Clone the repo

```bash
git clone <your-repo-url> builder-claude
cd builder-claude
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JS dependencies

```bash
npm install
```

This will pull in Vue, Vite, TipTap, Tailwind, and all the other frontend packages (~150MB).

### 4. Create your environment file

```bash
# Linux / Mac / Git Bash
cp .env.example .env

# Windows CMD
copy .env.example .env
```

### 5. Generate the application key

```bash
php artisan key:generate
```

This writes a fresh `APP_KEY` into your `.env` file.

### 6. Configure the database

Open `.env` and update the database section to match your local MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=page_builder
DB_USERNAME=root
DB_PASSWORD=
```

Then create the database in MySQL:

```sql
CREATE DATABASE page_builder
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;
```

You can do this from the MySQL CLI, phpMyAdmin, MySQL Workbench, or whatever you prefer.

### 7. Run migrations + seed the demo content

```bash
php artisan migrate:fresh --seed
```

This:
- Creates all tables (users, sections, assets, settings, audit_logs, publish_histories, etc.)
- Seeds permissions and roles (admin / editor)
- Creates two demo users
- Populates 8 site settings (title, brand color, footer fields)
- Publishes 9 demo sections to **Live** so the public homepage is immediately populated
- **Generates a `PREVIEW_TOKEN` and writes it to your `.env`** automatically (via `EnsurePreviewTokenSeeder`). If you already have one set, it's left alone.

### 8. Create the storage symlink

```bash
php artisan storage:link
```

This makes uploaded assets in `storage/app/public` reachable via `public/storage`.

> **Windows note:** if `storage:link` fails because `public/storage` already exists as a regular folder, delete it first and re-run:
> ```bash
> rm -rf public/storage
> php artisan storage:link
> ```

### 9. (Optional) Create the test database

If you plan to run the test suite, create a separate test DB so it can refresh schema without touching your dev data:

```sql
CREATE DATABASE page_builder_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;
```

The test DB credentials are configured in `phpunit.xml` under `<env>`. If your MySQL setup uses non-default credentials, update them there.

---

## Running the app

You need **two terminal windows** running side-by-side:

### Terminal 1 — Laravel backend

```bash
php artisan serve
```

This starts the API server on `http://127.0.0.1:8000`.

### Terminal 2 — Vite frontend dev server

```bash
npm run dev
```

This starts Vite on a separate port and Laravel auto-injects the asset URLs. Vite watches for file changes and hot-reloads the browser.

### Open it in your browser

| URL | What it is |
|---|---|
| `http://127.0.0.1:8000/` | Public website (the demo homepage you just seeded) |
| `http://127.0.0.1:8000/login` | Admin login |
| `http://127.0.0.1:8000/admin` | Admin dashboard (after login) |
| `http://127.0.0.1:8000/preview?token=YOUR_PREVIEW_TOKEN` | Dark preview (with the token from step 7) |

### Default login credentials

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@example.com` | `password123` |
| **Editor** | `editor@example.com` | `password123` |

> ⚠️ **Change these immediately if you deploy to a real server.** They're for local development only.

---

## Common commands

| Command | What it does |
|---|---|
| `php artisan serve` | Start the Laravel dev server on `:8000` |
| `npm run dev` | Start Vite (must be running for the admin to load) |
| `npm run build` | Build production frontend assets |
| `npm test` | Run frontend (Vitest) tests once |
| `npm run test:watch` | Run frontend tests in watch mode |
| `php artisan test` | Run backend (PHPUnit) tests via Laravel's wrapper |
| `composer test` | Run backend tests with pretty test names |
| `composer test:fast` | Backend tests with compact dot output |
| `composer test:publish` | Only publish pipeline tests (13) |
| `composer test:validator` | Only section validator tests (29) |
| `composer lint` | Auto-format PHP files with Laravel Pint |
| `composer lint:check` | Check formatting without changing files |
| `php artisan migrate:fresh --seed` | Wipe DB and re-seed (loses all your edits!) |
| `php artisan db:seed --class=AdminUserSeeder` | Re-create just the admin users |
| `php artisan publish:run-scheduled` | Manually trigger any due scheduled publishes |

---

## 🧪 Automated Testing

The project ships with both backend and frontend test suites covering the highest-value, hardest-to-debug logic: the publish pipeline and the per-section content validators.

### Backend (PHPUnit)

PHPUnit 10 + a real MySQL test database (configured in `phpunit.xml`).

```bash
# Run the full backend suite (13 feature + 30 unit + 1 example = 44 tests)
php artisan test

# or, equivalent
composer test                   # pretty test names
composer test:fast              # compact dot output
./vendor/bin/phpunit            # raw phpunit

# Targeted runs
composer test:publish           # only the publish pipeline regression suite
composer test:validator         # only the section content validator suite
```

**One-time setup:** create the test database before the first run.

```sql
CREATE DATABASE page_builder_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;
```

The test DB connection string lives in `phpunit.xml` under `<env>` and **only** applies when running tests. Your dev database is never touched.

**What's covered:**
- `tests/Feature/PublishPipelineTest.php` — 13 tests covering `publishToDark`, `publishToLive`, the "stale dark preview" regression, soft-delete cleanup, scheduled publishes, audit log entries
- `tests/Unit/SectionContentValidatorTest.php` — 30 tests covering every section type's validation rules

### Frontend (Vitest)

Vitest with Node environment for the pure JS utility modules.

```bash
# Run frontend tests once
npm test

# Watch mode (re-runs on file change)
npm run test:watch
```

**What's covered:**
- `resources/js/utils/__tests__/sectionValidation.spec.js` — 31 tests mirroring the PHPUnit `SectionContentValidatorTest`

### Why both backend and frontend tests for validation?

Section validation runs in **two places**:
- **Server side** (`app/Services/SectionContentValidator.php`) — the source of truth, can never be bypassed
- **Client side** (`resources/js/utils/sectionValidation.js`) — gives instant feedback in the Builder Workspace before the request hits the server

Both implementations enforce the same rules. The mirrored test suites (PHPUnit + Vitest) catch any drift if a rule is added to one but forgotten in the other.

### Running everything before committing

```bash
composer test && npm test
```

Both suites together run in under 5 seconds. Set this as a git pre-commit hook if you want airtight regression coverage.

---

## How to use it (5-minute tour)

1. **Open `http://127.0.0.1:8000/`** — you'll see the seeded demo homepage with hero, features, testimonials, FAQ, and footer.
2. **Click the navigation links** — they smooth-scroll to anchored sections.
3. **Log into the admin** at `/login` with `admin@example.com` / `password123`.
4. **Page Builder** (sidebar) — see the 9 demo sections, drag to reorder, toggle "Show in navigation" per section.
5. **Click "Edit Content"** on the Hero section — the Builder Workspace opens with **Inline mode** by default. Click the headline and type to edit it.
6. **Save Draft** → a confirm dialog asks if you want to go to Publish.
7. **Publish → Publish to Live** — content is live on `/` instantly.
8. **Settings** (sidebar) — change the site title, brand color, footer contact info.
9. **Media** (sidebar) — upload an image, edit its title/alt/tags, organize by folders.
10. **Audit** (sidebar) — see every action you just took, with diffs.

For a deeper walkthrough, see [`docs/ADMIN_GUIDE.md`](docs/ADMIN_GUIDE.md).

---

## Project structure

```
.
├── app/
│   ├── Console/Commands/        # Artisan commands (e.g. publish:run-scheduled)
│   ├── Http/Controllers/Api/    # All API endpoints — extend BaseController
│   ├── Models/                  # Eloquent models
│   ├── Observers/               # Audit log observers (Section, Asset, User, Setting)
│   ├── Repositories/            # Repository pattern (Section + Asset interfaces)
│   ├── Services/                # Business logic (PublishService, ContentManagementService, Auditor, etc.)
│   └── Traits/                  # ApiResponseTrait
├── database/
│   ├── migrations/              # Schema migrations
│   └── seeders/                 # Demo content + permissions + admin user
├── docs/
│   ├── ADMIN_GUIDE.md           # End-user docs
│   └── DEPLOYMENT.md            # Production deployment guide (nginx + php-fpm)
├── resources/
│   └── js/
│       ├── components/
│       │   ├── sections/        # 14 block components (HeroBlock, CtaBlock, etc.)
│       │   ├── inline/          # Inline editing primitives (InlineText, InlineImage, etc.)
│       │   └── common/          # ToastContainer, ConfirmDialog, AppDataTable
│       ├── stores/              # Pinia stores (auth, settings, toast, confirm)
│       ├── views/               # Page-level Vue components
│       ├── layouts/             # AdminLayout
│       ├── router/              # Vue Router config
│       └── utils/               # sectionValidation.js
├── routes/
│   ├── api.php                  # All real backend routes
│   ├── web.php                  # SPA catch-all
│   └── console.php              # Scheduled tasks
├── tests/
│   ├── Feature/PublishPipelineTest.php
│   └── Unit/SectionContentValidatorTest.php
├── composer.json
├── package.json
├── vitest.config.js             # Frontend test config
└── phpunit.xml                  # Backend test config (uses page_builder_test DB)
```

---

## Architecture highlights

### Three-stage publishing pipeline

Each `Section` row has three JSON content columns:

```
┌──────────────┐    Publish to Dark    ┌─────────────────────┐    Publish to Live    ┌────────────────────────┐
│ draft_content│ ────────────────────▶ │dark_preview_content │ ────────────────────▶ │live_published_content  │
│              │                       │                     │                       │                        │
│ Save Draft   │                       │ /preview shows this │                       │  /  shows THIS         │
│ writes here  │                       │                     │                       │                        │
└──────────────┘                       └─────────────────────┘                       └────────────────────────┘
```

- `Save Draft` only writes `draft_content`
- `Publish to Dark` copies draft → dark preview (private)
- `Publish to Live` copies draft → both dark and live (public)

The public site at `/` only reads `live_published_content`. The preview site at `/preview?token=...` reads `dark_preview_content`. So you can review changes safely before they go live.

### Section blocks

Each of the 14 section types lives in `resources/js/components/sections/<type>Block.vue`. They all:
- Accept a `content` prop (JSON object)
- Accept an `editable` prop (toggles inline editing)
- Emit `update:content` for inline edits
- Emit `pick-asset` for image/video field replacements

`FrontendRenderer.vue` is the dispatcher — it looks up the right component for each `section.type` from a registry.

To add a 15th section type:
1. Create the block component in `resources/js/components/sections/`
2. Register it in `FrontendRenderer.vue`'s `componentRegistry`
3. Add a default content shape in `BuilderWorkspace.vue::getDefaultContent()`
4. Add an `<option>` to the dropdown in `PageBuilderView.vue`
5. Add validation rules in `app/Services/SectionContentValidator.php` and `resources/js/utils/sectionValidation.js`

### API response shape

Every API endpoint extends `App\Http\Controllers\Api\BaseController` and returns the same envelope:

```json
{
  "success": true,
  "message": "Operation completed",
  "data": { ... },
  "pagination": { ... }
}
```

Use `successResponse()`, `errorResponse()`, `paginatedResponse()`, or `validationErrorResponse()` from `ApiResponseTrait` — the Vue stores depend on this shape.

---

## Troubleshooting

### "vite manifest not found"
You forgot to run `npm run dev` or `npm run build`. The admin needs Vite assets to load.

### "could not find driver"
PHP doesn't have the `pdo_mysql` extension enabled. On WAMP, click the WAMP tray icon → PHP → PHP extensions → enable `pdo_mysql` and restart.

### Public site at `/` is empty
Either you didn't run `php artisan db:seed`, or you accidentally overwrote the seeded data. Run:
```bash
php artisan migrate:fresh --seed
```
(This wipes everything and reseeds — only do it on a dev install.)

### Uploaded images don't appear
The `public/storage` symlink is missing. Run:
```bash
rm -rf public/storage
php artisan storage:link
```

### "Missing preview token" on /preview
You need to set `PREVIEW_TOKEN` in `.env`, then either pass it as a query string (`/preview?token=...`) or paste it into the **Admin → Publish** screen which stores it in your browser localStorage.

### Tests fail with "database not found"
Create the test database:
```sql
CREATE DATABASE page_builder_test CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
```
The test DB name comes from `phpunit.xml` — match whatever's there.

### "Class 'Imagick' not found" on image upload
We use GD, not Imagick. If you see this, your PHP doesn't have `gd` enabled. Enable it the same way as `pdo_mysql`.

---

## Documentation

| Doc | For who |
|---|---|
| `README.md` (this file) | Developers setting up locally |
| [`docs/ADMIN_GUIDE.md`](docs/ADMIN_GUIDE.md) | End users (admin / editor) |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | Operators deploying to production |

---

## Production deployment

This README covers local development setup. For deploying to a production server (nginx + php-fpm + MySQL + cron for scheduled publishes), see [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — it walks through the manual install, env var reference, day-2 operations (backups, logs, updates), and a hardening checklist.

---

## License

Built on top of [Laravel](https://laravel.com), which is open-source under the MIT license.
