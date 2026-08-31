# Skoolyst Blog — `blog.skoolyst.com`

Public blog + author/admin dashboard for the Skoolyst family of apps.
Backend: **plain PHP, API-first** (no framework). Frontend: static HTML/CSS/JS calling a JSON API.

This README is the **development task tracker**. Update it as work happens — check items off (`[x]`) only after they're implemented *and* tested. Don't mark a parent item done while its sub-tasks are pending.

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
- [ ] PHP backend — not started
- [ ] Database (`blog_*` tables) — not started
- [ ] `/api/v1/...` — not started
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
```

- [ ] Verify public homepage, blog archive, single post, category, about, contact pages
- [ ] Verify dashboard: overview, posts, post editor, categories, media
- [ ] Verify responsive layout + existing design tokens
- [ ] Keep frontend visual design unchanged unless required for API integration

---

## 5. Backend Foundation (not started)

### Core
- [ ] `core/` — Request, Response, Database, Config loader, Validator
- [ ] Auth middleware, authorization middleware
- [ ] CSRF protection, rate limiter, upload handler, audit log helper
- [ ] Centralized error handling → JSON error responses

### Configuration
- [ ] `.env`, `config/app.php`, `config/database.php`
- [ ] App URL, DB connection, session name, upload paths, API settings

## 6. Database (not started)

All tables prefixed `blog_`:

```text
blog_migrations, blog_users, blog_posts, blog_categories, blog_tags,
blog_post_tags, blog_comments, blog_media, blog_post_views_daily,
blog_audit_log, blog_api_keys
```

- [ ] Migration runner + `blog_migrations`
- [ ] `blog_users` (author/admin fields, password hash, status)
- [ ] `blog_posts` (title, slug, excerpt, body, cover, status, author_id, category_id, published_date, views, SEO fields)
- [ ] `blog_categories` (name, slug, description, color)
- [ ] `blog_tags` + `blog_post_tags`
- [ ] `blog_comments` (post_id, author name/email, body, moderation status)
- [ ] `blog_media` (filename, path, alt text, uploaded_by)
- [ ] `blog_post_views_daily` (daily view aggregation)
- [ ] `blog_audit_log`
- [ ] `blog_api_keys`

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

## 9. Routing (not started)
- [ ] `routes/api.php`, module route loading, `/api/v1` prefix
- [ ] Auth + admin middleware, method validation, JSON 404/405

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
