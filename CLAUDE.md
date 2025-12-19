# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a local Dockerized WordPress development environment for gaycarboys.com, designed to approximate typical shared hosting (Apache + PHP 8.1 + MariaDB 10.6). It includes automated validation pipelines for linting, health checks, and end-to-end testing.

## Architecture

### Docker Stack (docker-compose.yml)

Three services form the development environment:

- **db**: MariaDB 10.6 database
- **wordpress**: wordpress:php8.1-apache image, mounts `./wordpress` to `/var/www/html`
- **wpcli**: wordpress:cli-php8.1 for WP-CLI commands

The WordPress container uses a custom config at `config/wp-config-local.php` instead of the standard auto-generated one.

### Environment Configuration

Configuration is controlled via `.env` file (copy from `.env.example`). Key variables:
- `WEB_PORT` (default: 8080)
- `WP_HOME` (local URL, e.g., http://localhost:8080)
- `DB_*` variables for database credentials
- `WP_ADMIN_USER`, `WP_ADMIN_PASSWORD`, `WP_ADMIN_EMAIL` for initial install

### Content Structure

- `wordpress/`: The WordPress installation root (mapped into container)
- `wordpress/wp-content/themes/`: Custom and default themes
- `wordpress/wp-content/plugins/`: Plugins
- `scripts/`: Shell scripts for setup, import, and health checks
- `tests/phpunit/`: PHPUnit tests
- `tests/e2e/`: Playwright end-to-end tests
- `docs/`: Import guides for WordPress.com and self-hosted sites

## Common Commands

### Docker Operations

```bash
make up              # Start containers
make down            # Stop and remove containers
make restart         # Down then up
make logs            # Follow container logs
make wp CMD="..."    # Run WP-CLI commands (e.g., make wp CMD="plugin list")
make wp-shell        # Interactive WP-CLI shell
make db-shell        # MySQL shell into database
```

### Initial Setup

```bash
cp .env.example .env
make up
./scripts/wp-install-local.sh  # Fresh WordPress install using .env values
```

### Import from Production

The import process differs based on hosting type:

**WordPress.com (Jetpack Backup):**
```bash
./scripts/wp-import-jetpack-backup.sh ./jetpack-backup-YYYY-MM-DD.zip
```

**WordPress.com (XML Export):**
```bash
./scripts/wp-import-from-wordpress-com.sh ./export.xml
```

**Self-hosted:**
```bash
./scripts/wp-import-from-prod.sh /path/to/prod.sql /path/to/wp-content
```

For guidance, run: `./scripts/wp-export-helper.sh`

### Validation Pipeline

```bash
npm install          # Install Node.js dependencies (first time)
composer install     # Install PHP tooling (first time)
npm run validate     # Run full validation suite
```

Individual validation steps:
```bash
npm run lint:php     # PHP CodeSniffer (WordPress Coding Standards)
npm run lint:js      # ESLint on wp-content JS files
npm run lint:css     # Stylelint on wp-content CSS files
npm run lint         # All linting tasks
npm run health       # HTTP health checks (scripts/health-check.js)
npm run test:phpunit # PHPUnit tests
npm run test:e2e     # Playwright end-to-end tests
```

### Testing

**PHPUnit:**
- Config: `phpunit.xml.dist`
- Test directory: `tests/phpunit/`
- Bootstrap: `tests/phpunit/bootstrap.php`
- Run: `npm run test:phpunit` or `phpunit`

**Playwright:**
- Config: `playwright.config.ts`
- Test directory: `tests/e2e/`
- Base URL: Uses `WP_HOME` env var or defaults to http://localhost:8080
- Run: `npm run test:e2e` or `npx playwright test`

### Linting

**PHP (phpcs.xml.dist):**
- Standards: WordPress-Core, WordPress-Docs, WordPress-Extra
- Target: `wordpress/wp-content/themes/` and `wordpress/wp-content/plugins/`
- PHP compatibility: 8.1+
- Fix auto-fixable issues: `composer phpcbf`

**JavaScript/CSS:**
- ESLint and Stylelint scan `wordpress/wp-content/**/*.{js,css}`
- Currently minimal/placeholder configuration

## WP-CLI Usage

All WP-CLI commands run via the `wpcli` container:

```bash
make wp CMD="plugin list"
make wp CMD="theme activate twentytwentyfour"
make wp CMD="user list"
make wp CMD="db export /var/www/html/backup.sql"
make wp CMD="search-replace 'http://oldsite.com' 'http://localhost:8080'"
```

For interactive shell: `make wp-shell`

## Import Scripts

- `wp-install-local.sh`: Fresh WordPress install (no production data)
- `wp-import-from-prod.sh`: Import from self-hosted WordPress (SQL dump + wp-content)
- `wp-import-from-wordpress-com.sh`: Import WordPress.com XML export
- `wp-import-jetpack-backup.sh`: Import Jetpack Backup ZIP
- `wp-import-jetpack-extracted.sh`: Import extracted Jetpack Backup folder
- `wp-import-jetpack-sql-folder.sh`: Import Jetpack SQL folder structure
- `wp-export-helper.sh`: Interactive helper to determine hosting type and guide import

All import scripts handle database import and URL search-replace automatically.

## Development Workflow

1. Start environment: `make up`
2. Make changes to themes/plugins in `wordpress/wp-content/`
3. Changes are immediately reflected (volume mount)
4. Run validation before committing: `npm run validate`
5. Use WP-CLI for database operations: `make wp CMD="..."`

## Notes

- The `wordpress/` directory is git-tracked but contains standard WordPress core files. Custom work should go in `wp-content/`.
- Database data persists in Docker volume `db_data` between restarts.
- The validation pipeline is designed to run in CI (GitHub Actions) using the same `npm run validate` command.
- Local URL is configurable via `WP_HOME` in `.env` (defaults to http://localhost:8080).
