## Gay Car Boys – Local WordPress Development

Local WordPress Studio environment for working on [`gaycarboys.com`](http://gaycarboys.com/) and running automated validation (lint, health checks, Playwright tests).

This project uses **WordPress Studio** for local development, which runs PHP via WebAssembly (WASM) and uses SQLite for the database.

### Prerequisites

- WordPress Studio installed and running
- Node.js (LTS 18+) + npm
- Composer (for PHP tooling)

### Getting Started

1. **Start WordPress Studio** and ensure the site is accessible at `http://localhost:8881`

2. **Install dependencies:**

```bash
npm install
composer install
```

3. **Verify the site is running:**

```bash
curl http://localhost:8881
```

### Environment Details

- **Local URL:** `http://localhost:8881`
- **Runtime:** PHP via WebAssembly (WASM)
- **Database:** SQLite (stored in `wp-content/database/.ht.sqlite`)
- **Production:** WordPress.com Managed Hosting (Atomic) running MariaDB/MySQL

### Important: Syncing to Staging

⚠️ **When syncing to the WordPress.com Atomic staging environment:**

Always use **Studio Sync** and **UNCHECK the Database** to prevent overwriting remote content. Only sync files (themes, plugins, uploads).

### Testing

This project uses Playwright for end-to-end testing with a Test-Driven Development (TDD) approach.

**Quick Start:**

1. Ensure WordPress Studio is running at `http://localhost:8881`
2. Activate the `gcb-test-utils` plugin in WordPress admin
3. Set up authentication (one-time):

```bash
npx playwright test auth.setup.ts --project=setup
```

4. Run tests:

```bash
npm test
```

For detailed testing documentation, see [TESTING.md](./TESTING.md).

**Available test commands:**

- `npm test` – Run all Playwright tests
- `npm run test:ui` – Run tests with Playwright UI
- `npm run test:debug` – Run tests in debug mode
- `npm run test:headed` – Run tests with visible browser
- `npm run test:report` – Show test report
- `npm run test:codegen` – Generate tests with Playwright codegen

### Development Workflow

This project follows strict Test-Driven Development (TDD):

1. **RED:** Write a Playwright test for the feature
2. **GREEN:** Write minimum code to make the test pass
3. **REFACTOR:** Optimize while keeping tests green

See [CLAUDE.md](./CLAUDE.md) for full development guidelines and coding standards.

### Project Structure

- **Themes:** `wp-content/themes/gcb-brutalist/` – Custom block theme with editorial brutalism design
- **Plugins:** `wp-content/plugins/` – Includes custom plugins:
  - `gcb-test-utils` – Database reset endpoint for E2E testing
  - `gcb-content-intelligence` – Content classification and schema generation
- **Tests:** `tests/e2e/` – Playwright end-to-end tests
- **Documentation:**
  - [TESTING.md](./TESTING.md) – Testing guide
  - [CLAUDE.md](./CLAUDE.md) – Development guidelines and standards
  - [IMPLEMENTATION-PLAN.md](./IMPLEMENTATION-PLAN.md) – Project progress and status
  - [DESIGN-SYSTEM-REFERENCE.md](./DESIGN-SYSTEM-REFERENCE.md) – Design tokens and patterns

### SQLite Compatibility Notes

The local environment uses SQLite. When writing code:

- ✅ Use `WP_Query` or `$wpdb` methods (they abstract database differences)
- ❌ Avoid raw SQL with MySQL-specific functions (e.g., `UNIX_TIMESTAMP`)
- Always use WordPress APIs for database queries

### Importing Content

For importing content from production, use WordPress Studio's sync feature or WordPress admin import tools.

⚠️ **Important:** When syncing from staging/production, always use **Studio Sync** and **UNCHECK the Database** to prevent overwriting remote content.

### Validation & Code Quality

Install dependencies and run validation:

```bash
npm install
composer install
```

The project uses:

- **PHP:** WordPress Coding Standards (`phpcs.xml.dist`)
- **TypeScript/JavaScript:** ESLint configuration
- **E2E Testing:** Playwright with comprehensive test coverage

### Additional Resources

- **Implementation Status:** See [IMPLEMENTATION-PLAN.md](./IMPLEMENTATION-PLAN.md) for current project status
- **Design System:** See [DESIGN-SYSTEM-REFERENCE.md](./DESIGN-SYSTEM-REFERENCE.md) for design tokens, patterns, and accessibility guidelines
- **Plugin Management:** See [PLUGIN-CLEANUP-README.md](./PLUGIN-CLEANUP-README.md) for plugin organization details
