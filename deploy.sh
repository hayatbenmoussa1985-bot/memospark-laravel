#!/bin/bash
# ══════════════════════════════════════════════════════════════
# MemoSpark Deployment Script — o2switch
# ══════════════════════════════════════════════════════════════
#
# Run on the o2switch server as user yaku4132.
#
# First time:   ./deploy.sh --init
# Updates:      ./deploy.sh
# With migrate: ./deploy.sh --migrate
#
# ══════════════════════════════════════════════════════════════

set -e

DEPLOY_DIR="/home/yaku4132/memospark-laravel"
PUBLIC_DIR="${DEPLOY_DIR}/public"
REPO_URL="https://github.com/hayatbenmoussa1985-bot/memospark-laravel.git"
BRANCH="main"
PHP_BIN="/usr/local/bin/php"  # Adjust for o2switch PHP version

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}   MemoSpark Deployment — o2switch                ${NC}"
echo -e "${GREEN}══════════════════════════════════════════════════${NC}"

# ══════════════════════════════════════════════════
# INIT mode (first deployment)
# ══════════════════════════════════════════════════
if [ "$1" = "--init" ]; then
    echo -e "${YELLOW}Running initial deployment...${NC}"

    # Clone repository
    if [ ! -d "$DEPLOY_DIR" ]; then
        echo "Cloning repository..."
        git clone -b "$BRANCH" "$REPO_URL" "$DEPLOY_DIR"
    fi

    cd "$DEPLOY_DIR"

    # Install PHP dependencies
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction

    # Install Node dependencies and build assets
    echo "Installing Node dependencies..."
    npm ci
    echo "Building assets..."
    npm run build

    # Setup environment
    if [ ! -f .env ]; then
        echo -e "${YELLOW}Copying .env.production to .env — EDIT IT before continuing!${NC}"
        cp .env.production .env
        echo "Run: nano .env  (then re-run this script without --init)"
        exit 0
    fi

    # Generate app key
    $PHP_BIN artisan key:generate --force

    # Create storage symlink
    $PHP_BIN artisan storage:link

    # Run migrations
    echo "Running migrations..."
    $PHP_BIN artisan migrate --force

    # Seed default data
    echo "Seeding default data..."
    $PHP_BIN artisan db:seed --force

    # Cache config, routes, views
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
    $PHP_BIN artisan event:cache

    echo -e "${GREEN}Initial deployment complete!${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. Edit .env with your MySQL password and other credentials"
    echo "  2. Create symlinks for domains (see DEPLOYMENT.md)"
    echo "  3. Run: php artisan app:post-deploy"
    exit 0
fi

# ══════════════════════════════════════════════════
# UPDATE mode (subsequent deployments)
# ══════════════════════════════════════════════════
cd "$DEPLOY_DIR"

echo "Pulling latest changes..."
git pull origin "$BRANCH"

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Building assets..."
npm ci
npm run build

# Run migrations if requested
if [ "$1" = "--migrate" ]; then
    echo -e "${YELLOW}Running migrations...${NC}"
    $PHP_BIN artisan migrate --force
fi

# Clear and rebuild caches
echo "Rebuilding caches..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

# Restart queue workers (if any)
$PHP_BIN artisan queue:restart 2>/dev/null || true

echo -e "${GREEN}Deployment complete!${NC}"
echo "Deployed at: $(date)"
