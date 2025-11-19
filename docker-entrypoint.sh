#!/bin/bash
set -e

echo "🚀 DigiParc Fleet Management v3.0.0"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Wait for database to be ready
echo "⏳ Waiting for database..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "✓ Database is ready!"

# Create .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env

    # Generate JWT secret
    JWT_SECRET=$(openssl rand -base64 32)

    # Update .env
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
    sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    sed -i "s/APP_ENV=.*/APP_ENV=$APP_ENV/" .env

    echo "✓ .env file created!"
fi

# Create config/config.php if it doesn't exist
if [ ! -f "config/config.php" ]; then
    echo "📝 Creating config/config.php..."
    cat > config/config.php <<EOF
<?php
// Auto-generated Docker configuration
define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('JWT_SECRET', '$(openssl rand -base64 32)');
define('APP_ENV', '$APP_ENV');
define('DEBUG', false);
define('APP_NAME', 'DigiParc Fleet Management');
define('APP_VERSION', '3.0.0');
define('APP_URL', 'http://localhost');
EOF
    echo "✓ config/config.php created!"
fi

# Run migrations
echo "🔄 Running database migrations..."
if [ -d "database/migrations" ]; then
    for migration in database/migrations/*.sql; do
        if [ -f "$migration" ]; then
            echo "   - Applying $(basename $migration)..."
            mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration" 2>/dev/null || true
        fi
    done
    echo "✓ Migrations completed!"
fi

# Create super admin if not exists
echo "👤 Checking super admin..."
ADMIN_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sse "SELECT COUNT(*) FROM users WHERE role='super_admin'" 2>/dev/null || echo "0")

if [ "$ADMIN_EXISTS" = "0" ]; then
    echo "   Creating super admin..."
    ADMIN_PASS_HASH=$(php -r "echo password_hash('admin123', PASSWORD_BCRYPT);")
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
INSERT INTO users (email, password, first_name, last_name, role, status, created_at)
VALUES ('admin@digiparc.com', '$ADMIN_PASS_HASH', 'Super', 'Admin', 'super_admin', 'active', NOW());
EOF
    echo "✓ Super admin created!"
    echo "   Email: admin@digiparc.com"
    echo "   Password: admin123"
    echo "   ⚠️  CHANGE THIS PASSWORD IMMEDIATELY!"
else
    echo "✓ Super admin already exists!"
fi

# Start cron daemon
echo "⏰ Starting CRON daemon..."
cron
echo "✓ CRON daemon started!"

# Clear cache
echo "🧹 Clearing cache..."
rm -rf storage/cache/*
echo "✓ Cache cleared!"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ DigiParc is ready!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 Web: http://localhost"
echo "📍 API: http://localhost/api/v1"
echo "📍 phpMyAdmin: http://localhost:8080"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Execute the main command
exec "$@"
