# Local Environment Setup Guide

This guide walks you through rebuilding the **LM_Landing** workspace locally with full access to the PHP codebase, assets, and administration tools. Follow the sections in order to clone the repository, install dependencies, configure infrastructure services, and verify that interactive features such as registrations, blogs, and newsletters work as expected.

## Prerequisites

Before you start, make sure the following software is installed on your workstation:

| Tool | Recommended Version | Notes |
| --- | --- | --- |
| PHP | 8.0 or newer | Enable the `pdo_mysql`, `openssl`, and `mbstring` extensions. |
| Composer | 2.x | Used to install PHPMailer and other vendor packages. |
| MySQL or MariaDB | MySQL 8.0+ / MariaDB 10.6+ | Any local server is fine (Docker, Homebrew, XAMPP, etc.). |
| Git | Latest stable | Required to clone the repository and manage branches. |

> **Tip:** If you are working on macOS or Windows, consider installing [Laravel Valet](https://laravel.com/docs/10.x/valet) or [XAMPP](https://www.apachefriends.org/index.html) to bundle PHP, MySQL, and a web server. Containers such as Docker Desktop also work well.

## 1. Clone the Repository

```bash
cd <workspace-directory>
git clone https://github.com/<your-org-or-user>/LM_Landing.git
cd LM_Landing
```

Creating a local clone gives you complete read/write access to the HTML, PHP, and asset files so you can develop new features or debug production issues.

## 2. Install PHP Dependencies

Install the Composer dependencies that power email delivery and other utilities:

```bash
composer install
```

Composer populates the `vendor/` directory, which is required by `mailer.php`. Re-run `composer install` whenever `composer.lock` changes to stay in sync with the repository.

## 3. Configure Writable Directories

The application writes uploaded files and generated data to the `uploads/` and `data/` directories. Ensure your local user (or web-server user, if different) can read and write to these paths:

```bash
chmod -R 775 uploads data
# or, if you need to fix ownership inside a VM/container
sudo chown -R "$USER":www-data uploads data
```

Adjust the group in the `chown` command to match the user that runs PHP on your machine.

## 4. Provision the Database

1. Start MySQL or MariaDB locally.
2. Create a database and a dedicated user. For example:
   ```sql
   CREATE DATABASE lm_landing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'lm_landing'@'localhost' IDENTIFIED BY 'change-me';
   GRANT ALL PRIVILEGES ON lm_landing.* TO 'lm_landing'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Import the schema from `create_table.sql` or run the helper script after updating credentials (see the next section).

## 5. Update Local Credentials

Database credentials are currently hard-coded in several PHP entry points. Replace the production placeholders (`u420143207_*` values) with the credentials you created above. The quickest way is to use a text editor or `sed` to update the following files:

- `register.php`
- `admin.php`
- `admin_complete.php`
- `dashboard.php`
- `blogs.php`
- `blogs-manage.php`
- `blog-edit.php`
- `post-blog.php`
- `api/like.php`
- `contact.php`
- `newsletter.php`
- `export.php`
- `setup_database.php`
- `test_database.php`

Example replacement snippet:

```php
$host = 'localhost';
$dbname = 'lm_landing';
$username = 'lm_landing';
$password = 'change-me';
```

> **Why so many files?** Each script opens its own PDO connection. Centralizing these values is a great refactor candidate, but until that happens you must update each file to point at your local database.

## 6. Create the Schema and Seed Data

After saving your credential updates, you have two options to build the schema:

- **Import the SQL file:**
  ```bash
  mysql -u lm_landing -p lm_landing < create_table.sql
  ```
- **Run the PHP installer (drops existing tables):**
  ```bash
  php setup_database.php
  ```

The PHP installer drops and recreates core tables (`admin_users`, `blog_posts`, `registrations`, `contact_messages`, `newsletter_subscribers`). Use it when you want a clean slate. To verify connectivity, run:

```bash
php test_database.php
```

You should see a success message confirming the PDO connection works.

## 7. Configure Outbound Email (Optional)

`mailer.php` uses PHPMailer to send transactional emails (registration notifications, contact form alerts). Set the following environment variables before starting the PHP server to override the bundled Hostinger defaults:

```bash
export SMTP_HOST=smtp.example.com
export SMTP_PORT=465
export SMTP_USER=notifications@example.com
export SMTP_PASS='<app-password>'
export SMTP_FROM='notifications@example.com'
export SMTP_FROM_NAME='LevelMinds Dev'
export SMTP_TO='you@example.com'           # Optional: default recipient
export SMTP_TO_NAME='Your Name'
```

Store these exports in your shell profile or use a tool like [direnv](https://direnv.net/) to load them automatically for the project.

## 8. Launch the Local Server

Start the built-in PHP web server from the repository root:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000/index.html> in your browser. Test the following flows to confirm everything works locally:

- Submit the technician/business registration form (`register.php`).
- Load the admin dashboard (`admin.php`) and ensure it lists registrations.
- Create and edit blog posts via `blogs-manage.php`.
- Subscribe to the newsletter and submit the contact form.

## 9. Manage Branches and Version Control

You now have full access to the codebase. Create feature branches as needed:

```bash
git checkout -b feature/<short-description>
```

Commit and push your work to your origin remote to collaborate with the rest of the team.

## 10. Troubleshooting

- **PDO connection errors:** Re-run `php test_database.php` to confirm credentials and that the `pdo_mysql` extension is enabled.
- **Permission denied when uploading files:** Double-check directory permissions on `uploads/` and `data/`.
- **Emails not sending:** Enable `SMTP_DEBUG=4` by temporarily editing `mailer.php` or verify your SMTP credentials and firewall settings.

## 11. Reset the Workspace Cache

If form submissions or blog content appear stale after database changes, reset the local cache to repopulate generated files from a clean state:

```bash
php scripts/reset_workspace_cache.php
```

The command removes the contents of common cache directories (including `data/cache/`, `uploads/cache/`, and `storage/cache/`) and rewrites `data/blogs.json` with an empty array. The script exits with status code `1` and prints a JSON payload describing any directories or files it could not modify, so you can fix permissions and re-run it if necessary.

With these steps complete you should have a fully functional replica of the production workspace that you can safely modify and extend.
