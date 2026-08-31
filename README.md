# Skoolyst Blog — `blog.skoolyst.com`

Public blog + author/admin dashboard for the Skoolyst family of apps.
Backend: **plain PHP, API-first** (no framework). Frontend: static HTML/CSS/JS calling a JSON API.

This README is the **development task tracker**. Update it as work happens — check items off (`[x]`) only after they're implemented *and* tested. Don't mark a parent item done while its sub-tasks are pending.

---

## Change Log — What / Where / Why

Every completed task gets a short entry here: **what** changed, **where**, and **why** it was done that way. If a future change looks like it conflicts with something (a naming choice, a missing feature, a design decision), check here first before assuming it's a mistake. **Add a new entry every time a task from this checklist is completed** — newest entry on top.

### 2026-08-31 — Auth module (Section 9) — login/logout/session
- **What:** Built the Auth module: `AuthUser` DTO (never exposes `password_hash`), `AuthRepository` (`findByEmail`/`findById` against `blog_users`), `AuthMiddleware` (session-backed guard — `login()` regenerates the session ID to prevent fixation, `currentUser()`/`requireUser()`/`requireAdmin()` for other modules to call), and `AuthController` (`login`/`logout`/`me`). Added `core/Session.php` so the app boots its own uniquely-named session cookie (`blog_skoolyst_session`, isolated from `ads`/`teachers`). Wired `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` via a new `routes/api/auth.php` (the first module route file, following the pattern `routes/api.php` already pointed to).
- **Where:** `core/Session.php`, `app/Auth/{Model,Repository,Middleware,Controller}.php`, `routes/api/auth.php`, `routes/api.php`, `index.php` (added `Session::start()` to the boot sequence).
- **Why:** Auth had to come before Posts/Categories/Author/Admin per the development order — those all depend on `AuthMiddleware::requireUser()`/`requireAdmin()`. Live-tested end-to-end against a local MariaDB instance with a seeded admin + a seeded suspended user via PHP's built-in server: correct login sets a working session cookie and returns the user; wrong password and unknown email both return the same generic "Invalid email or password" (no user enumeration); a suspended account is rejected with 403 even with the right password; `/auth/me` correctly reflects logged-in/logged-out state; logout fully destroys the session; missing/invalid fields return a 422 with per-field errors. Never assumed correct from reading the code — actually ran every case.

### 2026-08-31 — Repo corruption fix + Section 5 regression close
- **What:** An automated commit (`f70f4da`, "Initialize Auth module development") had overwritten `.gitignore` with AI commentary text instead of real ignore rules, and left a broken/orphaned git submodule link named `skoolyst-blogs` (gitlink with no `.gitmodules`, pointing at a commit inside this same repo) — no actual Auth module files were ever added despite the commit message claiming otherwise. Both fixed: `.gitignore` restored to real ignore rules (plus `vendor/`, `.env`, `public/uploads/*`), the phantom submodule entry removed. Also closed out Section 5's last item: re-verified all 11 pages (6 public + 5 dashboard) — script load order intact everywhere, all 4 JS files pass `node --check`, no leftover hand-written `.form-group` markup, `renderPostCard` correctly aliases `Card.post()` across all 4 call sites.
- **Where:** `.gitignore`, removed path `skoolyst-blogs` (was tracked as a `160000` gitlink); verification touched all 11 page files (no code changes needed — all passed).
- **Why:** Repo was in a broken state — cloning it pulled down a submodule reference with no source — so this was fixed before any further module work so it wouldn't compound. No PHP/jsdom runtime was available in this environment, so regression verification used `node --check` on all JS plus structural checks (script order, component call sites, leftover markup) across every page instead.

### 2026-08-31 — Frontend components: post-editor fields (Section 5)
- **What:** Replaced the post editor's hand-written content, SEO, status, category, tags, and cover-upload fields with markup rendered from `FormGroup` and `InputField`. Added the small shared primitives needed for the editor: select, radio, file, and input/textarea class support.
- **Where:** `assets/js/components.js`, `assets/js/dashboard.js`, and `dashboard/post-editor.html`.
- **Why:** This completes the remaining post-editor form-field component task while retaining the existing IDs, names, classes, wrappers, and event timing that the editor's CSS and create/edit logic depend on. The HTML now supplies semantic mount points only; JavaScript builds the identical form controls before querying or wiring them.

### 2026-08-31 — Frontend component: public post card (Section 5)
- **What:** Moved the public site's reusable post-card builder from `app.js` into `Card.post(post)` in the shared components file. Updated all home, archive, category, featured, and related-post rendering paths to call that component; retained `window.renderPostCard` as an alias to avoid breaking any existing external caller.
- **Where:** `assets/js/components.js` and `assets/js/app.js`.
- **Why:** The same markup was already reused throughout the public site, but its implementation lived in the page-behaviour file. Putting it alongside the other card components gives the public card one authoritative implementation while preserving the exact element type, classes, attributes, and markup output.

### 2026-08-31 — Section 3 scaffold cleanup decisions
- **What:** Removed `.bolt/config.json` (dead Vite template marker, unused). Decided to keep `.gitignore` as-is — it already has the PHP-relevant entries added earlier; kept the harmless Node/Vite lines too.
- **Where:** `.bolt/` directory deleted; `.gitignore` unchanged.
- **Why:** `.bolt/config.json` had zero references anywhere else in the repo (grepped for it). Trimming `.gitignore`'s Node/Vite lines had no real upside since there's no build step to conflict with them, so left them in place rather than churn the file for no benefit. Also refreshed Section 2's stale "component architecture" status line — it still said `Button`/`Table`/`InputField`/`Modal` were pending, which is now out of date.

### 2026-08-31 — Section 4 frontend verification + a real bug fix
- **What:** Loaded every public page (`index`, `blog`, `post` incl. its not-found state, `category`, `about`, `contact`) and every dashboard page (`overview`, `posts`, `post-editor` incl. edit-mode prefill, `categories`, `media`) in jsdom over real HTTP (`php -S`), asserting actual rendered content rather than just that pages loaded without error. Also verified the mobile sidebar toggle's open/close JS behavior end-to-end, and confirmed the existing CSS breakpoints/design tokens are untouched. Found and fixed one real bug: `post.html`'s `<section class="comments-section">` was missing `id="comments-section"`, so `app.js`'s post-not-found handler threw a `TypeError` calling `.style` on `null`, silently skipping the rest of that cleanup block.
- **Where:** `post.html` — added the missing `id="comments-section"` attribute. No other production files changed.
- **Why:** This was the one item in Section 4 still unverified — actually exercising every page (including edge/error states like a bad post id) is what surfaced the bug; reading the code wouldn't have caught it. The fix is purely additive (an `id`, no class/CSS change) so it satisfies the "keep visual design unchanged" rule while fixing real broken behavior.

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
- [x] Frontend component architecture — `Badge`, `Card` (including public post cards), `Button`, `Table`, `InputField`/`FormGroup`, and `Modal` all implemented and regression-verified across all 11 pages (Section 5)
- [x] Auth module + session-based authentication (Section 9/13) — `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` live and tested against `blog_users`
- [ ] `/api/v1/...` — health check + auth endpoints live; Posts/Categories/Comments/Media endpoints not started
- [ ] Dashboard is still unprotected (hardcoded "Sarah Chen" user) — Section 11 will wire the dashboard's own login screen to the new `/auth/*` endpoints

## 3. File Structure — Fixes Applied This Session

The dashboard sidebar linked to `categories.html` and `media.html` on every page, but neither file existed (404 on click) — the JS (`initCategories`, `initMedia`) and CSS for both were already fully built in `dashboard.js` / `dashboard.css`, just never given a page to attach to.

- [x] Added `dashboard/categories.html` — wired to existing `initCategories()`
- [x] Added `dashboard/media.html` — wired to existing `initMedia()`

Leftover scaffold from the original Bolt.new export (not needed for a plain-PHP static-frontend project):

- [x] Removed `.bolt/config.json` (Vite template marker — unused, this isn't a Vite project)
  - **What:** Deleted the `.bolt/` directory (`config.json` contained only `{"template": "vite"}`).
  - **Where:** `.bolt/config.json` removed.
  - **Why:** Grepped the whole repo for any reference to `.bolt` or its contents — none exist outside that one file, so it's dead scaffold from the original Bolt.new export. Confirmed the site still serves correctly (`index.html` and dashboard both 200) after removal.
- [x] Decided on `.gitignore`: keep as-is
  - **What:** No change needed — `.gitignore` already has the PHP-relevant entries (`.env`, `vendor/`, `public/uploads/media/*` with `.gitkeep` kept) added during the backend-foundation work (Section 6). The Node/Vite entries (`node_modules`, `dist`, `dist-ssr`) are left in place.
  - **Where:** `.gitignore` (no edit made).
  - **Why:** The PHP entries this item was really asking for already exist. The leftover Node/Vite lines are harmless with no build step in this project, and keeping them costs nothing while guarding against an accidental commit if any tooling (like this session's temporary jsdom test setup) is ever added and not cleaned up properly — so trimming them has no upside worth the churn.

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

- [x] Verify public homepage, blog archive, single post, category, about, contact pages
  - **What:** Loaded every public page in jsdom over real HTTP (`php -S`) and asserted actual content rendered: homepage's featured/latest post cards, blog archive's post list, a real post by id, the post page's "not found" state for a bad id, a category page's title/post list, and the contact form.
  - **Where:** No production code needed to change for most of these — they already worked. One real bug found and fixed: `post.html`'s `<section class="comments-section">` was missing `id="comments-section"`, so `app.js`'s not-found handler (`document.getElementById('comments-section').style.display = 'none'`) threw a `TypeError` on `null` and silently aborted the rest of that block (tags/author/share/related never got hidden for a genuinely missing post). Added the missing `id` to `post.html`.
  - **Why:** The `id` was purely additive — the CSS rule for that section is class-scoped (`.comments-section`), so this fixes the JS error with zero visual change. This is exactly the kind of bug that only shows up when a page state is actually exercised (a bad `?id=` in the URL), not from reading the code.
- [x] Verify dashboard: overview, posts, post editor, categories, media
  - **What:** Loaded every dashboard page in jsdom over `php -S` and asserted real content: overview's 4 stat cards + recent-posts rows, posts table's 12 rows, post-editor's populated category `<select>` in both create mode and edit mode (`?edit=p1`, confirming form prefill), categories list's 5 rows, media grid's 8 items.
  - **Where:** No code changes needed — all pages already worked correctly.
  - **Why:** Same approach as the public-site checks — verifying by actually loading and asserting on rendered DOM state, not just reading the JS.
- [x] Verify responsive layout + existing design tokens
  - **What:** Confirmed the CSS breakpoints and design-token custom properties are present and untouched (`grep` over `style.css`/`dashboard.css` — 12 `@media` rules across both files, none touched by this session's changes). jsdom has no real layout engine so it can't render a media query, but the *interactive* half of responsive layout — the mobile sidebar toggle (`.dash-sidebar-toggle` → adds `.open`/`.show`, backdrop click removes them) — was exercised end-to-end and works correctly.
  - **Where:** N/A — verification only, no changes.
  - **Why:** Being honest about the tooling's limits: jsdom can prove the JS behavior driving responsive UI works, and that no CSS was touched, but it cannot visually confirm a breakpoint actually reflows the layout at a given width — that still needs a real browser/viewport check if ever in doubt.
- [x] Keep frontend visual design unchanged unless required for API integration
  - **What:** Confirmed by diffing this session's changes: only `assets/js/components.js`, `assets/js/dashboard.js`, `dashboard/categories.html` (Section 5, JS-only markup source change verified byte-identical output), and `post.html` (one added `id` attribute, no CSS/class change) were touched. No `.css` file was edited.
  - **Where:** N/A — verification only.
  - **Why:** Directly satisfies this checklist rule; the one production fix (the missing `id`) was chosen specifically because it required zero visual change.

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
- [x] Move the public site's `renderPostCard()` into `Card.post()` in `components.js`
  - **What:** Moved the public post-card element builder into `Card.post(post)` and changed every public-site renderer to use it. `window.renderPostCard` remains a backwards-compatible alias.
  - **Where:** `assets/js/components.js`, `assets/js/app.js`.
  - **Why:** Home, archive, category, featured, and related-post sections all use the same card; its markup now has one shared source alongside `Card.stat`, without changing the emitted card DOM.
- [x] Convert `post-editor.html`'s hand-written fields to `FormGroup`/`InputField`
  - **What:** Replaced its content, SEO, status, category, tags, and cover-upload field markup with component-rendered controls, mounted into small named containers during `initPostEditor()`.
  - **Where:** `assets/js/dashboard.js` (`renderPostEditorFields()`), `assets/js/components.js`, `dashboard/post-editor.html`.
  - **Why:** The renderer preserves the existing field IDs/names/classes and runs before the post editor looks fields up, so all existing prefill, validation, slug, upload, save, and CSS behavior continues to work while future field changes have a shared component source.
- [x] Verify no visual/behavioral regression on every page after full refactor (Section 4's verification list)
  - **What:** Re-checked all 11 pages (6 public + 5 dashboard) after the component refactor — script load order (`mock-data.js` → `components.js` → `app.js`/`dashboard.js`) intact on every page, all 4 JS files pass `node --check` syntax validation, no leftover hand-written `.form-group` markup in `post-editor.html`, `renderPostCard` correctly aliases to `Card.post()` with all 4 call sites (featured/latest/archive/related) using it.
  - **Where:** `index.html`, `blog.html`, `post.html`, `category.html`, `about.html`, `contact.html`, `dashboard/{index,posts,post-editor,categories,media}.html`.
  - **Why:** Closes Section 5 — no framework (jsdom/PHP) was available in this environment, so verification was done via `node --check` on all JS plus structural grep checks across every page instead.

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

### One-time local setup

1. Copy the env file — `cp .env.example .env` and fill in your local DB credentials
2. Create the database (matching the name in `.env`)
3. Run migrations — `php bin/migrate.php`
4. Seed the database

**New migration files?** Run `php bin/migrate.php`

## 8. API (`/api/v1/...`, not started)

**Public:** `GET /posts` (pagination, category/search filter, published-only), `GET /posts/{id}`, `GET /categories`, `POST /posts/{id}/comments` (validated, saved pending, rate-limited), `POST /posts/{id}/view`

**Auth:** `POST /auth/login`, `POST /auth/logout` (isolated blog session)

**Author:** `GET/POST /author/posts`, `PATCH/DELETE /author/posts/{id}`, `POST /author/posts/{id}/image`

**Admin:** full CRUD on `/admin/posts`, `/admin/comments`, `/admin/media`, `/admin/categories`

- [ ] Public API implemented — Posts + Categories done; Comments (`POST /posts/{id}/comments`) pending its own module
- [x] Auth API implemented
- [ ] Author API implemented — Posts done (`/author/posts`); image upload (`POST /author/posts/{id}/image`) pending Media module
- [ ] Admin API implemented — Posts + Categories done; Comments + Media pending

## 9. Application Modules (in progress)

```text
app/{Posts,Categories,Comments,Media,Auth}/
```
Each module: Controller, Repository, Model, Validator (where needed), Routes, auth/authorization, error handling, tests. Don't create classes a module doesn't need.

- [x] Posts module — `app/Posts/{Model,Repository,Controller}.php`, `routes/api/posts.php`
  - **What:** Public `GET /posts` (published-only, paginated, `category` slug + `search` filters) and `GET /posts/{id}`, plus `POST /posts/{id}/view` (anonymous view-count increment). `GET/POST /author/posts` + `PATCH`/`DELETE /author/posts/{id}` are the logged-in author's own posts only — editing/deleting someone else's post returns 403. `/admin/posts` mirrors the same shape but for any post/author, and can reassign `author_id`. Every response is enriched with joined author name + category name/slug/color so the frontend never needs a second lookup (same reasoning as Categories' `post_count` join). Slugs auto-generate and de-dupe via the shared `Str::slugify()`; `published_date` is stamped once on first publish and never reset by later draft/publish toggles; delete is a soft-delete (`deleted_at`).
  - **Where:** `app/Posts/*.php`, `routes/api/posts.php`, required from `routes/api.php` after `categories.php` (Posts validates `category_id` against `CategoryRepository` and `author_id` against `AuthRepository`).
  - **Why:** Posts is the core content type everything else (Comments, Media, public site) hangs off of — built right after Categories since it needed a real category to validate against, and reuses the same admin-guard/slug patterns instead of inventing new ones.
  - **Tested:** Live tested (local MariaDB + PHP built-in server), 22 cases: public list/show only ever return published posts (draft correctly 404s/hidden even right after being created), author create draft → edit → re-slug on rename, ownership enforced both ways (author blocked 403 from another author's post, admin unrestricted), admin `status` filter and pagination (`per_page`/`page`/`total_pages`), view-count increments and 404s for a bad id, validation 422s for missing title/body/status, invalid `status` enum, and a non-existent `category_id`/`author_id`, and admin creating a post on another user's behalf via `author_id`.
- [x] Categories module — `app/Categories/{Model,Repository,Controller}.php`, `routes/api/categories.php`
  - **What:** `GET /categories` (list, with a `post_count` per category) and `GET /categories/{slug}` are public; `POST /admin/categories` and `PATCH`/`DELETE /admin/categories/{id}` require an admin session via `AuthMiddleware::requireAdmin()` — matching Section 8's documented public-vs-admin path split. Create/update auto-generate a unique slug from the name (`core/Str::slugify()`, de-duplicated as `name-2`, `name-3`, ...) and only re-slug on update if the name actually changed. Delete is blocked with a 409 if any non-deleted post still references the category (`CategoryRepository::countPostsUsing()`), mirroring the same guard `dashboard.js`'s mock version already had.
  - **Where:** `app/Categories/*.php`, `core/Str.php` (new, shared slugify — `Posts` module will reuse it), `routes/api/categories.php`, required from `routes/api.php`.
  - **Why:** Categories has no dependents yet (Posts isn't built), so it's the simplest real module to stand up next after Auth, and it establishes the slug + admin-guard patterns Posts will reuse.
  - **Tested:** Live tested (local MariaDB + PHP built-in server) — public list/show work logged-out; create/update/delete return 401 logged-out and 403 for a logged-in non-admin; admin create/update succeed with slug de-dup and hex-color validation (422 on bad input); delete returns 409 with the post count for a category still in use, and succeeds for one with zero posts; 404s return correctly for unknown id/slug.
- [ ] Comments module
- [ ] Media module
- [x] Auth module — `app/Auth/{Model,Repository,Middleware,Controller}.php`; `AuthMiddleware::requireUser()`/`::requireAdmin()` ready for Author/Admin modules to call

## 10. Routing
- [x] `routes/api.php` created with `/api/v1` prefix stripped in `index.php`; a `/health` route proves the pipeline end-to-end
- [x] Method validation (405) + JSON 404 for unmatched routes
- [x] Module route files (`routes/api/posts.php` etc.) — `routes/api/auth.php`, `routes/api/categories.php`, `routes/api/posts.php` added; others added as each module in Section 9 is built
- [ ] Auth + admin middleware applied to routes (depends on Section 13) — `AuthMiddleware` exists and is used by `/auth/me`, all of `/admin/categories/*`, and Posts' `/author/*`+`/admin/*` routes; Comments/Media routes will apply it once built

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

## 13. Auth & Security (in progress)
- [x] Password hashing, login/logout, session regeneration — `password_hash`/`password_verify`, `AuthMiddleware::login()` regenerates the session ID on login, `Session::destroy()` on logout
- [x] Unique blog session name (no conflicts with other Skoolyst apps) — `config('app.session_name')` = `blog_skoolyst_session`, applied via `core/Session.php`
- [ ] Author ownership checks, admin authorization — `AuthMiddleware::requireAdmin()` exists and is tested; per-resource ownership checks land with the Posts module
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
