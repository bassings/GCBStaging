# GCB Magazine - Testing Guide

## Overview

This guide explains how to use the E2E testing infrastructure for the Gay Car Boys WordPress magazine project. All tests follow strict Test-Driven Development (TDD) protocol.

---

## ✅ Initial Setup Complete

Phase 1 infrastructure has been implemented:

- ✅ Playwright testing framework configured
- ✅ TypeScript configuration with path aliases
- ✅ Database reset plugin (`gcb-test-utils`) created
- ✅ Authentication system (saved session state)
- ✅ Test utilities and helpers
- ✅ Smoke tests for infrastructure validation

---

## 🚀 Quick Start

### 1. Start WordPress Studio

Ensure WordPress Studio is running at `http://localhost:8881`

```bash
# Verify WordPress is accessible
curl http://localhost:8881
```

### 2. Activate gcb-test-utils Plugin

1. Navigate to http://localhost:8881/wp-admin/plugins.php
2. Find "GCB Test Utils"
3. Click "Activate"
4. **IMPORTANT**: You should see a warning notice: "⚠️ WARNING: Can DELETE ALL DATABASE CONTENT"

### 3. Set Up Authentication

Run this ONCE to save your admin session:

```bash
npx playwright test auth.setup.ts --project=setup
```

A browser window will open at the login page. Log in with your WordPress admin credentials. The session will be saved to `tests/auth/.auth/admin.json` and reused for all future admin tests.

### 4. Run Tests

```bash
# Run all tests
npm test

# Run smoke tests only
npm run test -- smoke.spec.ts

# Run with UI for debugging
npm run test:ui

# Run in headed mode (see browser)
npm run test:headed

# Generate code for new tests
npm run test:codegen
```

---

## 📁 Project Structure

```
/Volumes/Storage/home/scott.b/repos/GCBStaging/
├── package.json                    # Node.js dependencies
├── tsconfig.json                   # TypeScript configuration
├── playwright.config.ts            # Playwright test configuration
├── global-setup.ts                 # Pre-test environment validation
├── TESTING.md                      # This file
│
├── tests/
│   ├── auth/
│   │   ├── .auth/
│   │   │   └── admin.json          # Saved admin session (gitignored)
│   │   └── auth.setup.ts           # Authentication setup script
│   │
│   ├── e2e/
│   │   ├── admin/
│   │   │   └── reset-db.admin.spec.ts  # Database reset test
│   │   ├── public/
│   │   └── smoke.spec.ts           # Infrastructure validation tests
│   │
│   └── utils/
│       ├── database.ts             # Database helper utilities
│       └── wordpress.ts            # WordPress helper utilities
│
└── wp-content/
    └── plugins/
        └── gcb-test-utils/
            ├── gcb-test-utils.php  # Main plugin file
            └── includes/
                └── class-gcb-database-reset.php  # Reset endpoint logic
```

---

## 🧪 Test Types

### Smoke Tests (`smoke.spec.ts`)

Infrastructure validation tests. Run these first to ensure everything is configured correctly.

```bash
npm run test -- smoke.spec.ts
```

**Tests:**
- WordPress is accessible
- WordPress admin is accessible
- REST API is functional
- gcb-testing/v1 namespace is registered
- Database reset endpoint is accessible
- Database reset endpoint works
- Database reset rejects unauthorized requests

### Admin Tests (`*.admin.spec.ts`)

Tests that require WordPress admin authentication. These use the saved session state from `auth.setup.ts`.

**Example:** `reset-db.admin.spec.ts`
- Creates a post
- Resets database
- Verifies post is deleted

---

## 🔑 Authentication

Authentication is handled via Playwright's `storageState` feature:

1. Run `auth.setup.ts` once to save cookies/session
2. All admin tests reuse the saved session
3. No need to log in for every test

**Re-authenticate:**

```bash
# Delete saved session
rm tests/auth/.auth/admin.json

# Run setup again
npx playwright test auth.setup.ts --project=setup
```

---

## 🔄 Database Reset

The `gcb-test-utils` plugin provides a REST API endpoint to reset the database between tests:

**Endpoint:** `DELETE /wp-json/gcb-testing/v1/reset`

**Authentication:** Requires `X-Test-Key` header matching `GCB_TEST_KEY` in `wp-config.php`

**What it deletes:**
- All posts, pages, and custom post types
- All media attachments
- All terms and taxonomies (except default category)
- All comments
- Orphaned metadata

**Usage in tests:**

```typescript
import { createDatabaseHelper } from '@utils/database';

test('my test', async ({ request }) => {
  const dbHelper = createDatabaseHelper(request);
  await dbHelper.reset();

  // Test with clean database...
});
```

---

## 🛠️ Writing New Tests

### TDD Workflow (MANDATORY)

1. **RED**: Write a failing test
2. **GREEN**: Write minimal code to make test pass
3. **REFACTOR**: Clean up and optimize

### Example Test

```typescript
import { test, expect } from '@playwright/test';
import { createDatabaseHelper } from '@utils/database';

test.describe('My Feature', () => {

  test.beforeEach(async ({ request }) => {
    // Reset database before each test
    const dbHelper = createDatabaseHelper(request);
    await dbHelper.reset();
  });

  test('should do something', async ({ page }) => {
    await page.goto('/');

    // Your test assertions...
    expect(true).toBe(true);
  });
});
```

### Test Projects

Tests are organized into projects (defined in `playwright.config.ts`):

- **setup**: Runs `auth.setup.ts` before other tests
- **admin**: Tests requiring authentication (uses saved storageState)
- **public**: Tests without authentication
- **smoke**: Infrastructure validation tests

**File naming conventions:**
- `*.admin.spec.ts` - Admin tests (authenticated)
- `*.public.spec.ts` - Public tests (no auth)
- `smoke.spec.ts` - Infrastructure tests

---

## 🐛 Troubleshooting

### "WordPress Studio not accessible"

**Problem:** Global setup fails with timeout error

**Solution:**
1. Ensure WordPress Studio is running
2. Verify it's accessible at http://localhost:8881
3. Check if port 8881 is in use by another service

### "Database reset endpoint returns 404"

**Problem:** Test fails because endpoint doesn't exist

**Solution:**
1. Verify `gcb-test-utils` plugin is activated
2. Check http://localhost:8881/wp-json/gcb-testing/v1/ in browser
3. Review WordPress debug.log for errors

### "Database reset returns 401 Unauthorized"

**Problem:** Test key mismatch

**Solution:**
1. Verify `GCB_TEST_KEY` is defined in `wp-config.php`
2. Ensure it matches `test-secret-key-local`
3. Check test is sending `X-Test-Key` header

### "Admin bar not visible"

**Problem:** Authentication failed or session expired

**Solution:**
1. Delete `tests/auth/.auth/admin.json`
2. Re-run auth setup: `npx playwright test auth.setup.ts --project=setup`
3. Verify you logged in successfully

### Tests are "flaky" (sometimes pass, sometimes fail)

**Problem:** Tests polluting each other's state

**Solution:**
1. Always call `dbHelper.reset()` in `beforeEach` hooks
2. Ensure `fullyParallel: false` in playwright.config.ts
3. Use unique identifiers (timestamps) in test data

---

## 📊 Test Reports

After running tests, view the HTML report:

```bash
npm run test:report
```

Results are saved to:
- HTML: `playwright-report/index.html`
- JSON: `test-results/results.json`
- Screenshots: `test-results/` (on failure)
- Videos: `test-results/` (on failure)

---

## ⚠️ Security Warnings

### NEVER in Production

The `gcb-test-utils` plugin should **NEVER** be activated in production:

1. It can delete ALL database content
2. It's protected only by a simple secret key
3. It's designed for local development only

### Environment Check

The plugin will refuse to activate if `WP_ENVIRONMENT_TYPE === 'production'`

### Test Key Security

- The `GCB_TEST_KEY` is defined in `wp-config.php`
- It's only defined for non-production environments
- Change it if you suspect it's been compromised

---

## 📝 Next Steps

Now that Phase 1 is complete, you're ready for:

### Phase 2: GCB Magazine FSE Block Theme
- Create FSE theme structure
- Implement Editorial Brutalism design tokens
- Build block patterns (Hero, Bento Grid)
- Write E2E tests for theme activation

### Phase 3: Custom Post Types
- Create `gcb-core` plugin
- Register `gcb_review` and `gcb_product` post types
- Implement JSON-LD schema for GEO
- Write E2E tests for CPT creation

---

## 🎯 Success Criteria

Phase 1 is complete when:

- [x] `npm test` runs without errors
- [ ] `reset-db.admin.spec.ts` passes (both tests)
- [ ] `smoke.spec.ts` passes (all 7 tests)
- [x] `gcb-test-utils` plugin is active
- [x] `GCB_TEST_KEY` is defined in wp-config.php
- [ ] Admin auth state saved to `tests/auth/.auth/admin.json`
- [ ] Database reset completes in <500ms
- [ ] Test runs are repeatable (idempotent)
- [x] `.gitignore` excludes test artifacts

**Note:** Items marked [ ] require WordPress Studio to be running to complete.

---

## 📚 Resources

- [Playwright Documentation](https://playwright.dev)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [TDD Best Practices](https://martinfowler.com/bliki/TestDrivenDevelopment.html)

---

## 🆘 Getting Help

If you encounter issues:

1. Check this guide's Troubleshooting section
2. Review `wp-content/debug.log` for WordPress errors
3. Run tests with `--debug` flag for step-by-step debugging
4. Use `--headed` flag to see browser interactions
5. Check Playwright trace files in `test-results/`

---

*Last updated: 2025-12-29*
