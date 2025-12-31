#!/bin/bash
################################################################################
# GCB Plugin Cleanup Script - WP-CLI Version (for SSH/Remote)
#
# Removes 24 unused/redundant plugins while preserving GCB- custom plugins
#
# Usage via SSH:
#   ssh user@staging-server
#   cd /path/to/wordpress
#   bash cleanup-plugins-wpcli.sh
#
# Or run commands individually (see below)
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "          GCB PLUGIN CLEANUP - WP-CLI VERSION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

################################################################################
# STEP 1: PRE-FLIGHT CHECK
################################################################################
echo -e "${BLUE}[STEP 1]${NC} Checking WP-CLI availability..."
if ! command -v wp &> /dev/null; then
    echo -e "${RED}ERROR:${NC} WP-CLI not found. Please install WP-CLI first."
    exit 1
fi
echo -e "${GREEN}✓${NC} WP-CLI is available"
echo ""

################################################################################
# STEP 2: BACKUP & LIST CURRENT PLUGINS
################################################################################
echo -e "${BLUE}[STEP 2]${NC} Listing current plugins..."
echo ""
wp plugin list
echo ""

echo -e "${YELLOW}Creating backup of plugin list...${NC}"
wp plugin list --format=csv > "plugin-backup-$(date +%Y%m%d-%H%M%S).csv"
echo -e "${GREEN}✓${NC} Backup created"
echo ""

################################################################################
# STEP 3: CONFIRM EXECUTION
################################################################################
echo -e "${YELLOW}WARNING:${NC} This will remove 24 plugins from your WordPress installation."
echo "GCB- plugins (gcb-test-utils, gcb-content-intelligence) will be preserved."
echo ""
read -p "Continue with cleanup? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Cleanup cancelled${NC}"
    exit 0
fi
echo ""

################################################################################
# STEP 4: DEACTIVATE AND DELETE PLUGINS
################################################################################

# Phase 1: Safe to remove (14 plugins)
PHASE1=(
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
PHASE2=(
    "instagram-feed"
    "popup-maker"
    "coblocks"
    "gutenberg"
    "header-footer"
)

# Phase 3: Subscription consolidation (5 plugins)
PHASE3=(
    "wp-smush-pro"
    "wpmu-dev-seo"
    "snapshot"
    "wpmudev-updates"
    "page-optimize"
)

remove_plugin() {
    local plugin="$1"

    # Safety check: Never remove GCB- plugins
    if [[ "$plugin" =~ ^gcb- ]] || [[ "$plugin" =~ ^GCB- ]]; then
        echo -e "${RED}SAFETY: Refusing to remove GCB- plugin: $plugin${NC}"
        return 1
    fi

    # Check if plugin exists
    if wp plugin is-installed "$plugin" 2>/dev/null; then
        # Deactivate if active
        if wp plugin is-active "$plugin" 2>/dev/null; then
            echo -e "${YELLOW}Deactivating:${NC} $plugin"
            wp plugin deactivate "$plugin" --quiet || true
        fi

        # Delete plugin
        echo -e "${BLUE}Deleting:${NC} $plugin"
        wp plugin delete "$plugin" --quiet && echo -e "${GREEN}✓ Removed:${NC} $plugin" || echo -e "${YELLOW}⚠ Not found:${NC} $plugin"
    else
        echo -e "${YELLOW}⚠ Not installed:${NC} $plugin (skipping)"
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 1: Safe Removal (14 plugins)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
for plugin in "${PHASE1[@]}"; do
    remove_plugin "$plugin"
done
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 2: Verified Safe Removal (5 plugins)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
for plugin in "${PHASE2[@]}"; do
    remove_plugin "$plugin"
done
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 3: Subscription Consolidation (5 plugins)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
for plugin in "${PHASE3[@]}"; do
    remove_plugin "$plugin"
done
echo ""

################################################################################
# STEP 5: VERIFICATION
################################################################################
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "VERIFICATION & FINAL REPORT"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo -e "${BLUE}Verifying GCB- plugins are preserved...${NC}"
wp plugin list --name=gcb-* --format=table || echo -e "${YELLOW}No GCB- plugins found (this is OK if none were installed)${NC}"
echo ""

echo -e "${BLUE}Current plugin list:${NC}"
wp plugin list
echo ""

echo -e "${GREEN}✓ CLEANUP COMPLETE!${NC}"
echo ""
echo "NEXT STEPS:"
echo "  1. Cancel WPMU DEV subscription (WDP ID: 912164)"
echo "  2. Enable Jetpack features (image optimization, backups, SEO)"
echo "  3. Test site functionality"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
