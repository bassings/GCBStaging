Project: WordPress.com Staging (Studio)
Environment
    Local: WordPress Studio (WASM + SQLite)
    Remote: WordPress.com Atomic (Linux Container + MySQL)
    Sync: Bidirectional via Studio Sync (Selective Sync required)

Development Guidelines
    Database Abstraction: Local is SQLite, Remote is MySQL. ALL database interactions must go through WP_Query or $wpdb->prepare(). Never use raw SQL dates or engine-specific syntax.
    Theme/Plugin Scope: Work exclusively within wp-content/themes/[your-theme] or wp-content/plugins.
    Security: Sanitize early, escape late. No exceptions.

Commands
    Linting: composer run lint (if configured)
    Build: npm run build (for block assets)
    Server: There is no server restart command. Changes to PHP files are reflected immediately.

Deployment Checklist
    Verify code works locally.
    Initiate Studio Sync.

