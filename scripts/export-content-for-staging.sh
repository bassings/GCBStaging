#!/bin/bash
#
# Export WordPress content from local and prepare for staging import
# This script:
# 1. Exports all content (posts, pages, etc.) from local WordPress
# 2. Replaces localhost URLs with staging URLs
# 3. Creates a clean XML file ready to import to staging
#

set -e

# Configuration
LOCAL_URL="http://localhost:8080"
STAGING_URL="https://staging-b262-gaycarboys.wpcomstaging.com"
EXPORT_DIR="./content-exports"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
EXPORT_FILE="${EXPORT_DIR}/export-${TIMESTAMP}.xml"
PROCESSED_FILE="${EXPORT_DIR}/staging-ready-${TIMESTAMP}.xml"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting content export from local WordPress...${NC}"

# Create export directory if it doesn't exist
mkdir -p "$EXPORT_DIR"

# Export content from local WordPress using WP-CLI
# Skip media attachments to reduce file size
echo -e "${YELLOW}Step 1: Exporting content from local database (posts and pages only)...${NC}"
docker compose run --rm wpcli wp export \
  --path=/var/www/html \
  --post_type=post,page \
  --stdout > "$EXPORT_FILE" 2>&1

if [ ! -f "$EXPORT_FILE" ] || [ ! -s "$EXPORT_FILE" ]; then
  echo "Error: Export failed or file is empty"
  exit 1
fi

echo -e "${GREEN}✓ Export completed: $EXPORT_FILE${NC}"

# Search and replace localhost URLs with staging URLs
echo -e "${YELLOW}Step 2: Replacing localhost URLs with staging URLs...${NC}"
sed "s|${LOCAL_URL}|${STAGING_URL}|g" "$EXPORT_FILE" > "$PROCESSED_FILE"

echo -e "${GREEN}✓ URL replacement completed${NC}"

# Show summary
echo ""
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}Export Complete!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo ""
echo "Original export: $EXPORT_FILE"
echo "Staging-ready:   $PROCESSED_FILE"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Go to staging admin: ${STAGING_URL}/wp-admin/import.php"
echo "2. Install 'WordPress Importer' if needed"
echo "3. Upload this file: $PROCESSED_FILE"
echo "4. Complete the import"
echo ""
echo -e "${YELLOW}Optional: Commit to git for version control${NC}"
echo "git add $PROCESSED_FILE"
echo "git commit -m 'Export content with ad removals - ${TIMESTAMP}'"
echo ""
