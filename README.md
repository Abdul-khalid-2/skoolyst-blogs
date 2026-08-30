# Skoolyst Blogs — `blog.skoolyst.com`

Part of the **Skoolyst app family** (same family as `ads.skoolyst.com`). A blogging
platform for Skoolyst: a public blog website plus an author/admin dashboard.

## Current status

**Static UI prototype only.** Every page is a plain `.html` file styled with
Bootstrap 5.3 + a custom stylesheet, driven by hardcoded mock data in
`assets/js/mock-data.js`. There is no backend, no API, and no database wired
up yet — this document specifies the plan for that next phase.

```
├── index.html                 Public blog home
├── blog.html                  Post archive (search + category filter)
├── post.html                  Single post (?id=)
├── category.html              Posts filtered by category (?slug=)
├── about.html
├── contact.html
├── dashboard/
│   ├── index.html             Dashboard overview (stats, recent posts)
│   ├── posts.html             All posts table (filter/search/status/actions)
│   └── post-editor.html       Create/edit post (?edit={id})
└── assets/
    ├── css/
    │   ├── style.css          Public site styles + design tokens
    │   └── dashboard.css      Dashboard-only styles
    ├── js/
    │   ├── app.js             Public site behaviour (renders from mock data)
    │   ├── dashboard.js       Dashboard behaviour (mock CRUD, local state)
    │   └── mock-data.js       MOCK_AUTHORS / MOCK_CATEGORIES / MOCK_POSTS /
    │                          MOCK_STATS / MOCK_COMMENTS / MOCK_MEDIA — the
    │                          single source of truth a real API replaces
    └── img/
```

Design tokens (`--color-primary: #0f4077`, `--color-secondary: #4361ee`,
`Inter` / `JetBrains Mono`, `8/12/18px` radius scale) are the same ones used
by `ads.skoolyst.com`, so the two apps read as one product family.

## Backend plan (next phase)

**Stack decision: plain PHP, no framework (no Laravel/Symfony/etc.), same
architectural style already used in `skoolyst-advertisement`
(`ads.skoolyst.com`)** — a small hand-rolled `core/` kernel, one folder per
domain module under `app/`, and a route-table array per module merged at
boot. This keeps every Skoolyst app consistent to work on and reason about.

**API-based, not web routes.** The PHP backend only exposes a JSON API under
`/api/v1/...` (same convention as the ads app). The existing static HTML
pages become the API's consumers: `assets/js/app.js` and
`assets/js/dashboard.js` are updated to `fetch()` real endpoints instead of
reading `mock-data.js`, without the HTML/CSS needing to change. There is no
server-rendered PHP page/template layer for this app (unlike
`ads.skoolyst.com`'s `admin/*.php` pages, which render HTML server-side) —
`blog.skoolyst.com`'s HTML stays fully static and talks to the API purely
over `fetch()`.

Planned structure, mirroring `skoolyst-advertisement`:

```
├── app/
│   ├── Posts/
│   │   ├── PostController.php
│   │   ├── PostRepository.php
│   │   ├── PostModel.php
│   │   ├── PostValidator.php
│   │   └── routes.php
│   ├── Categories/
│   │   ├── CategoryController.php
│   │   ├── CategoryRepository.php
│   │   └── routes.php
│   ├── Comments/
│   │   ├── CommentController.php
│   │   ├── CommentRepository.php
│   │   └── routes.php
│   ├── Media/
│   │   ├── MediaController.php
│   │   ├── MediaRepository.php
│   │   └── routes.php
│   └── Auth/
│       ├── AuthController.php
│       ├── UserRepository.php
│       └── routes.php
├── core/                       (same kernel style as the ads app: Request,
│                                 Response, Validator, Database, Env,
│                                 Auth/Middleware, Security/Csrf, Cache,
│                                 AuditLog, Uploads, RateLimiter)
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── migrations/              blog_-prefixed tables only (see below)
│   └── seeders/
├── routes/
│   └── api.php                  merges every module's routes.php
└── public/
    ├── index.php                 front controller / router entry
    ├── .htaccess
    ├── assets/                   (existing static assets, unchanged)
    └── uploads/media/            (real uploaded cover images / media library)
```

### Database: shared instance, isolated table names

The database server/instance is **shared across the whole Skoolyst app
family** (same MySQL instance also used by `ads.skoolyst.com`, and future
apps like `teachers.skoolyst.com`), but **every app owns its own,
non-colliding table names** — nothing here may reuse a name already taken by
another app's schema (e.g. `ads.skoolyst.com` already owns `users`, `apps`,
`api_keys`, `audit_log`, `migrations`, `ads`, `ad_clicks`, `ad_impressions`,
`ad_stats_daily`, `placements`).

**Convention: every table in this app is prefixed `blog_`.** This guarantees
no collision with the ads app today, and with any other app added to the
same database later, without needing to track every other app's table list
by hand.

Planned tables:

| Table | Purpose |
|---|---|
| `blog_migrations` | This app's own migration-tracking table (separate from the ads app's `migrations`) |
| `blog_users` | Authors/admins for this app (separate from the ads app's `users` — no shared login between apps unless a future SSO layer is built on top) |
| `blog_posts` | Post content: title, slug, excerpt, body, cover_image, status (draft/published), author_id, category_id, published_at, view_count, seo_title, seo_description |
| `blog_categories` | id, name, slug, description, color |
| `blog_tags` | id, name, slug |
| `blog_post_tags` | pivot: post_id, tag_id |
| `blog_comments` | post_id, author_name, author_email, body, status (pending/approved/spam), created_at |
| `blog_media` | Uploaded media library items: filename, path, alt_text, uploaded_by, created_at |
| `blog_post_views_daily` | Rollup table for view analytics (same pattern as the ads app's `ad_stats_daily`) |
| `blog_audit_log` | Admin action log (post published/deleted, comment moderated, etc.) — same pattern as the ads app's `audit_log` |
| `blog_api_keys` | If `blog.skoolyst.com` ever needs to expose posts to other Skoolyst apps the way `ads.skoolyst.com`'s `/api/v1/ads/serve` does for ad placements |

### Planned API endpoints (`/api/v1/...`)

Following the same public / authenticated / admin split used by the ads
app's route tables:

```
GET    /api/v1/posts                    Public — published posts, paginated, filterable (?category=, ?search=, ?page=)
GET    /api/v1/posts/{id}               Public — single published post
GET    /api/v1/categories               Public — category list
POST   /api/v1/posts/{id}/comments      Public — submit a comment (goes to 'pending')
POST   /api/v1/posts/{id}/view          Public — increment view count

POST   /api/v1/auth/login               Author/admin login
POST   /api/v1/auth/logout

GET    /api/v1/author/posts             Auth — the logged-in author's own posts
POST   /api/v1/author/posts             Auth — create a post (own)
PATCH  /api/v1/author/posts/{id}        Auth — update own post
DELETE /api/v1/author/posts/{id}        Auth — soft-delete own post
POST   /api/v1/author/posts/{id}/image  Auth — upload/replace cover image

GET    /api/v1/admin/posts              Admin — all posts, any author
PATCH  /api/v1/admin/posts/{id}         Admin — edit any post (unscoped)
DELETE /api/v1/admin/posts/{id}         Admin — delete any post (unscoped)
GET    /api/v1/admin/comments           Admin — moderation queue
PATCH  /api/v1/admin/comments/{id}      Admin — approve/reject a comment
GET    /api/v1/admin/media              Admin — media library
POST   /api/v1/admin/media              Admin — upload media
DELETE /api/v1/admin/media/{id}         Admin — delete media
GET    /api/v1/admin/categories         Admin — manage categories
POST   /api/v1/admin/categories
PATCH  /api/v1/admin/categories/{id}
DELETE /api/v1/admin/categories/{id}
```

### Migration to a real backend

1. Stand up `core/` + `config/` + `.env` (copy the ads app's kernel files as
   a starting point — `Database.php`, `Request.php`, `Response.php`,
   `Validator.php`, `Auth/Middleware.php`, etc. are backend-agnostic enough
   to reuse near-verbatim).
2. Write the `blog_*` migrations and a seeder that loads the current
   `mock-data.js` content into real rows, so the UI shows the same content
   before and after the switch.
3. Build each `app/<Module>/` controller + repository per the endpoint list
   above.
4. Swap `assets/js/app.js` / `assets/js/dashboard.js` from reading
   `window.MOCK_*` to `fetch()`-ing the real endpoints — HTML/CSS untouched.
5. Retire `assets/js/mock-data.js` once the swap is verified end-to-end.
