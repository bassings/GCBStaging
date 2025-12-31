#!/bin/bash
# GCB Content Classification Script
# Runs the classify-all command via REST API
#
# Usage: ./classify-all.sh

set -e

# Configuration
SITE_URL="http://localhost:8881"
API_ENDPOINT="/wp-json/gcb-content-intelligence/v1/classify-all"
TEST_KEY="test-secret-key-local"

echo "🔍 Starting content classification..."
echo ""

# Make the API request
response=$(curl -s -X POST \
  "${SITE_URL}${API_ENDPOINT}" \
  -H "GCB-Test-Key: ${TEST_KEY}" \
  -H "Content-Type: application/json")

# Check if the request was successful
if [ $? -eq 0 ]; then
  echo "✅ Classification complete!"
  echo ""
  echo "📊 Results:"
  echo "$response" | jq '.'
else
  echo "❌ Classification failed"
  echo "$response"
  exit 1
fi
