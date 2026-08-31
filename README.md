# Skoolyst Blog — `blog.skoolyst.com`

Public blog + author/admin dashboard for the Skoolyst family of apps.
Backend: **plain PHP, API-first** (no framework). Frontend: static HTML/CSS/JS calling a JSON API.

This README is the **development task tracker**. Update it as work happens — check items off (`[x]`) only after they're implemented *and* tested. Don't mark a parent item done while its sub-tasks are pending.

---

## Change Log — What / Where / Why

Every completed task gets a short entry here: **what** changed, **where**, and **why** it was done that way. If a future change looks like it conflicts with something (a naming choice, a missing feature, a design decision), check here first before assuming it's a mistake. **Add a new entry every time a task from this checklist is completed** — newest entry on top.

### 2026-08-31 — Frontend components: Button, Table, InputField/FormGroup, Modal (Section 5)
- **What:** Added `Button.action()`, `Table.actions()`, `InputField.*`/`FormGroup.*`, and `Modal.wrapper()`/`Modal.confirm()` to `assets/js/components.js`. Refactored `dashboard.js`'s posts-table and categories-list row renderers to use `Button`/`Table`; refactored `initCategories()`'s add/edit modal fields to render via `FormGroup`/`InputField` instead of static HTML; replaced all three `window.confirm()` calls (delete post/category/media) with `Modal.confirm()`.
- **Where:** `assets/js/components.js` (new functions); `assets/js/dashboard.js` (`initPosts`, `initCategories`, `initMedia`); `dashboard/categories.html` (modal body is now an empty `#cat-modal-body` container populated by JS instead of hand-written fields).
- **Why:** Same "real duplication first" rule as `Badge`/`Card` — the `action-btn`/`table-actions` shape was hand-copied in both the posts and categories row builders, so `Button`/`Table` extract that. `FormGroup`/`InputField` convert the category modal's 3 fields to component functions (verified byte-for-byte equivalent markup — the `.form-group` CSS rule only ever applied inside `.editor-form`, which this modal isn't part of, so there's no visual change). `Modal.confirm()` replaces `window.confirm()` in all three delete flows: this both removes 3 duplicated one-line `confirm(...)` calls and — more importantly — makes those flows testable at all, since jsdom can't drive the browser's native `confirm()` dialog. Full jsdom-over-`php -S` test suite (all pages + all new interactions, including a full add/delete round-trip through the new modal) is green with zero console errors.

### 2026-08-31 — Frontend components: Badge + Card extracted (Section 5)
- **What:** Created `assets/js/components.js` with `Badge.status()` and `Card.stat()`. Refactored `dashboard.js` to call them instead of building that markup inline in two places (badge) and one place (card).
- **Where:** New file `assets/js/components.js`; edited `assets/js/dashboard.js`; added a `<script>` tag for the new file to all 11 HTML pages (right after `mock-data.js`, before `app.js`/`dashboard.js`).
- **Why:** `Badge` was picked first because it was genuinely duplicated byte-for-byte in two places (overview's recent-posts table, posts table) — the strongest possible case. `Card.stat` was already a pure function (`statCard()`) just living in the wrong file. Verified there's no regression by loading pages in a headless browser (jsdom) over a real local HTTP server and diffing the rendered markup against what the old inline code produced — identical output, zero console errors across every page tested.

### 2026-08-31 — Frontend Component Architecture decided (Section 5)
- **What:** Decided the frontend will move to reusable JS component functions (buttons, cards, tables, badges, modals, form fields) instead of hand-copied HTML markup per page — documented as a new checklist section. No code written yet, decision + plan only.
- **Where:** `README.md` (this file) — new Section 5.
- **Why:** Compared directly against `Abdul-khalid-2/skoolyst-advertisement`, which already runs this pattern in PHP (`views/components/stat-card.php`, `status-badge.php`, etc. — that repo's own comments note markup used to be hand-copied 4x per page before being extracted). Same problem exists here: `dashboard.js` already has one component-shaped function (`statCard()`) but table rows, badges, and form groups are still duplicated inline across pages. This is a maintainability/consistency decision, not a performance one — plain string-building JS has no virtual-DOM diffing to benefit from, so duplicating vs. sharing markup costs the same at runtime either way. The win is one place to fix bugs and change design, matching the pattern already proven out in the sibling `ads` project.

### 2026-08-31 — Database migration system (Section 6)
- **What:** Added a migration runner + 10 SQL migration files creating all 11 `blog_*` tables (`blog_migrations`, `blog_users`, `blog_posts`, `blog_categories`, `blog_tags`, `blog_post_tags`, `blog_comments`, `blog_media`, `blog_post_views_daily`, `blog_audit_log`, `blog_api_keys`).
- **Where:** `database/migrations/0001–0010*.sql`, `core/Migrator.php`, `bin/migrate.php`. Also fixed `core/Database.php`'s connection-failure handling.
- **Why:** README's own architecture rules require every table to live under `blog_` prefix with no cross-app foreign keys — schema was written and FK-checked against that constraint directly (verified via `information_schema`, every FK points only to another `blog_*` table). Migrations aren't wrapped in an explicit PDO transaction because `CREATE TABLE` causes an implicit commit in MySQL/MariaDB anyway — wrapping it would be a no-op transaction, so the code doesn't pretend otherwise. `Database.php` was changed to `throw` on connection failure instead of calling `Response::json()`, because `Response` is an HTTP-only class that isn't loaded when `bin/migrate.php` runs from the CLI — the original code would fatal-error with "Class Response not found" outside a web request.

### 2026-08-31 — Backend foundation (Sections 6 & 10)
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
  - **What:** All public pages (`index`, `blog`, `post`, `category`, `about`, `contact`) and dashboard pages (`overview`, `posts`, `post-editor`, `categories`, `media`) exist with full HTML/CSS.
  - **Where:** Repo root `*.html`, `dashboard/*.html`, `assets/css/style.css`, `assets/css/dashboard.css`.
  - **Why:** This was the state of the repo before any backend work started — it's a Bolt.new-exported frontend prototype, not something built in this session (except the two missing dashboard pages, logged separately below).

- [x] Mock data layer (`assets/js/mock-data.js`) driving all pages
  - **What:** Hardcoded JS arrays/objects (`MOCK_POSTS`, `MOCK_CATEGORIES`, `MOCK_MEDIA`, etc.) that every page reads from instead of a real database.
  - **Where:** `assets/js/mock-data.js`, consumed by `assets/js/app.js` (public site) and `assets/js/dashboard.js` (dashboard).
  - **Why:** Lets the frontend be fully clickable/demoable with no backend — this is what Section 12 (Mock Data Migration) will eventually replace with real API calls, but it stays in place until the API is proven out, so the UI never breaks mid-migration.

- [x] Dashboard CRUD interactions work against mock data + `localStorage`
  - **What:** Creating/editing/deleting posts, categories, and media in the dashboard actually updates state and persists across page reloads.
  - **Where:** `assets/js/dashboard.js` (`initPosts()`, `initCategories()`, `initMedia()` etc.), backed by the browser's `localStorage`.
  - **Why:** `localStorage` was the original prototype's stand-in for a database — good enough to demo real CRUD flows before any PHP/MySQL existed. It's a stepping stone, not the final design: Section 11 replaces these calls with real `fetch()` calls to `/api/v1/...`.

- [x] PHP backend core + config + routing skeleton (Sections 6 & 10)
  - **What:** `core/` classes (`Env`, `Config`, `Database`, `Request`, `Response`, `Validator`, `Router`), `config/app.php` + `config/database.php`, `index.php` front controller, `.htaccess`, and a working `GET /api/v1/health` route.
  - **Where:** `core/*.php`, `config/*.php`, `index.php`, `.htaccess`, `routes/api.php`.
  - **Why:** README's architecture requires plain PHP (no framework) with everything under `/api/v1/...` — this is the minimum scaffolding needed before any real endpoint can exist. Built and smoke-tested with PHP's built-in server before moving to the database (see the Change Log entry for the exact test results).

- [x] Database schema — all 11 `blog_*` tables live-tested on local MySQL (Section 7)
  - **What:** A migration runner (`core/Migrator.php`) plus 10 SQL files creating every `blog_*` table the app needs.
  - **Where:** `database/migrations/0001–0010*.sql`, `bin/migrate.php`.
  - **Why:** README requires every table under this app to be `blog_`-prefixed with zero cross-app foreign keys (isolation from `ads`/`teachers`). Actually spun up a local MariaDB instance to run the migrations twice (second run correctly no-ops) and checked `information_schema` to confirm no FK ever points outside `blog_*` — not just written and assumed correct.
- [ ] Frontend component architecture — in progress (Section 5): `Badge` + `Card` extracted and live-tested, `Button`/`Table`/`InputField`/`Modal` still pending
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

## 5. Frontend Component Architecture (in progress)

Decision: move from hand-copied HTML markup per page to **reusable JS component functions**, mirroring the pattern already proven in `Abdul-khalid-2/skoolyst-advertisement` (there it's server-side PHP functions like `stat_card()`, `status_badge()` in `views/components/`; here it'll be plain JS functions since this frontend is static/mock-data driven, not server-rendered).

This is a maintainability/consistency change, **not** a performance optimization — no virtual DOM here, so duplicated vs. shared markup costs the same at runtime. The win is a single place to fix a bug or change a design, instead of N copies.

Shared component functions live in `assets/js/components.js`, loaded on every page right after `mock-data.js` and before `app.js`/`dashboard.js`.

- [x] `Badge.status(status)` — status pill (`badge-status` + `badge-dot`)
  - **What:** Extracted the exact markup that was hand-copied identically in two places.
  - **Where:** `assets/js/components.js` (new); called from `assets/js/dashboard.js` in `initOverview()`'s recent-posts table and `initPosts()`'s posts table.
  - **Why:** This was real, literal duplication (byte-for-byte identical string in two functions) — the clearest possible case for extraction, so it went first.
- [x] `Card.stat(color, icon, label, value, trend, isUp)` — dashboard overview stat card
  - **What:** Moved the standalone `statCard()` helper that already existed in `dashboard.js` into the shared components file, unchanged.
  - **Where:** `assets/js/components.js`; called from `initOverview()`.
  - **Why:** It was already component-shaped (a pure function returning markup) but lived inside `dashboard.js` instead of the shared file — public-site pages have no reason to load it, but keeping all "cards" in one file matches the plan and makes it easy to add a second card type later without hunting through `dashboard.js`.
- [x] `Button.action(icon, opts)` — icon-only row-action button (edit/toggle/delete)
  - **What:** Extracted the `class="action-btn ..." data-id="..." title="..." aria-label="..."` shape into one function, rendering `<a>` when `opts.href` is given (the Edit link) or `<button>` otherwise.
  - **Where:** `assets/js/components.js`; called from `dashboard.js`'s posts-table row renderer and the categories-list row renderer.
  - **Why:** This exact attribute shape was hand-copied for every row action in both `initPosts()` and `initCategories()` — same pattern as the earlier `Badge` extraction (real, literal duplication, not a hypothetical one).
- [x] `Table.actions(buttonsHtml)` — wraps a set of `Button.action(...)` strings in `<div class="table-actions">`
  - **What:** One-line wrapper function replacing the hand-copied `<div class="table-actions">...</div>` container.
  - **Where:** `assets/js/components.js`; used alongside `Button.action` in both row renderers above.
  - **Why:** The wrapper div was duplicated in the same two places as the buttons it contains, so it made sense to extract together.
- [x] `InputField(...)` / `FormGroup(...)` — label + input/textarea/color wrapper, matches existing `.form-group` markup
  - **What:** `InputField.text/textarea/color` build the bare input elements; `FormGroup.text/textarea/color` wrap them with the `<label>` (+ optional `.req`/`.form-hint`) using the exact classes the hand-written markup already used.
  - **Where:** `assets/js/components.js`; `dashboard.js`'s `initCategories()` now renders the add/edit-category modal's three fields into `#cat-modal-body` (was static HTML in `categories.html`, now `<div class="modal-body" id="cat-modal-body"></div>`).
  - **Why:** `post-editor.html` still hand-writes its own form fields (bigger refactor, left for a follow-up task) but the category modal's 3 fields were small and self-contained enough to convert now, matching the plan. Confirmed no visual change: the only CSS rule for `.form-group` is scoped to `.editor-form`, which the modal was never inside, so styling is identical before/after — this is a markup-source change, not a visual one. One incidental fix: the old hand-written HTML had a literal `\u2026` text sequence in the description placeholder (should have been a real ellipsis character) — `FormGroup.textarea` now emits an actual `…` via a proper JS string escape.
- [x] `Modal.wrapper(id, title, body, footer)` + `Modal.confirm(message, onConfirm)` — generic modal shell + a reusable confirm dialog
  - **What:** `Modal.wrapper` builds the `.modal-overlay > .modal-box` shell (header/body/footer) as a string. `Modal.confirm` uses it to inject a real confirm dialog into `<body>`, wired with Cancel/✕/backdrop-click/Confirm, replacing the native `window.confirm()`.
  - **Where:** `assets/js/components.js`; `Modal.confirm` now used in `dashboard.js` for all three destructive actions — delete post (`initPosts`), delete category (`initCategories`), delete media (`initMedia`) — previously three separate `confirm(...)` calls.
  - **Why:** This is the concrete "future modal" the plan mentioned — it proves `Modal.wrapper` is reusable beyond the one hand-written `cat-modal`, and gives all three delete flows one shared, styled, testable component instead of the browser's native dialog (which also isn't stylable and isn't something jsdom can drive in an automated test — `window.confirm()` had to be replaced to make delete flows testable at all).
- [ ] Finish refactoring `app.js` (public site's `renderPostCard()` is already de-duplicated internally, but not yet moved into `components.js`)
- [ ] Convert `post-editor.html`'s hand-written fields to `FormGroup`/`InputField` (left for a follow-up — bigger surface area: ~10 fields, radio group, file input, select)
- [ ] Verify no visual/behavioral regression on every page after full refactor (Section 4's verification list)

**Verified so far:** all 11 pages (`*.html` + `dashboard/*.html`) now load `assets/js/components.js`; loaded every page in a headless browser (jsdom) served over real HTTP (`php -S`) — `dash-stats`/`recent-posts` (overview) and the full posts table render **byte-identical** markup to before the refactor, zero console errors on any page. New this round, also jsdom-over-`php -S`: confirmed `Button.action`/`Table.actions` render the right tags/classes/attrs (incl. `<a href>` vs `<button>`) on `posts.html` and `categories.html`; confirmed `#cat-name`/`#cat-desc`/`#cat-color` render as the correct element types via `FormGroup`/`InputField` with the required-marker preserved; ran the add-category flow end-to-end through the `FormGroup`-built modal (new category appears in the list); confirmed `Modal.confirm` opens on delete clicks on all three pages (posts/categories/media) instead of calling `window.confirm()`, and that confirming actually removes the row and removes `#confirm-modal` from the DOM afterward.

---

## 6. Backend Foundation

### Core
- [x] `core/Env.php` — dependency-free `.env` loader
- [x] `core/Config.php` — dot-notation config reader over `config/*.php`
- [x] `core/Database.php` — PDO singleton + `select`/`selectOne`/`execute` helpers (never touches non-`blog_*` tables)
- [x] `core/Request.php` — method, path, query, JSON/form body, headers, bearer token
- [x] `core/Response.php` — uniform JSON success/error responses
- [x] `core/Validator.php` — `required|email|max|min|in|numeric` rule engine
- [x] `core/Router.php` — path-param routing (`{id}`), 404/405 handling
- [x] `index.php` — front controller, wires everything above, dispatches `/api/v1/*`
- [ ] Auth middleware, authorization middleware (needs `blog_users`/sessions — Section 7/13)
- [ ] CSRF protection, rate limiter, upload handler, audit log helper
- [x] Centralized error handling → JSON error responses (`set_exception_handler` in `index.php`)

### Configuration
- [x] `.env.example` committed (`.env` itself gitignored); `config/app.php`, `config/database.php`
- [x] App URL, session name, upload path/size/mime settings, DB connection settings
- [x] `.htaccess` — routes `/api/v1/*` to `index.php`, leaves static frontend untouched, blocks direct `.env` access

**Verified:** `php -l` clean on all new files; smoke-tested with PHP's built-in server — `GET /api/v1/health` → 200 with app name + timestamp, unknown `/api/v1/*` route → 404 JSON, non-API path → 404 JSON (never reaches the router).

## 7. Database

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

## 8. API (`/api/v1/...`, not started)

**Public:** `GET /posts` (pagination, category/search filter, published-only), `GET /posts/{id}`, `GET /categories`, `POST /posts/{id}/comments` (validated, saved pending, rate-limited), `POST /posts/{id}/view`

**Auth:** `POST /auth/login`, `POST /auth/logout` (isolated blog session)

**Author:** `GET/POST /author/posts`, `PATCH/DELETE /author/posts/{id}`, `POST /author/posts/{id}/image`

**Admin:** full CRUD on `/admin/posts`, `/admin/comments`, `/admin/media`, `/admin/categories`

- [ ] Public API implemented
- [ ] Auth API implemented
- [ ] Author API implemented
- [ ] Admin API implemented

## 9. Application Modules (not started)

```text
app/{Posts,Categories,Comments,Media,Auth}/
```
Each module: Controller, Repository, Model, Validator (where needed), Routes, auth/authorization, error handling, tests. Don't create classes a module doesn't need.

- [ ] Posts module
- [ ] Categories module
- [ ] Comments module
- [ ] Media module
- [ ] Auth module

## 10. Routing
- [x] `routes/api.php` created with `/api/v1` prefix stripped in `index.php`; a `/health` route proves the pipeline end-to-end
- [x] Method validation (405) + JSON 404 for unmatched routes
- [ ] Module route files (`routes/api/posts.php` etc.) — added as each module in Section 9 is built
- [ ] Auth + admin middleware applied to routes (depends on Section 13)

## 11. Frontend → API Integration (not started)

Keep existing HTML/CSS; replace mock-data calls with real fetches.

**Public:** update `assets/js/app.js` — API helper, fetch posts/categories/single post/category posts, submit comments, record views, loading/error/empty states.

**Dashboard:** update `assets/js/dashboard.js` — real login, load/create/edit/delete posts via API, image upload via API, comment moderation, media + category management via API.

- [ ] Public frontend wired to API
- [ ] Dashboard wired to API

## 12. Mock Data Migration (not started)
- [ ] Review + seed: authors, categories, posts, comments, media, stats
- [ ] Switch frontend off `mock-data.js`
- [ ] Remove `mock-data.js` only after API migration is verified

## 13. Auth & Security (not started)
- [ ] Password hashing, login/logout, session regeneration
- [ ] Unique blog session name (no conflicts with other Skoolyst apps)
- [ ] Author ownership checks, admin authorization
- [ ] Input validation, prepared statements, upload MIME/size limits
- [ ] API rate limiting, comment spam protection, admin action audit log

## 14. Media Uploads (not started)
- [ ] `public/uploads/media/` with writable perms
- [ ] MIME/size validation, safe filenames, block executables
- [ ] Cover image upload, media library upload/delete

## 15. Testing (not started)
- [ ] Backend: DB, migrations, seeders, auth, all API groups, validation, errors, rate limiting
- [ ] Frontend: every public page + every dashboard flow, plus no regression after Section 5's component refactor
- [ ] Cross-app isolation: no shared tables/sessions/FKs with `ads`/`teachers`, no data leakage across APIs

## 16. Production Deployment (not started)
- [ ] Production `.env` + DB, migrations/seeders, Apache + `.htaccess`, `/api/v1` routing, upload dir, error logging off in prod, HTTPS
- [ ] Smoke test: API, frontend, dashboard login, media uploads, DB perms

## 17. Final Cleanup (not started)
- [ ] Remove unused mock/debug code, test credentials
- [ ] Confirm `.env` not committed, verify security settings & indexes
- [ ] Verify mobile responsiveness
- [ ] Keep this README's checkboxes in sync with real status

---

## Development Order

```text
1. Foundation → 2. Database → 3. Frontend components → 4. Core modules
→ 5. API → 6. Auth → 7. Frontend/API integration → 8. Mock-data migration
→ 9. Security → 10. Testing → 11. Deployment → 12. Cleanup
```

Work one small task at a time: implement → test → check it off here → move on. Never check off something untested.
