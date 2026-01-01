# GCB Test Session Scripts

## Multi-Session Test Isolation

This directory contains scripts for running E2E tests with session-based isolation, enabling multiple Claude Code instances to work on different tests without interfering with each other.

## How It Works

Each test session gets its own isolated SQLite database:
- Session 1 → `.ht.sqlite.session-1`
- Session 2 → `.ht.sqlite.session-2`
- Development → `.ht.sqlite` (preserved, never modified by tests)

## Current Implementation: Symlink-Based (v1)

**Status**: ✅ Implemented
**Supports**: Sequential sessions (one Claude instance at a time)

### Usage

Run tests with a specific session ID:

```bash
TEST_SESSION_ID=claude-1 npm run test
```

Or let the system auto-generate a session ID from process ID:

```bash
npm run test
```

### How It Works

1. Global setup creates `.ht.sqlite.session-{id}` from baseline
2. Creates symlink: `.ht.sqlite` → `.ht.sqlite.session-{id}`
3. WordPress Studio reads from symlink (session database)
4. Global teardown removes symlink and session database

### Limitations

⚠️ **Current limitation**: Only ONE test session can run at a time because WordPress Studio runs on a single port (8881) with one symlink.

If two Claude instances run simultaneously:
- Both try to create `.ht.sqlite` symlink
- They overwrite each other's symlinks
- Database conflicts occur

## Future Enhancement: Multi-Instance (v2)

**Status**: 📋 Planned
**Supports**: Truly parallel sessions (multiple Claude instances simultaneously)

### Proposed Approach

Run separate WordPress Studio instances for each session:

```bash
# Claude Instance 1
TEST_SESSION_ID=1 ./tests/scripts/start-test-session.sh
# WordPress runs on port 8881, uses .ht.sqlite.session-1

# Claude Instance 2
TEST_SESSION_ID=2 ./tests/scripts/start-test-session.sh
# WordPress runs on port 8882, uses .ht.sqlite.session-2
```

### Requirements

1. WordPress Studio must support:
   - Custom port binding (`--port=8882`)
   - Custom database path (`--db=path/to/.ht.sqlite.session-2`)

2. Update `playwright.config.ts`:
   ```typescript
   const sessionId = process.env.TEST_SESSION_ID || process.pid;
   const port = 8881 + parseInt(sessionId);

   export default defineConfig({
     use: {
       baseURL: `http://localhost:${port}`,
     }
   });
   ```

### Next Steps

1. Research WordPress Studio CLI options for custom database path
2. Test `studio start --port=8882` to verify port binding works
3. Implement `start-test-session.sh` script
4. Update Playwright config for session-aware base URL

## Baseline Database

The `.ht.sqlite.baseline` file is created from your development database on first test run:

- **Purpose**: Template for creating fast session databases
- **Size**: Same as development database (~725 MB currently)
- **Location**: `wp-content/database/.ht.sqlite.baseline`

### Optimizing Baseline (Optional)

To create a smaller, faster baseline:

```bash
npm run test:seed-baseline
```

This will:
1. Reset database to clean state
2. Create minimal realistic content (5 posts, 5 videos, etc.)
3. Save as baseline (~50-100 MB instead of 725 MB)
4. Speed up session creation from 2-5s to <500ms

## Troubleshooting

### Session database not cleaned up

If tests crash, session databases may remain:

```bash
# List session databases
ls -lh wp-content/database/.ht.sqlite.session-*

# Remove all session databases
rm wp-content/database/.ht.sqlite.session-*
```

### Original database not restored

If global teardown fails:

```bash
# Check if backup exists
ls -lh wp-content/database/.ht.sqlite.original

# Manually restore
cp wp-content/database/.ht.sqlite.original wp-content/database/.ht.sqlite
```

### Symlink issues

If symlink becomes corrupted:

```bash
# Check symlink status
ls -lh wp-content/database/.ht.sqlite

# Remove symlink
rm wp-content/database/.ht.sqlite

# Restore from original
cp wp-content/database/.ht.sqlite.original wp-content/database/.ht.sqlite
```

## Testing Session Isolation

Verify session isolation works:

**Terminal 1**:
```bash
TEST_SESSION_ID=test-1 npm run test -- tests/e2e/hero-section.public.spec.ts
```

**Wait for Terminal 1 to complete**, then run Terminal 2:
```bash
TEST_SESSION_ID=test-2 npm run test -- tests/e2e/video-rail.public.spec.ts
```

Expected:
- Session 1 creates `.ht.sqlite.session-test-1`
- Session 2 creates `.ht.sqlite.session-test-2`
- Both use isolated databases
- Original `.ht.sqlite` untouched

⚠️ Don't run both simultaneously yet - symlink approach doesn't support parallel execution.
