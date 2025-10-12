# LevelMinds Marketing Website

LevelMinds connects visionary schools with inspiring teachers. This repository contains the marketing experience that tells the LevelMinds story, showcases product capabilities, and routes visitors to the live platform at [lmap.in](https://www.lmap.in).

The codebase now runs on a shared layout system with reusable components, consistent blue brand styling (`#3F97D5` / `#3248AD`), and a dynamic blog gallery so every page feels cohesive.

## ✨ Highlights

- **Unified header & footer** – Navigation, CTAs, and footer links are driven from config so updates propagate everywhere in one edit.
- **Modern marketing pages** – Landing, team, careers, tour, solutions, pricing, contact, and blog gallery pages reuse the same design language and responsive grid system.
- **Dynamic blog experience** – `blogs.php` loads approved posts from MySQL, surfaces category views, and renders full articles in an AJAX modal with likes, shares, and view tracking.
- **PDO helpers & config** – Database credentials live in `config/database.php` and shared helpers under `app/` manage view rendering, blog queries, and routing hints.
- **Hostinger-ready** – No build tools required. Upload the repository to `public_html`, update the database credentials, and you are live.

## 🚀 Quick Start (Local or Hostinger)

1. **Clone the repo**
   ```bash
   git clone https://github.com/<your-org>/Lm_Landing.git
   cd Lm_Landing
   ```
2. **Install PHP dependencies**
   ```bash
   composer install
   ```
3. **Configure the database connection**
   - Update `config/database.php` with your MySQL credentials (defaults match the Hostinger instance).
   - Legacy scripts that open their own PDO connection (e.g., `register.php`, `admin.php`) still require manual credential updates—refer to [docs/environment-setup.md](docs/environment-setup.md) for the full checklist.
4. **Import the schema**
   ```bash
   mysql -u <user> -p <database> < create_table.sql
   ```
5. **Serve locally**
   ```bash
   php -S localhost:8000
   ```
   Visit `http://localhost:8000/index.php` to browse the marketing site.

## 📁 Key Directories

| Path | Description |
| --- | --- |
| `bootstrap/` | Loads helpers and autoloaders for every page entry point. |
| `config/` | Site metadata, navigation, and database credentials. |
| `app/` | Utility functions for rendering views and interacting with blog data. |
| `resources/views/` | Marketing page templates rendered through the shared layout. |
| `assets/css/site.css` | Central stylesheet implementing the LevelMinds design system. |
| `api/` | Endpoints for blog content, likes, and view counters. |

## 🌐 Deployment Checklist (Hostinger)

1. Upload repository contents to the Hostinger `public_html` directory.
2. Confirm the database credentials in `config/database.php` match your phpMyAdmin database (defaults are `u420143207_LM_landing` / `u420143207_lmlanding`).
3. Import `create_table.sql` in phpMyAdmin.
4. Ensure `uploads/` and `data/` directories are writable (`chmod 775`).
5. Verify the marketing pages, blog modal, and contact form.

## 📚 Additional Documentation

- [docs/environment-setup.md](docs/environment-setup.md) – Complete local setup instructions, credential updates, and SMTP configuration tips.
- [docs/levelminds-revamp-plan.md](docs/levelminds-revamp-plan.md) – Roadmap and progress log for the redesign workstream.

## 🤝 Support & Contact

Have questions or want to partner with us? Reach out at [hello@levelminds.in](mailto:hello@levelminds.in) or use the contact form on the site.
