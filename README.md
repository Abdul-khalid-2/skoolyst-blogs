# Task: Rebuild and Simplify `README.md` into a Development Checklist

You are working on the **Skoolyst Blog System** project for:

**`blog.skoolyst.com`**

The current `README.md` contains the complete architecture and development plan, but it is too long and difficult to use as a day-to-day development checklist.

Your task is to **rewrite the existing `README.md`** into a much simpler, well-structured, developer-friendly implementation checklist.

## Important Rules

1. **Do not change the actual project architecture or technical decisions.**
2. Keep the project as a **plain PHP application** — no Laravel, Symfony, or other framework.
3. The backend must be **API-first**.
4. API routes must use:

```text
/api/v1/...
```

5. The existing static HTML/CSS frontend should remain the frontend consumer of the API.
6. Do not introduce server-rendered PHP pages.
7. The application must use its own isolated database tables with the `blog_` prefix.
8. Do not create relationships, joins, or foreign keys with tables belonging to other Skoolyst applications.
9. `ads.skoolyst.com`, `teachers.skoolyst.com`, and `blog.skoolyst.com` are independent applications.
10. Authentication/session configuration must be isolated for this application.
11. Keep compatibility with the architectural style already used by `ads.skoolyst.com`.
12. Do not delete existing functionality just to make the README shorter.

---

# README Structure

Rewrite the README using the following major sections.

## 1. Project Overview

Keep this section very short.

Explain:

* What `blog.skoolyst.com` is.
* That it is part of the Skoolyst app family.
* It provides a public blog and author/admin dashboard.
* Backend is plain PHP.
* Frontend is static HTML/CSS/JS.
* Frontend communicates with backend through JSON API.

---

# 2. Architecture

Create a simple architecture diagram such as:

```text
blog.skoolyst.com
│
├── Static Frontend
│   ├── Public Blog
│   └── Author/Admin Dashboard
│
└── PHP API
    └── /api/v1/...
            │
            ▼
        MySQL Database
        blog_* tables
```

Also clearly state:

```text
blog.skoolyst.com
    ↓
Own PHP application
    ↓
Own blog_* tables
    ↓
No database relationship with other Skoolyst apps
```

Mention that the following are separate applications:

```text
teachers.skoolyst.com
ads.skoolyst.com
blog.skoolyst.com
```

---

# 3. Technology

Create a checklist.

Example:

* [ ] Plain PHP backend
* [ ] MySQL database
* [ ] HTML5
* [ ] Bootstrap 5.3
* [ ] JavaScript
* [ ] Fetch API
* [ ] JSON API
* [ ] Apache / `.htaccess`
* [ ] Environment configuration using `.env`

Do not introduce Laravel or another framework.

---

# 4. Existing Frontend

Document the existing frontend structure.

Keep it simple:

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
└── post-editor.html

assets/
├── css/
├── js/
└── img/
```

Then create small tasks:

* [ ] Verify public homepage
* [ ] Verify blog archive
* [ ] Verify single post page
* [ ] Verify category page
* [ ] Verify about page
* [ ] Verify contact page
* [ ] Verify dashboard homepage
* [ ] Verify dashboard posts page
* [ ] Verify post editor
* [ ] Verify responsive Bootstrap layout
* [ ] Verify existing design tokens
* [ ] Keep frontend design unchanged unless required for API integration

---

# 5. Backend Foundation

Break backend development into very small tasks.

Example:

### Core

* [ ] Create `core/`
* [ ] Create Request handler
* [ ] Create Response handler
* [ ] Create Database class
* [ ] Create environment/config loader
* [ ] Create Validator
* [ ] Create authentication middleware
* [ ] Create authorization middleware
* [ ] Create security utilities
* [ ] Create CSRF protection where required
* [ ] Create rate limiter
* [ ] Create cache helper if required
* [ ] Create upload handler
* [ ] Create audit log helper
* [ ] Add centralized error handling
* [ ] Add JSON error responses

### Configuration

* [ ] Create `.env`
* [ ] Create `config/app.php`
* [ ] Create `config/database.php`
* [ ] Configure application URL
* [ ] Configure database connection
* [ ] Configure session name
* [ ] Configure upload paths
* [ ] Configure API settings

---

# 6. Database

Create a dedicated database checklist.

Important:

**Every table owned by this application must start with `blog_`.**

Planned tables:

```text
blog_migrations
blog_users
blog_posts
blog_categories
blog_tags
blog_post_tags
blog_comments
blog_media
blog_post_views_daily
blog_audit_log
blog_api_keys
```

Create individual tasks:

### Migration System

* [ ] Create `blog_migrations`
* [ ] Build migration runner
* [ ] Test migration execution
* [ ] Test migration tracking

### Users

* [ ] Create `blog_users`
* [ ] Add author/admin fields
* [ ] Add password hashing
* [ ] Add account status
* [ ] Add timestamps

### Posts

* [ ] Create `blog_posts`
* [ ] Add title
* [ ] Add slug
* [ ] Add excerpt
* [ ] Add body
* [ ] Add cover image
* [ ] Add status
* [ ] Add author ID
* [ ] Add category ID
* [ ] Add published date
* [ ] Add view count
* [ ] Add SEO title
* [ ] Add SEO description
* [ ] Add timestamps
* [ ] Add soft-delete support if required

### Categories

* [ ] Create `blog_categories`
* [ ] Add name
* [ ] Add slug
* [ ] Add description
* [ ] Add color

### Tags

* [ ] Create `blog_tags`
* [ ] Create `blog_post_tags`
* [ ] Add post/tag relationship inside the blog application only

### Comments

* [ ] Create `blog_comments`
* [ ] Add post ID
* [ ] Add author name
* [ ] Add author email
* [ ] Add body
* [ ] Add moderation status
* [ ] Add timestamps

### Media

* [ ] Create `blog_media`
* [ ] Add filename
* [ ] Add file path
* [ ] Add alt text
* [ ] Add uploaded-by field
* [ ] Add timestamps

### Analytics

* [ ] Create `blog_post_views_daily`
* [ ] Implement daily view tracking
* [ ] Implement daily aggregation

### Audit

* [ ] Create `blog_audit_log`
* [ ] Implement admin action logging

### API Keys

* [ ] Create `blog_api_keys`
* [ ] Implement API key generation
* [ ] Implement API key validation
* [ ] Implement API key revocation

---

# 7. API

Create a separate API checklist.

All API endpoints must use:

```text
/api/v1/
```

## Public API

* [ ] `GET /api/v1/posts`

* [ ] Add pagination

* [ ] Add category filter

* [ ] Add search filter

* [ ] Add status protection so only published posts are public

* [ ] `GET /api/v1/posts/{id}`

* [ ] Return only published posts

* [ ] `GET /api/v1/categories`

* [ ] `POST /api/v1/posts/{id}/comments`

* [ ] Validate comment input

* [ ] Save comments as pending

* [ ] Add spam protection/rate limiting

* [ ] `POST /api/v1/posts/{id}/view`

* [ ] Increment view count

* [ ] Update daily analytics

## Authentication API

* [ ] `POST /api/v1/auth/login`

* [ ] Validate credentials

* [ ] Create isolated blog session/auth state

* [ ] `POST /api/v1/auth/logout`

* [ ] Destroy blog session/auth state

## Author API

* [ ] `GET /api/v1/author/posts`

* [ ] Return logged-in author's posts

* [ ] `POST /api/v1/author/posts`

* [ ] Create post

* [ ] `PATCH /api/v1/author/posts/{id}`

* [ ] Update own post

* [ ] `DELETE /api/v1/author/posts/{id}`

* [ ] Soft delete own post

* [ ] `POST /api/v1/author/posts/{id}/image`

* [ ] Upload/replace cover image

## Admin API

* [ ] `GET /api/v1/admin/posts`

* [ ] `PATCH /api/v1/admin/posts/{id}`

* [ ] `DELETE /api/v1/admin/posts/{id}`

* [ ] `GET /api/v1/admin/comments`

* [ ] `PATCH /api/v1/admin/comments/{id}`

* [ ] `GET /api/v1/admin/media`

* [ ] `POST /api/v1/admin/media`

* [ ] `DELETE /api/v1/admin/media/{id}`

* [ ] `GET /api/v1/admin/categories`

* [ ] `POST /api/v1/admin/categories`

* [ ] `PATCH /api/v1/admin/categories/{id}`

* [ ] `DELETE /api/v1/admin/categories/{id}`

---

# 8. Application Modules

Create separate checklists for:

```text
app/
├── Posts/
├── Categories/
├── Comments/
├── Media/
└── Auth/
```

For every module create tasks for:

* [ ] Controller
* [ ] Repository
* [ ] Model
* [ ] Validator where required
* [ ] Routes
* [ ] Authentication/authorization where required
* [ ] Validation
* [ ] Error handling
* [ ] Testing

Do not create unnecessary classes if a module does not need them.

---

# 9. Routing

Create small tasks:

* [ ] Create `routes/api.php`
* [ ] Load module route files
* [ ] Merge module routes during application boot
* [ ] Create `/api/v1` prefix
* [ ] Add authentication middleware
* [ ] Add admin middleware
* [ ] Add request method validation
* [ ] Add 404 JSON response
* [ ] Add 405 JSON response

---

# 10. Frontend API Integration

The existing HTML/CSS must remain mostly unchanged.

Replace mock data usage with API calls.

### Public frontend

* [ ] Update `assets/js/app.js`
* [ ] Create API helper
* [ ] Fetch posts from API
* [ ] Fetch categories from API
* [ ] Fetch single post from API
* [ ] Fetch category posts from API
* [ ] Submit comments through API
* [ ] Record post views through API
* [ ] Add loading states
* [ ] Add API error states
* [ ] Add empty states

### Dashboard

* [ ] Update `assets/js/dashboard.js`
* [ ] Add login API handling
* [ ] Load dashboard data from API
* [ ] Load posts from API
* [ ] Create posts through API
* [ ] Edit posts through API
* [ ] Delete posts through API
* [ ] Upload images through API
* [ ] Load comments through API
* [ ] Moderate comments through API
* [ ] Load media through API
* [ ] Manage categories through API

---

# 11. Mock Data Migration

The current frontend uses:

```text
assets/js/mock-data.js
```

Create a dedicated migration checklist:

* [ ] Review all mock authors
* [ ] Review all mock categories
* [ ] Review all mock posts
* [ ] Review mock comments
* [ ] Review mock media
* [ ] Review mock statistics
* [ ] Create database seeder
* [ ] Import mock authors
* [ ] Import mock categories
* [ ] Import mock posts
* [ ] Import mock comments
* [ ] Import mock media
* [ ] Verify imported data
* [ ] Replace mock-data API usage
* [ ] Remove `mock-data.js` only after successful API migration

---

# 12. Authentication & Security

Create individual tasks:

* [ ] Implement password hashing
* [ ] Implement login
* [ ] Implement logout
* [ ] Implement session regeneration
* [ ] Use a unique blog session name
* [ ] Prevent session conflicts with other Skoolyst applications
* [ ] Implement authentication middleware
* [ ] Implement author ownership checks
* [ ] Implement admin authorization
* [ ] Validate all API input
* [ ] Protect file uploads
* [ ] Validate upload MIME types
* [ ] Restrict upload file sizes
* [ ] Protect against SQL injection using prepared statements
* [ ] Add API rate limiting
* [ ] Add comment spam protection
* [ ] Add audit logging for important admin actions

---

# 13. Media Uploads

Create:

```text
public/uploads/media/
```

Tasks:

* [ ] Create upload directory
* [ ] Configure writable permissions
* [ ] Validate uploaded files
* [ ] Validate MIME type
* [ ] Validate file size
* [ ] Generate safe filenames
* [ ] Prevent executable file uploads
* [ ] Store media metadata
* [ ] Implement cover image upload
* [ ] Implement media library upload
* [ ] Implement media deletion

---

# 14. Testing

Create a simple testing checklist.

### Backend

* [ ] Test database connection
* [ ] Test migrations
* [ ] Test seeders
* [ ] Test authentication
* [ ] Test authorization
* [ ] Test public post API
* [ ] Test author API
* [ ] Test admin API
* [ ] Test comments
* [ ] Test media uploads
* [ ] Test API validation
* [ ] Test API errors
* [ ] Test rate limiting

### Frontend

* [ ] Test homepage
* [ ] Test blog archive
* [ ] Test search
* [ ] Test category filter
* [ ] Test single post
* [ ] Test comments
* [ ] Test login
* [ ] Test dashboard
* [ ] Test create post
* [ ] Test edit post
* [ ] Test delete post
* [ ] Test image upload
* [ ] Test media library
* [ ] Test category management

### Cross-Application Isolation

* [ ] Verify blog tables do not conflict with ads tables
* [ ] Verify blog tables do not conflict with teachers tables
* [ ] Verify no cross-application foreign keys exist
* [ ] Verify blog sessions are isolated
* [ ] Verify blog authentication is independent
* [ ] Verify API responses do not expose other application data

---

# 15. Production Deployment

Create tasks:

* [ ] Configure production `.env`
* [ ] Configure production database
* [ ] Run migrations
* [ ] Run seeders if required
* [ ] Configure Apache
* [ ] Configure `.htaccess`
* [ ] Configure `/api/v1` routing
* [ ] Configure upload directory
* [ ] Configure PHP error logging
* [ ] Disable debug output in production
* [ ] Configure HTTPS
* [ ] Test production API
* [ ] Test production frontend
* [ ] Test dashboard login
* [ ] Test media uploads
* [ ] Verify database permissions

---

# 16. Final Cleanup

Create a final checklist:

* [ ] Remove unused mock-data code
* [ ] Remove unused JavaScript
* [ ] Remove unused PHP files
* [ ] Remove debug code
* [ ] Remove test credentials
* [ ] Verify `.env` is not committed
* [ ] Verify API error responses
* [ ] Verify security settings
* [ ] Verify database tables
* [ ] Verify indexes
* [ ] Verify frontend/API integration
* [ ] Verify mobile responsiveness
* [ ] Update README status
* [ ] Mark completed tasks `[x]`

---

# Task Tracking Rules

This README is not just documentation. It is the **development task tracker**.

Every implementation task must use:

```text
- [ ] Task
```

When a task is completely implemented and verified, change it to:

```text
- [x] Task
```

Do not mark a parent task `[x]` if its required subtasks are incomplete.

Example:

```text
## Database

- [ ] Database foundation

### Users

- [x] Create blog_users table
- [x] Add password hashing
- [ ] Add account status
- [ ] Add timestamps
```

Do **not** mark `Database foundation` complete until all required database tasks are complete.

---

# Development Workflow

The README should make the development process clear:

```text
1. Foundation
2. Database
3. Core modules
4. API
5. Authentication
6. Frontend API integration
7. Mock data migration
8. Security
9. Testing
10. Production deployment
11. Cleanup
```

Work on **small tasks one at a time**.

After completing a task:

1. Implement it.
2. Test it.
3. Update `README.md`.
4. Change only that completed task from `[ ]` to `[x]`.
5. Continue to the next task.

Never mark tasks as completed without actually implementing and testing them.

---

# Important Existing Architecture

The existing project already contains a static UI prototype.

Do not unnecessarily rebuild the frontend.

The goal is:

```text
Current:

HTML
  ↓
mock-data.js
  ↓
Hardcoded UI


Final:

HTML
  ↓
JavaScript fetch()
  ↓
/api/v1/...
  ↓
Plain PHP API
  ↓
MySQL
  ↓
blog_* tables
```

The existing visual design should remain consistent with the Skoolyst family, including the existing design tokens and Bootstrap-based UI.

---

# Completion Definition

The project is considered complete only when:

* [ ] Backend is running
* [ ] Database is running
* [ ] All required `blog_*` tables exist
* [ ] Migrations work
* [ ] Seed data works
* [ ] Authentication works
* [ ] Public APIs work
* [ ] Author APIs work
* [ ] Admin APIs work
* [ ] Frontend consumes real APIs
* [ ] Mock data is no longer required
* [ ] Comments work
* [ ] Media uploads work
* [ ] Analytics work
* [ ] Security checks are completed
* [ ] All major frontend flows are tested
* [ ] Production configuration is ready
* [ ] README checklist accurately reflects the real implementation status

**Important:** Keep this README concise, practical, and task-oriented. Avoid long architectural explanations. The README should allow a developer to open the project and immediately understand **what is done, what is pending, and what task should be worked on next**.
