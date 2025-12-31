#!/bin/bash

################################################################################
# GCB Plugin Cleanup Script
#
# Removes 24 unused/redundant plugins while preserving GCB- custom plugins
#
# Usage: ./cleanup-plugins.sh [--dry-run]
#
# Features:
# - Automatic database backup
# - Preserves all GCB- plugins (gcb-test-utils, gcb-content-intelligence, etc.)
# - Database integrity verification
# - Rollback capability
# - Detailed logging
################################################################################

set -e  # Exit on error
set -u  # Exit on undefined variable

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_PATH="wp-content/database/.ht.sqlite"
BACKUP_DATE=$(date +%Y%m%d-%H%M%S)
DRY_RUN=false

# Check for dry-run flag
if [[ "${1:-}" == "--dry-run" ]]; then
    DRY_RUN=true
    echo -e "${YELLOW}DRY RUN MODE - No changes will be made${NC}"
    echo ""
fi

################################################################################
# Helper Functions
################################################################################

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_separator() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

################################################################################
# Plugins to Remove (24 total)
################################################################################

# Phase 1: Safe to remove (14 plugins)
PHASE1_PLUGINS=(
    "classic-editor"
    "insert-headers-and-footers"
    "code-snippets"
    "duplicate-page"
    "onesignal-free-web-push-notifications"
    "layout-grid"
    "pwa"
    "qc-simple-link-directory"
    "taxonomy-terms-order"
    "wp-category-permalink"
    "polldaddy"
    "crowdsignal-forms"
    "LayerSlider"
    "revslider"
)

# Phase 2: Verified safe removal (5 plugins)
PHASE2_PLUGINS=(
    "instagram-feed"
    "popup-maker"
    "coblocks"
    "gutenberg"
    "header-footer"
)

# Phase 3: Subscription consolidation (5 plugins)
PHASE3_PLUGINS=(
    "wp-smush-pro"
    "wpmu-dev-seo"
    "snapshot"
    "wpmudev-updates"
    "page-optimize"
)

################################################################################
# Pre-flight Checks
################################################################################

preflight_checks() {
    print_separator
    log_info "Running pre-flight checks..."
    print_separator
    echo ""

    # Check if we're in the WordPress root
    if [[ ! -f "wp-config.php" ]]; then
        log_error "wp-config.php not found. Please run this script from WordPress root directory."
        exit 1
    fi
    log_success "WordPress installation detected"

    # Check if database exists
    if [[ ! -f "$DB_PATH" ]]; then
        log_error "Database not found at $DB_PATH"
        exit 1
    fi
    log_success "Database found: $DB_PATH"

    # Check if plugins directory exists
    if [[ ! -d "wp-content/plugins" ]]; then
        log_error "Plugins directory not found"
        exit 1
    fi
    log_success "Plugins directory found"

    # Check for SQLite3
    if ! command -v sqlite3 &> /dev/null; then
        log_error "sqlite3 command not found. Please install SQLite3."
        exit 1
    fi
    log_success "SQLite3 available"

    echo ""
}

################################################################################
# Backup Functions
################################################################################

create_backups() {
    print_separator
    log_info "Creating backups..."
    print_separator
    echo ""

    if [[ "$DRY_RUN" == true ]]; then
        log_warning "DRY RUN: Would create database backup"
        log_warning "DRY RUN: Would create plugin list backup"
        echo ""
        return
    fi

    # Backup database
    log_info "Backing up database..."
    cp "$DB_PATH" "${DB_PATH}.backup-${BACKUP_DATE}"
    if [[ -f "${DB_PATH}.backup-${BACKUP_DATE}" ]]; then
        log_success "Database backup created: ${DB_PATH}.backup-${BACKUP_DATE}"
        ls -lh "${DB_PATH}.backup-${BACKUP_DATE}"
    else
        log_error "Failed to create database backup"
        exit 1
    fi

    # Backup plugin list
    log_info "Backing up plugin list..."
    ls -1 wp-content/plugins/ | grep -v -E '(hello\.php|index\.php)' > "active-plugins-backup-${BACKUP_DATE}.txt"
    log_success "Plugin list saved: active-plugins-backup-${BACKUP_DATE}.txt"

    echo ""
}

################################################################################
# Plugin Removal
################################################################################

count_plugins() {
    ls -1 wp-content/plugins/ 2>/dev/null | grep -v -E '(hello\.php|index\.php)' | wc -l | xargs
}

remove_plugin() {
    local plugin_name="$1"
    local plugin_path="wp-content/plugins/${plugin_name}"

    # Safety check: Never remove GCB- plugins
    if [[ "$plugin_name" =~ ^gcb- ]] || [[ "$plugin_name" =~ ^GCB- ]]; then
        log_error "SAFETY: Refusing to remove GCB- plugin: $plugin_name"
        return 1
    fi

    if [[ -d "$plugin_path" ]]; then
        if [[ "$DRY_RUN" == true ]]; then
            log_warning "DRY RUN: Would remove $plugin_name"
        else
            rm -rf "$plugin_path"
            log_success "Removed: $plugin_name"
        fi
        return 0
    else
        log_warning "Not found: $plugin_name (skipping)"
        return 0
    fi
}

remove_plugins_phase() {
    local phase_name="$1"
    shift
    local plugins=("$@")
    local removed_count=0

    print_separator
    log_info "$phase_name"
    print_separator
    echo ""

    for plugin in "${plugins[@]}"; do
        if remove_plugin "$plugin"; then
            ((removed_count++)) || true
        fi
    done

    echo ""
    log_info "$phase_name: Processed ${#plugins[@]} plugins"
    echo ""
}

################################################################################
# Verification
################################################################################

verify_database() {
    print_separator
    log_info "Verifying database integrity..."
    print_separator
    echo ""

    if [[ "$DRY_RUN" == true ]]; then
        log_warning "DRY RUN: Would verify database integrity"
        echo ""
        return
    fi

    local integrity_check
    integrity_check=$(sqlite3 "$DB_PATH" "PRAGMA integrity_check;" 2>&1)

    if [[ "$integrity_check" == "ok" ]]; then
        log_success "Database integrity: OK"
    else
        log_error "Database integrity check failed: $integrity_check"
        log_error "Rolling back changes..."
        rollback
        exit 1
    fi

    echo ""
}

verify_theme() {
    if [[ -f "wp-content/themes/gcb-brutalist/theme.json" ]]; then
        log_success "Theme verified: gcb-brutalist"
    else
        log_warning "Theme check: gcb-brutalist theme.json not found"
    fi
}

verify_gcb_plugins() {
    log_info "Verifying GCB- plugins are preserved..."

    local gcb_plugins_found=0
    for plugin_dir in wp-content/plugins/gcb-*; do
        if [[ -d "$plugin_dir" ]]; then
            plugin_name=$(basename "$plugin_dir")
            log_success "Preserved: $plugin_name"
            ((gcb_plugins_found++)) || true
        fi
    done

    if [[ $gcb_plugins_found -eq 0 ]]; then
        log_warning "No GCB- plugins found (this is OK if none were installed)"
    fi
}

################################################################################
# Rollback
################################################################################

rollback() {
    log_warning "Initiating rollback..."

    if [[ -f "${DB_PATH}.backup-${BACKUP_DATE}" ]]; then
        cp "${DB_PATH}.backup-${BACKUP_DATE}" "$DB_PATH"
        log_success "Database restored from backup"
    else
        log_error "Backup file not found: ${DB_PATH}.backup-${BACKUP_DATE}"
    fi
}

################################################################################
# Reporting
################################################################################

generate_report() {
    local initial_count="$1"
    local final_count="$2"
    local removed_count=$((initial_count - final_count))
    local reduction_percent=$((removed_count * 100 / initial_count))

    print_separator
    echo "              PLUGIN CLEANUP COMPLETE"
    print_separator
    echo ""
    echo "RESULTS:"
    echo "  Initial plugins:  $initial_count"
    echo "  Final plugins:    $final_count"
    echo "  Removed:          $removed_count plugins"
    echo "  Reduction:        ${reduction_percent}%"
    echo ""

    if [[ "$DRY_RUN" == false ]]; then
        echo "BACKUPS CREATED:"
        echo "  Database:     ${DB_PATH}.backup-${BACKUP_DATE}"
        echo "  Plugin list:  active-plugins-backup-${BACKUP_DATE}.txt"
        echo ""
    fi

    echo "REMAINING PLUGINS:"
    ls -1 wp-content/plugins/ | grep -v -E '(hello\.php|index\.php)' | nl
    echo ""

    echo "GCB- PLUGINS (PRESERVED):"
    if ls wp-content/plugins/gcb-* 1> /dev/null 2>&1; then
        ls -1 wp-content/plugins/gcb-* | xargs -n 1 basename | sed 's/^/  ✓ /'
    else
        echo "  (none found)"
    fi
    echo ""

    if [[ "$DRY_RUN" == false ]]; then
        echo "NEXT STEPS:"
        echo "  1. Cancel WPMU DEV subscription (WDP ID: 912164)"
        echo "  2. Enable Jetpack features (image optimization, backups, SEO)"
        echo "  3. Test site functionality"
        echo ""
    fi

    print_separator
}

################################################################################
# Main Execution
################################################################################

main() {
    echo ""
    print_separator
    echo "          GCB PLUGIN CLEANUP SCRIPT"
    print_separator
    echo ""
    echo "This script will remove 24 unused/redundant plugins"
    echo "All GCB- plugins will be preserved"
    echo ""

    # Pre-flight checks
    preflight_checks

    # Count initial plugins
    initial_count=$(count_plugins)
    log_info "Initial plugin count: $initial_count"
    echo ""

    # Confirm execution (skip in dry-run mode)
    if [[ "$DRY_RUN" == false ]]; then
        read -p "Continue with plugin cleanup? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log_warning "Cleanup cancelled by user"
            exit 0
        fi
        echo ""
    fi

    # Create backups
    create_backups

    # Remove plugins in phases
    remove_plugins_phase "PHASE 1: Safe Removal (14 plugins)" "${PHASE1_PLUGINS[@]}"
    remove_plugins_phase "PHASE 2: Verified Safe Removal (5 plugins)" "${PHASE2_PLUGINS[@]}"
    remove_plugins_phase "PHASE 3: Subscription Consolidation (5 plugins)" "${PHASE3_PLUGINS[@]}"

    # Verify system
    verify_database
    verify_theme
    verify_gcb_plugins

    # Count final plugins
    final_count=$(count_plugins)

    # Generate report
    echo ""
    generate_report "$initial_count" "$final_count"

    if [[ "$DRY_RUN" == true ]]; then
        echo ""
        log_info "DRY RUN COMPLETE - No changes were made"
        log_info "Run without --dry-run to execute cleanup"
        echo ""
    else
        log_success "Plugin cleanup completed successfully!"
        echo ""
    fi
}

# Run main function
main "$@"
