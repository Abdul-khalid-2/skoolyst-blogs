# Skoolyst Blog — `blog.skoolyst.com`

Public blog + author/admin dashboard for the Skoolyst family of apps.
Backend: **plain PHP, API-first** (no framework). Frontend: static HTML/CSS/JS calling a JSON API.

This README is the **development task tracker**. Update it as work happens — check items off (`[x]`) only after they're implemented *and* tested. Don't mark a parent item done while its sub-tasks are pending.

---

## Change Log — What / Where / Why

Every completed task gets a short entry here: **what** changed, **where**, and **why** it was done that way. If a future change looks like it conflicts with something (a naming choice, a missing feature, a design decision), check here first before assuming it's a mistake. **Add a new entry every time a task from this checklist is completed** — newest entry on top.


### 2026-08-31 — Database migration system (Section 6)
- **What:** Added a migration runner + 10 SQL migration files creating all 11 `blog_*` tables (`blog_migrations`, `blog_users`, `blog_posts`, `blog_categories`, `blog_tags`, `blog_post_tags`, `blog_comments`, `blog_media`, `blog_post_views_daily`, `blog_audit_log`, `blog_api_keys`).
- **Where:** `database/migrations/0001–0010*.sql`, `core/Migrator.php`, `bin/migrate.php`. Also fixed `core/Database.php`'s connection-failure handling.
- **Why:** README's own architecture rules require every table to live under `blog_` prefix with no cross-app foreign keys — schema was written and FK-checked against that constraint directly (verified via `information_schema`, every FK points only to another `blog_*` table). Migrations aren't wrapped in an explicit PDO transaction because `CREATE TABLE` causes an implicit commit in MySQL/MariaDB anyway — wrapping it would be a no-op transaction, so the code doesn't pretend otherwise. `Database.php` was changed to `throw` on connection failure instead of calling `Response::json()`, because `Response` is an HTTP-only class that isn't loaded when `bin/migrate.php` runs from the CLI — the original code would fatal-error with "Class Response not found" outside a web request.

### 2026-08-31 — Backend foundation (Sections 5 & 9)
- **What:** Added the plain-PHP core (`Env`, `Config`, `Database`, `Request`, `Response`, `Validator`, `Router`), a front controller (`index.php`), `.htaccess`, and the first working route (`GET /api/v1/health`).
- **Where:** `core/*.php`, `config/app.php`, `config/database.php`, `.env.example`, `index.php`, `.htaccess`, `routes/api.php`.
- **Why:** README's rules require plain PHP (no framework) and everything under `/api/v1/...`. `.htaccess` only rewrites requests starting with `/api/v1/` to `index.php` — real files/directories (the static frontend) are matched and served first, so the existing HTML pages are untouched and don't go through the router at all. `Request`/`Router` properties aren't `readonly` even though that's the more modern style, because `readonly`-in-`clone` needs PHP 8.3, and this needs to run on shared hosting that may only offer 8.1/8.2.

### 2026-08-31 — Missing dashboard pages (`categories.html`, `media.html`)
- **What:** Added `dashboard/categories.html` and `dashboard/media.html`.
- **Where:** `dashboard/` folder.
- **Why:** Every dashboard page's sidebar already linked to both files, and `assets/js/dashboard.js` / `assets/css/dashboard.css` already had full working logic and styling for both (`initCategories()`, `initMedia()`, `.cat-row`, `.media-grid`, etc.) — the HTML pages themselves were just never created, so those two sidebar links 404'd on every page. No new JS or CSS was written; the pages only had to attach to what already existed, so behavior matches the rest of the dashboard exactly.

### 2026-08-31 — README rewritten as an actual checklist
- **What:** Replaced `README.md`.
- **Where:** `README.md`.
- **Why:** The file on GitHub wasn't a real README — it was a meta-instruction document (someone had prompted an AI to rewrite the README into a checklist, but that rewritten output was never committed; only the instructions were). This rewrite is that instruction actually carried out, with real status per section instead of a template.

---

## 1. Architecture

```text
blog.skoolyst.com
│
├── Static Frontend (HTML/CSS/JS, Bootstrap-based)
│   ├── Public Blog
│   └── Author/Admin Dashboard
│
└── PHP API — /api/v1/...
        │
        ▼
    MySQL — blog_* tables only
```

- Own PHP app, own `blog_*` tables, own session — **no relationship** (no joins/FKs) with `ads.skoolyst.com` or `teachers.skoolyst.com` tables. All three are independent applications sharing a design language, not a database.

---

## 2. Current Status (as of this pull)

The repo currently contains **only the static frontend prototype** — it's UI/mock-data only, there is no PHP backend, database, or API yet.

- [x] Frontend HTML/CSS scaffold complete
- [x] Mock data layer (`assets/js/mock-data.js`) driving all pages
- [x] Dashboard CRUD interactions work against mock data + `localStorage`
- [x] PHP backend core + config + routing skeleton (Sections 5 & 9)
- [x] Database schema — all 11 `blog_*` tables live-tested on local MySQL (Section 6)
- [ ] `/api/v1/...` — health check only so far; real endpoints not started
- [ ] Real authentication — not started (dashboard is unprotected, hardcoded "Sarah Chen" user)

## 3. File Structure — Fixes Applied This Session

The dashboard sidebar linked to `categories.html` and `media.html` on every page, but neither file existed (404 on click) — the JS (`initCategories`, `initMedia`) and CSS for both were already fully built in `dashboard.js` / `dashboard.css`, just never given a page to attach to.

- [x] Added `dashboard/categories.html` — wired to existing `initCategories()`
- [x] Added `dashboard/media.html` — wired to existing `initMedia()`

Leftover scaffold from the original Bolt.new export (not needed for a plain-PHP static-frontend project):

- [ ] Decide whether to remove `.bolt/config.json` (Vite template marker — unused, this isn't a Vite project)
- [ ] Decide whether to trim `.gitignore` (currently Node/Vite-oriented — `node_modules`, `dist`, etc. — irrelevant until an actual build step or PHP backend is added; PHP-relevant entries like `.env`, `vendor/`, `uploads/` will need adding once the backend exists)

## 4. Existing Frontend Pages

```text
index.html
blog.html
post.html
category.html
about.html
contact.html

dashboard/
├── index.html
├── posts.html
├── post-editor.html
├── categories.html   (added)
└── media.html        (added)

assets/
├── css/ (style.css, dashboard.css)
└── js/  (app.js, dashboard.js, mock-data.js)

core/    (Env, Config, Database, Request, Response, Validator, Router, Migrator)
config/  (app.php, database.php)
routes/  (api.php)
database/migrations/  (0001–0010, run via bin/migrate.php)
bin/migrate.php   (CLI migration runner)
public/uploads/media/   (empty, gitignored except .gitkeep)
index.php   (API front controller)
.htaccess   (routes /api/v1/* to index.php)
.env.example
```

- [ ] Verify public homepage, blog archive, single post, category, about, contact pages
- [ ] Verify dashboard: overview, posts, post editor, categories, media
- [ ] Verify responsive layout + existing design tokens
- [ ] Keep frontend visual design unchanged unless required for API integration

---

## 5. Backend Foundation

### Core
- [x] `core/Env.php` — dependency-free `.env` loader
- [x] `core/Config.php` — dot-notation config reader over `config/*.php`
- [x] `core/Database.php` — PDO singleton + `select`/`selectOne`/`execute` helpers (never touches non-`blog_*` tables)
- [x] `core/Request.php` — method, path, query, JSON/form body, headers, bearer token
- [x] `core/Response.php` — uniform JSON success/error responses
- [x] `core/Validator.php` — `required|email|max|min|in|numeric` rule engine
- [x] `core/Router.php` — path-param routing (`{id}`), 404/405 handling
- [x] `index.php` — front controller, wires everything above, dispatches `/api/v1/*`
- [ ] Auth middleware, authorization middleware (needs `blog_users`/sessions — Section 6/12)
- [ ] CSRF protection, rate limiter, upload handler, audit log helper
- [x] Centralized error handling → JSON error responses (`set_exception_handler` in `index.php`)

### Configuration
- [x] `.env.example` committed (`.env` itself gitignored); `config/app.php`, `config/database.php`
- [x] App URL, session name, upload path/size/mime settings, DB connection settings
- [x] `.htaccess` — routes `/api/v1/*` to `index.php`, leaves static frontend untouched, blocks direct `.env` access

**Verified:** `php -l` clean on all new files; smoke-tested with PHP's built-in server — `GET /api/v1/health` → 200 with app name + timestamp, unknown `/api/v1/*` route → 404 JSON, non-API path → 404 JSON (never reaches the router). No live MySQL yet, so DB-touching endpoints are still untested — that starts with Section 6.

## 6. Database

All tables prefixed `blog_`, isolated from `ads.skoolyst.com` / `teachers.skoolyst.com` — verified via `information_schema` that every foreign key in this schema points only to another `blog_*` table.

```text
blog_migrations, blog_users, blog_posts, blog_categories, blog_tags,
blog_post_tags, blog_comments, blog_media, blog_post_views_daily,
blog_audit_log, blog_api_keys
```

- [x] Migration runner (`core/Migrator.php`) + `blog_migrations` tracking table
- [x] `blog_users` (author/admin fields, password hash, status)
- [x] `blog_posts` (title, slug, excerpt, body, cover, status, author_id, category_id, published_date, views, SEO fields, soft-delete via `deleted_at`)
- [x] `blog_categories` (name, slug, description, color)
- [x] `blog_tags` + `blog_post_tags` (composite PK pivot)
- [x] `blog_comments` (post_id, author name/email, body, moderation status)
- [x] `blog_media` (filename, path, alt text, uploaded_by)
- [x] `blog_post_views_daily` (unique per post/day, for aggregation)
- [x] `blog_audit_log`
- [x] `blog_api_keys`

**Verified against a live local MySQL/MariaDB instance:**
- `php bin/migrate.php` → all 10 migration files run cleanly, create 11 tables
- Re-running `php bin/migrate.php` → correctly reports "Nothing to migrate" (idempotent)
- `information_schema.KEY_COLUMN_USAGE` check → every FK references a `blog_*` table only
- `Database::execute` / `selectOne` round-tripped an insert → read → delete successfully

Migration files live in `database/migrations/*.sql`, numbered and run in order. Add new ones with the next number — never edit an already-applied migration file.

## 7. API (`/api/v1/...`, not started)

**Public:** `GET /posts` (pagination, category/search filter, published-only), `GET /posts/{id}`, `GET /categories`, `POST /posts/{id}/comments` (validated, saved pending, rate-limited), `POST /posts/{id}/view`

**Auth:** `POST /auth/login`, `POST /auth/logout` (isolated blog session)

**Author:** `GET/POST /author/posts`, `PATCH/DELETE /author/posts/{id}`, `POST /author/posts/{id}/image`

**Admin:** full CRUD on `/admin/posts`, `/admin/comments`, `/admin/media`, `/admin/categories`

- [ ] Public API implemented
- [ ] Auth API implemented
- [ ] Author API implemented
- [ ] Admin API implemented

## 8. Application Modules (not started)

```text
app/{Posts,Categories,Comments,Media,Auth}/
```
Each module: Controller, Repository, Model, Validator (where needed), Routes, auth/authorization, error handling, tests. Don't create classes a module doesn't need.

- [ ] Posts module
- [ ] Categories module
- [ ] Comments module
- [ ] Media module
- [ ] Auth module

## 9. Routing
- [x] `routes/api.php` created with `/api/v1` prefix stripped in `index.php`; a `/health` route proves the pipeline end-to-end
- [x] Method validation (405) + JSON 404 for unmatched routes
- [ ] Module route files (`routes/api/posts.php` etc.) — added as each module in Section 8 is built
- [ ] Auth + admin middleware applied to routes (depends on Section 12)

## 10. Frontend → API Integration (not started)

Keep existing HTML/CSS; replace mock-data calls with real fetches.

**Public:** update `assets/js/app.js` — API helper, fetch posts/categories/single post/category posts, submit comments, record views, loading/error/empty states.

**Dashboard:** update `assets/js/dashboard.js` — real login, load/create/edit/delete posts via API, image upload via API, comment moderation, media + category management via API.

- [ ] Public frontend wired to API
- [ ] Dashboard wired to API

## 11. Mock Data Migration (not started)
- [ ] Review + seed: authors, categories, posts, comments, media, stats
- [ ] Switch frontend off `mock-data.js`
- [ ] Remove `mock-data.js` only after API migration is verified

## 12. Auth & Security (not started)
- [ ] Password hashing, login/logout, session regeneration
- [ ] Unique blog session name (no conflicts with other Skoolyst apps)
- [ ] Author ownership checks, admin authorization
- [ ] Input validation, prepared statements, upload MIME/size limits
- [ ] API rate limiting, comment spam protection, admin action audit log

## 13. Media Uploads (not started)
- [ ] `public/uploads/media/` with writable perms
- [ ] MIME/size validation, safe filenames, block executables
- [ ] Cover image upload, media library upload/delete

## 14. Testing (not started)
- [ ] Backend: DB, migrations, seeders, auth, all API groups, validation, errors, rate limiting
- [ ] Frontend: every public page + every dashboard flow
- [ ] Cross-app isolation: no shared tables/sessions/FKs with `ads`/`teachers`, no data leakage across APIs

## 15. Production Deployment (not started)
- [ ] Production `.env` + DB, migrations/seeders, Apache + `.htaccess`, `/api/v1` routing, upload dir, error logging off in prod, HTTPS
- [ ] Smoke test: API, frontend, dashboard login, media uploads, DB perms

## 16. Final Cleanup (not started)
- [ ] Remove unused mock/debug code, test credentials
- [ ] Confirm `.env` not committed, verify security settings & indexes
- [ ] Verify mobile responsiveness
- [ ] Keep this README's checkboxes in sync with real status

---

## Development Order

```text
1. Foundation → 2. Database → 3. Core modules → 4. API → 5. Auth
→ 6. Frontend/API integration → 7. Mock-data migration → 8. Security
→ 9. Testing → 10. Deployment → 11. Cleanup
```

Work one small task at a time: implement → test → check it off here → move on. Never check off something untested.
