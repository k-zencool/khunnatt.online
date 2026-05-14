# CLAUDE.md — khunnatt.online

Developer guide for Claude Code sessions on this project.
Read this before touching any file.

---

## Project overview

Personal portfolio website for **KHUN NATT** (`khunnatt.online`).
Showcases four categories of work: Web Development, Database Design, Hardware, IT Support.

**Design aesthetic:** iOS 26 "Liquid Aura" — dark Glassmorphism, electric-blue/cyan accents, animated radial-gradient orbs, asymmetric Bento-box grid, GSAP 3D card tilt.

**Bilingual:** Thai (default) / English via `?lang=th|en` URL param stored in PHP session.

---

## Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Backend     | PHP 8.3, pure — no framework / CMS  |
| Database    | MariaDB 10.11                       |
| DB access   | PDO + real prepared statements      |
| Web server  | Nginx 1.25 (local) → DirectAdmin Apache (prod) |
| JS          | GSAP 3.12.5 (CDN, defer), Vanilla JS |
| CSS         | Custom design system (no Tailwind)  |
| Local dev   | Docker Compose                      |

---

## Directory layout

```
khunnatt.online/
├── docker-compose.yml          # Local stack (Nginx:8888, phpMyAdmin:8088, MariaDB)
├── docker/
│   ├── nginx/default.conf      # Nginx vhost + security headers
│   └── php/Dockerfile          # PHP 8.3-FPM alpine + extensions
├── sql/
│   └── init.sql                # Schema + seed data (runs on first Docker boot)
├── includes/
│   ├── config.php              # DB constants, PDO singleton, session/lang bootstrap
│   ├── lang.php                # All UI strings (TRANSLATIONS[]), t(), langUrl()
│   └── functions.php           # getProjects(), bentoClass(), h(), safeUrl()
├── assets/
│   ├── css/style.css           # Full design system — CSS custom properties
│   ├── js/main.js              # GSAP hero, scroll reveals, tilt, filter, theme
│   └── images/                 # Project screenshots; placeholder.svg fallback
├── uploads/                    # User-uploaded files (git-ignored except .htaccess)
│   └── .htaccess               # Deny PHP execution in uploads dir
└── index.php                   # Single public entry point
```

---

## Local development

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f nginx
docker compose logs -f php

# Stop + wipe DB volume (forces init.sql to re-run on next boot)
docker compose down -v

# Shell into PHP container
docker compose exec php sh
```

| Service    | URL                          |
|------------|------------------------------|
| Website    | http://localhost:8888        |
| phpMyAdmin | http://localhost:8088        |
| MariaDB    | localhost:3306 (internal)    |

Docker env vars (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) are read by `includes/config.php` via `getenv()`. Changing credentials means updating both `docker-compose.yml` and the fallback defaults in `config.php`.

---

## PHP rules — mandatory

### Load order
Every public PHP file must start with exactly:
```php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/functions.php';
```

### Database
- Always use `getDB()` — never instantiate PDO directly.
- Always use prepared statements. Never interpolate variables into SQL.
- `getDB()` returns `PDO::FETCH_ASSOC` by default.

### Output safety
- Every DB-sourced or user-sourced value echoed to HTML **must** go through `h()`.
- Every URL from DB output must go through `safeUrl()` before use in `href`.
- Never `echo $_GET[...]` or `echo $_POST[...]` without sanitisation.

### Bilingual
- Use `t('key')` for all UI strings — never hardcode Thai or English inline.
- Add new strings to `TRANSLATIONS` in `lang.php` (both `th` and `en`).
- The active language is the `LANG` constant (set in `config.php`).

### Error display
- `display_errors = On` in Dockerfile `custom.ini` — for local Docker only.
- Before deploying to DirectAdmin, set `display_errors = Off` in `config.php` (`ini_set('display_errors', '0')`).

---

## CSS rules

- All design tokens live as CSS custom properties in `style.css` under `:root` / `[data-theme]`.
- Dark mode is the default (`[data-theme="dark"]` on `<html>`).
- Light mode overrides via `[data-theme="light"]`.
- Theme is toggled with JS (`data-theme` attribute) and persisted to `localStorage` under key `kn-theme`.
- The anti-flash script in `<head>` must remain before any stylesheet link — it sets `data-theme` synchronously before first paint.
- Glassmorphism: use the `.glass` class. Do not re-implement `backdrop-filter` inline.
- Bento sizes: `bento-featured` (span 3 col × 2 row), `bento-wide` (3×1), `bento-medium` (2×1), `bento-small` (1×1). Assignment is handled by `bentoClass()` in `functions.php`.

---

## JavaScript rules

- GSAP is loaded from CDN with `defer` and a SRI integrity hash — do not change the hash without verifying the new one.
- ScrollTrigger is intentionally NOT loaded. Scroll reveals use native `IntersectionObserver`.
- All JS is in `assets/js/main.js`. Keep it vanilla — no npm, no build step.
- The aura parallax uses `requestAnimationFrame` throttling — do not replace with `mousemove` listeners without it.
- Category filter hides/shows cards with `gsap.to()` opacity + `display:none` on complete — this must stay in sync with the PHP-rendered `data-category` attributes.

---

## Database schema

Table: `portfolio_projects`

| Column          | Type              | Notes                                          |
|-----------------|-------------------|------------------------------------------------|
| `id`            | INT UNSIGNED PK   | Auto-increment                                 |
| `title_th`      | VARCHAR(255)       | Thai title                                     |
| `title_en`      | VARCHAR(255)       | English title                                  |
| `category`      | ENUM              | `web`, `database`, `hardware`, `it_support`    |
| `description_th`| TEXT              | Thai description                               |
| `description_en`| TEXT              | English description                            |
| `image_path`    | VARCHAR(500) NULL | Relative from project root, e.g. `assets/images/foo.jpg` |
| `github_link`   | VARCHAR(500) NULL | Full HTTPS URL                                 |
| `sort_order`    | TINYINT UNSIGNED  | Lower = shown first; 99 = default              |
| `is_featured`   | TINYINT(1)        | 1 = rendered in large `bento-featured` cell    |
| `created_at`    | TIMESTAMP         | Auto-set on insert                             |
| `updated_at`    | TIMESTAMP         | Auto-updated on every write                    |

Indexes: `idx_category` on `category`, `idx_sort_order` on `(sort_order, id)`.

To add a project: `INSERT` into `portfolio_projects` — no admin panel exists yet. Use phpMyAdmin at http://localhost:8088.

---

## Security checklist (review before every deploy)

- [ ] `display_errors` → `0` in `config.php` for production
- [ ] DB credentials not committed — use DirectAdmin's config or environment variables
- [ ] `uploads/.htaccess` blocks PHP execution in uploads dir
- [ ] Nginx `location ~ /\.(ht|env|git)` block denies access to dot-files
- [ ] All echoed DB values pass through `h()`
- [ ] All echoed URLs pass through `safeUrl()`
- [ ] All SQL uses prepared statements via `getDB()`
- [ ] Session cookie flags: `secure=true, httponly=true, samesite=Lax`

---

## Deployment to DirectAdmin

1. Export DB: phpMyAdmin → Export → `utf8mb4`, SQL format.
2. Upload via DirectAdmin File Manager or SFTP: copy everything except `docker/`, `docker-compose.yml`, `sql/`, `.git/`.
3. Import SQL via DirectAdmin → phpMyAdmin.
4. Update DB constants in `includes/config.php` (or use a `config.local.php` gitignored file).
5. Set file permissions: `uploads/` → 755; PHP files → 644.
6. Point domain `khunnatt.online` to the public_html directory.
7. Enable HTTPS via Let's Encrypt in DirectAdmin → SSL.
8. Verify `SITE_URL` constant in `config.php` matches the live domain.

---

## Adding a new page

1. Create `page-name.php` in the project root.
2. First three lines: `require_once` config → lang → functions (in that order).
3. Call `getDB()` only if the page needs data.
4. Use `h()` on all DB output; `t()` for all UI strings.
5. Mirror the nav and footer HTML from `index.php`.
6. Add the nav link translation key to `TRANSLATIONS` in `lang.php`.

---

## What does NOT exist yet (future work)

- Admin panel for managing projects without phpMyAdmin
- Project detail page (`/project/[id]`)
- Contact form with server-side validation and email delivery
- Image upload pipeline
- Sitemap.xml / robots.txt
- Mobile hamburger menu (nav links are hidden on ≤768px currently)
