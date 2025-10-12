# LevelMinds Experience & Codebase Redesign Plan

## 1. Objectives
- Deliver a cohesive LevelMinds brand system with a single source of truth for navigation, theming, and layout components.
- Rebuild the marketing funnel (landing, solutions, onboarding CTAs) to match industry-leading teacher hiring platforms while positioning the site explicitly as the marketing companion to the LevelMinds platform.
- Modernize the blogs platform for readers and give admins a powerful authoring workflow.
- Lay groundwork for future teacher and school dashboards while keeping the stack deployable on shared Hostinger hosting.

### Execution Progress (May 2024)
- ✅ Introduced the `config/`, `bootstrap/`, and `resources/views/` directories alongside helper utilities so pages now render through a shared layout that injects the global header and footer.
- ✅ Rebuilt the public landing (`index.php`), team, careers, contact, and blog gallery experiences with the refreshed #3F97D5/#3248AD palette and server-rendered footer to eliminate the JavaScript injection dependency.
- ✅ Extended the layout system to new tour, solutions, pricing, about, privacy, and terms pages while surfacing shared footer navigation generated from configuration.
- ✅ Centralized database access for blogs/likes/views through `lm_db()` and blog helper functions, allowing `blogs.php` and API endpoints to share sanitisation, excerpt, and category logic.
- ✅ Removed superseded IT-services HTML pages and updated the sitemap to reflect the marketing-first structure.
- ⏳ Next milestones: modernise the admin/blog authoring workflow and begin marketplace schema work.

## 2. Current State Assessment
### 2.1 Global Includes
- Header markup lives in `includes/header.php`, already exposing the required links (Home, Team, Tour, Blogs, Careers, Contact, Login / Sign Up) but is duplicated across many static HTML pages that hardcode their own nav. The header also mixes structural classes with bespoke button styles (`btn-nav-primary`, `btn-nav-outline`) and the Login / Sign Up CTA still references legacy staging URLs instead of the production lmap.in application entry point.【F:includes/header.php†L1-L38】
- Footer injection is handled via JavaScript (`assets/js/footer.js`), which means static crawlers do not see links until runtime and pages without the script fall back to empty footers.【F:assets/js/footer.js†L1-L48】

### 2.2 Landing & Marketing Pages
- Legacy `index.html` contained dense inline meta markup, legacy comments, and no shared include usage, making it hard to maintain and update consistently. The rebuilt `index.php` now sources its layout from `resources/views/layouts/app.php`.
- New solutions, tour, pricing, and about templates inherit the shared layout and remove the last pockets of inline styling and duplicated nav markup.【F:resources/views/pages/solutions.php†L1-L155】【F:resources/views/pages/tour.php†L1-L151】
- Policy pages (privacy and terms) now live inside the layout system, enabling config-driven footer links and consistent branding.【F:resources/views/pages/privacy.php†L1-L98】【F:resources/views/pages/terms.php†L1-L98】

### 2.3 Blog Experience
- `blogs.php` contains data access, HTML rendering, and 400+ lines of inline CSS/JS in a single file. It connects directly to the production database credentials and handles decoding, views, likes, and modal rendering inside one script.【F:blogs.php†L1-L200】
- API endpoints such as `api/get_blog.php` and `api/update_views.php` are helpful but lack shared utilities (e.g., central DB connector, sanitization helpers).【F:api/get_blog.php†L1-L120】
- Admin tooling (`blogs-manage.php`, `post-blog.php`) repeats validation logic and stores uploads directly in the project root without media abstraction, complicating future migrations.【F:blogs-manage.php†L1-L160】【F:post-blog.php†L1-L160】

### 2.4 Database & Auth
- The schema in `create_table.sql` covers admin users, blog posts, registrations, contact messages, and newsletter subscribers but lacks tables for job postings, teacher applications, or organization profiles required for the LevelMinds marketplace vision.【F:create_table.sql†L21-L76】

### 2.5 Hosting Constraints
- Site is served from shared hosting with PHP and MySQL (Hostinger). No Node build steps or long-running daemons are available, so all tooling must remain PHP-first and deployable via FTP/SFTP.

## 3. Target Architecture
### 3.1 Directory Layout
```
/ (project root)
├── config/
│   ├── app.php              # Site-wide settings (brand colors, contact info)
│   ├── database.php         # PDO DSN & credentials (loaded from env)
│   └── navigation.php       # Centralized menu definitions
├── public/
│   ├── index.php            # Landing page entry (includes layout)
│   ├── blogs.php            # Blog index (controller style)
│   ├── api/                 # REST-like endpoints (get_blog, like, views, jobs)
│   └── assets/              # CSS/JS/images (built or static)
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.php      # Master layout with header/footer slots
│   │   │   └── auth.php     # Auth-specific layout for admin
│   │   ├── pages/           # Marketing views (landing, solutions, tour)
│   │   ├── blogs/           # Blog cards, modal partials
│   │   └── components/      # Buttons, feature rows, CTA sections
│   ├── styles/              # SCSS/CSS source (compiled to public/assets)
│   └── data/                # YAML/JSON content seeds for landing sections
├── storage/
│   ├── cache/               # Twig/Blade cache or simple PHP fragments
│   └── uploads/             # Media uploads (blog hero images)
├── app/
│   ├── Support/             # Helpers (HTML sanitizer, excerpt builder)
│   ├── Http/
│   │   ├── Controllers/     # PHP classes handling routes
│   │   └── Middleware/      # Authentication, CSRF tokens (admin)
│   ├── Models/              # PDO-backed models (BlogPost, Job, Application)
│   └── Services/            # Business logic (ViewCounter, LikeManager)
└── bootstrap/
    └── app.php              # Autoload includes, start session, load config
```
- `public/` remains deployable via Hostinger by uploading contents to the web root. Rewriting `.htaccess` will map clean URLs to PHP controllers while preserving static asset paths.

### 3.2 Configuration Strategy
- Introduce `config/database.php` to map `$_ENV`/`.env` values to PDO credentials rather than embedding them in `blogs.php`. Use host-provided `.env` (or fall back to constant) for shared hosting compatibility.
- Centralize navigation items in `config/navigation.php` so header & footer render automatically from the same array, ensuring single-point updates.

### 3.3 Rendering Approach
- Adopt a lightweight PHP templating engine (e.g., Plates or native PHP includes) to separate presentation (`resources/views`) from controllers. For Hostinger, avoid composer-heavy frameworks; leverage Composer autoloading already present for PHPMailer.
- Create `View` helper to render templates with slots, enabling a consistent header/footer without repeating includes.

## 4. Design System & Brand Layer
- Anchor the refreshed palette on primary `#3F97D5` (vibrant action blue) and secondary `#3248AD` (trust-heavy indigo), balanced with deep navy `#0F1D3B` for headings and neutral grays (`#F5F7FB`, `#A3AEC2`) for surfaces and dividers.
- Establish tone variants (tints/shades) for states (hover, focus, disabled) and ensure minimum 4.5:1 contrast on body text against white backgrounds.
- Build a component library for hero sections, CTA panels, testimonial sliders, pricing tiers, and metrics, ensuring responsive breakpoints (xs, sm, md, lg, xl) align with Bootstrap 5 grid.
- Replace inline CSS with modular SCSS partials (`resources/styles`) compiled via simple npm script run locally (optional) or manual precompiled CSS uploaded to `public/assets/css` for hosting.
- Document spacing, typography scale (8px baseline), iconography, and animation guidelines in `docs/design-system.md` so designers and engineers share a single reference.

## 5. Landing Experience Redesign
### 5.1 Research & Benchmarking
- Analyze top platforms (Teachmint, MyClassboard, Workable Education) to pull patterns for hero messaging, multi-audience journeys, and trust signals.
- Conduct stakeholder workshop to align on key value props for teachers vs. institutions.

### 5.2 Information Architecture
- Reorganize landing page into: Hero with dual CTAs, How it Works (three-step for schools & teachers), Success Metrics, Featured Schools, Product Tour, Pricing/Plans, Testimonials, FAQs, Blog Teasers, and conversion-focused footer.
- Use dynamic sections to highlight product benefits, referencing job and application flows that will be implemented later, while clearly routing prospective users to the external platform at [lmap.in](https://www.lmap.in) for authentication and onboarding.
- Define scroll-triggered storytelling anchored on the new palette (e.g., alternating white and soft-blue bands) and support dark-on-light CTA buttons that leverage `#3248AD` backgrounds with white text for highest visibility.

### 5.3 Interaction Enhancements
- Integrate microinteractions (scroll-based animations via AOS or pure CSS), ensuring they degrade gracefully on shared hosting.
- Provide sticky CTAs on mobile (e.g., “Post a Job” vs. “Find Jobs”).
- Introduce an insights ribbon that surfaces real-time job counts once the marketplace launches, pulling from cached metrics in `storage/cache/landing.json`.

## 6. Unified Header & Footer
- Build `resources/views/components/header.php` and `footer.php` that read navigation data from configuration. The header retains responsive off-canvas menu while ensuring accessible markup (ARIA roles, focus management) and bakes in the canonical Login / Sign Up target of `https://www.lmap.in` so every CTA stays aligned with the platform entry point.【F:includes/header.php†L1-L38】
- Replace JavaScript-injected footer with server-rendered partial to improve SEO and load-time reliability.【F:assets/js/footer.js†L1-L48】
- Introduce global announcement bar support (optional) managed via config flags for campaigns.
- Provide utility classes (`.lm-nav-link`, `.lm-cta`) in the design system so the header CTA can consistently express the new `#3F97D5`/`#3248AD` palette without per-page overrides.

## 7. Blog Platform Roadmap
### 7.1 Public Blog
- Split concerns: controllers fetch posts, view templates handle markup, JavaScript modules manage modal interactions.
- Serve `Read More` modal content via JSON API, keeping view counts accurate and accessible, while caching blog listings to reduce database load on shared hosting.
- Provide category filters, search, and tags. Extend schema with `slug`, `tags`, `estimated_read_time`, and `seo_meta` fields.【F:blogs.php†L1-L200】

### 7.2 Admin Authoring Suite
- Build an authenticated admin area (`/admin`) with features:
  - Rich text editor (TipTap/CKEditor with HTML sanitization).
  - Media library for hero images stored in `uploads/blogs/`.
  - Draft/preview workflow and scheduled publishing.
  - Analytics dashboard summarizing views, likes, top categories.
- Implement role-based access via `admin_users` table (extend schema for permissions).【F:create_table.sql†L12-L37】

### 7.3 API Hardening
- Move all API endpoints under `public/api/` with route-based controllers. Introduce CSRF tokens for like/view updates to prevent abuse.
- Normalize JSON responses and error handling (HTTP status codes, message envelopes).
- Cache blog lists (category filters, hero card) in `storage/cache/blog_index.json` with manual busting when posts publish to ensure the modal stays performant on shared hosting.

## 8. Marketplace Foundations
- Extend database schema with new tables: `schools`, `teachers`, `job_posts`, `applications`, `application_status_updates`, `saved_jobs`, `notifications`.
- Design teacher dashboard (track applications, update profile, manage documents) and school dashboard (post jobs, manage applicants, analytics). These will live under `/portal/teacher` and `/portal/school` with shared components.
- Provide API endpoints for job discovery, application submission, and status updates to support future SPA or mobile clients.
- Align teacher and school onboarding flows with an extensible status model (draft, submitted, approved) stored in `applications` so teachers can see progress in real time once dashboards go live.

## 9. Deployment & Operations
- Configure `.env` reading via `vlucas/phpdotenv` (Composer) with fallback to Hostinger environment variables.
- Create deployment checklist: asset compilation, database migrations, content sync, backup strategy.
- Use GitHub Actions (or manual script) to lint PHP (`php -l`), run static analysis (`phpstan` level 4), and minify CSS/JS before deployment.
- Add visual regression checks (Percy or BackstopJS run locally with screenshots committed) for critical marketing and blog pages to catch layout drifts after palette and component updates.

## 10. Documentation & Governance
- Produce updated `docs/environment-setup.md` tailored for the new structure, covering local development (PHP built-in server), database migrations, and admin account creation.
- Author `docs/content-workflow.md` describing blog editorial process and asset requirements.
- Maintain change log and release notes for stakeholders.

## 11. Workstream Breakdown
| Phase | Duration | Key Tasks | Owners |
|-------|----------|-----------|--------|
| 0. Discovery | 1 week | Stakeholder interviews, competitive analysis (Teachmint, MyClassboard, Workable), analytics review, inventory existing assets | Product + UX |
| 1. Foundations | 1 week | Implement new directory layout, bootstrap autoloader, migrate header/footer into layout system, configure .env support, create config/navigation.php | Engineering |
| 2. Design System | 1.5 weeks | Finalize palette (#3F97D5/#3248AD), typography scale, component tokens, responsive SCSS, accessibility audit | UX + Front-end |
| 3. Landing Rebuild | 2 weeks | Implement new hero, audience sections, case studies, CTAs, integrate CMS-friendly content blocks, wire metrics ribbon | Front-end + Content |
| 4. Blog Platform | 2 weeks | Refactor public blog, build admin authoring (CRUD, media uploads, scheduling), add analytics dashboard, implement caching strategy | Full-stack |
| 5. Marketplace Prep | 3 weeks | Extend database for jobs/applications, scaffold portals, implement authentication & RBAC, connect dashboards to posting workflow | Backend + Front-end |
| 6. QA & Launch | 1 week | Cross-browser tests, performance pass, SEO checks, regression screenshots, deployment dry runs, content freeze | QA + DevOps |

### 11.1 Task Backlog Summary
- **T001 – Configuration Bootstrap:** Create `config` directory with `app.php`, `database.php`, `navigation.php`; refactor header/footer includes to consume config arrays.
- **T002 – Layout Engine:** Introduce lightweight view renderer (native PHP templates) with `resources/views/layouts/app.php`, ensuring pages call `render('pages/...')` while injecting metadata.
- **T003 – Asset Pipeline:** Consolidate SCSS sources, document build instructions, and ship precompiled `public/assets/css/main.css` aligned to the new palette.
- **T004 – Landing Page Modules:** Produce reusable partials for hero, trust badges, metrics, testimonials, and CTA banners; feed copy and imagery from `resources/data/landing.yml` for easy updates.
- **T005 – Blog Refactor:** Separate concerns between controller, repository, and view; move modal JS to `public/assets/js/blog-modal.js`; add caching and sanitization helpers.
- **T006 – Admin CMS Enhancements:** Implement WYSIWYG editor, image management, draft scheduling, and audit trail for blog authors; ensure permissions derived from updated `admin_users` schema.
- **T007 – Marketplace Schema:** Deliver SQL migration scripts for schools, teachers, job posts, applications, notifications; wire seeders for staging data.
- **T008 – Portal UX:** Define teacher and school dashboard wireframes, build navigation skeletons, and integrate status tracking for applications.
- **T009 – QA Tooling:** Configure linting, static analysis, and visual regression suite; document manual smoke test plans covering jobs, blogs, and marketing flows.

## 12. Risk Mitigation
- Shared hosting limits long-running processes; ensure all build steps occur locally with compiled assets committed to repo.
- Data migrations for new tables must run during low-traffic windows; provide SQL scripts and rollback plans.
- Modal-heavy blog UI must maintain accessibility (focus trapping, keyboard navigation) to avoid regression.

## 13. Next Steps
1. Validate plan with stakeholders and prioritize phases based on business goals.
2. Create detailed technical specs per phase (ERDs, wireframes, API contracts).
3. Kick off Phase 0 discovery and schedule design workshops.
