#!/bin/bash
set -e

echo "⏳ Waiting for WordPress to be ready..."
timeout=60
elapsed=0
while ! curl -s http://localhost:8080 > /dev/null; do
  if [ $elapsed -ge $timeout ]; then
    echo "❌ WordPress failed to start within ${timeout}s"
    exit 1
  fi
  echo "  Waiting... (${elapsed}s/${timeout}s)"
  sleep 2
  elapsed=$((elapsed + 2))
done

echo "✅ WordPress is responding"

echo "🔧 Installing WordPress..."
docker compose exec -T cli wp core install \
  --url="http://localhost:8080" \
  --title="Gay Car Boys" \
  --admin_user="admin" \
  --admin_password="admin" \
  --admin_email="admin@example.com" \
  --skip-email

echo "🎨 Activating theme..."
docker compose exec -T cli wp theme activate gcb-brutalist

echo "🔌 Activating plugins..."
# Activate gcb-test-utils plugin if it exists
if docker compose exec -T cli wp plugin list --field=name | grep -q "gcb-test-utils"; then
  docker compose exec -T cli wp plugin activate gcb-test-utils
  echo "  ✅ gcb-test-utils activated"
fi

# Set permalink structure to match production
docker compose exec -T cli wp rewrite structure "/%postname%/" --hard

# Flush rewrite rules
docker compose exec -T cli wp rewrite flush --hard

echo "✅ WordPress setup complete"

# Display site info
echo ""
echo "📊 Site Information:"
docker compose exec -T cli wp core version
docker compose exec -T cli wp theme list --status=active
docker compose exec -T cli wp plugin list --status=active
