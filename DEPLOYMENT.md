# MemoSpark Deployment Guide — o2switch

## Overview

MemoSpark runs as a single Laravel application on o2switch serving:
- **memospark.net** — Public marketing site (vitrine)
- **app.memospark.net** — User space + Admin dashboard + API

Both domains point to the same `/public` directory. Laravel handles domain-based routing internally.

---

## Prerequisites

- o2switch hosting account (user: `yaku4132`)
- SSH access configured (`ssh memospark`)
- MySQL database created via cPanel
- PHP 8.2+ with extensions: pdo_mysql, pdo_pgsql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, gd
- Composer installed
- Node.js 18+ (for asset building)

---

## Step 1: Create MySQL Database (cPanel)

1. Login to cPanel: `https://yaku4132.o2switch.net:2083`
2. Go to **MySQL Databases**
3. Create database: `yaku4132_memospark`
4. Create user: `yaku4132_memospark` with a strong password
5. Add user to database with **ALL PRIVILEGES**

---

## Step 2: Initial Deployment

```bash
# SSH into o2switch
ssh memospark

# Clone the repository
cd /home/yaku4132
git clone https://github.com/hayatbenmoussa1985-bot/memospark-laravel.git
cd memospark-laravel

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Configure environment
cp .env.production .env
nano .env  # Fill in MySQL password, mail credentials, etc.

# Generate key and setup
php artisan key:generate
php artisan storage:link

# Run migrations
php artisan migrate --force

# Seed default data (permissions, plans, badges, super admin)
php artisan db:seed --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify
php artisan app:post-deploy
```

---

## Step 3: Configure Apache (cPanel)

### Option A: Subdomain via cPanel

1. Go to **Domains** or **Subdomains** in cPanel
2. Point `memospark.net` document root to: `/home/yaku4132/memospark-laravel/public`
3. Point `app.memospark.net` document root to: `/home/yaku4132/memospark-laravel/public`

### Option B: Symlinks (if domains already configured)

```bash
# If memospark.net points to /home/yaku4132/memospark.net
rm -rf /home/yaku4132/memospark.net/public_html
ln -s /home/yaku4132/memospark-laravel/public /home/yaku4132/memospark.net/public_html

# If app.memospark.net points to /home/yaku4132/app.memospark.net
rm -rf /home/yaku4132/app.memospark.net/public_html
ln -s /home/yaku4132/memospark-laravel/public /home/yaku4132/app.memospark.net/public_html
```

### SSL Certificates

Enable SSL via cPanel **SSL/TLS** or **AutoSSL** for both domains.

---

## Step 4: DNS Configuration

### For memospark.net (currently on Vercel)

Update DNS to point to o2switch:
```
A     @     YOUR_O2SWITCH_IP
A     www   YOUR_O2SWITCH_IP
CNAME app   YOUR_O2SWITCH_IP (or A record)
```

Wait for DNS propagation (up to 48h), then decommission Vercel.

---

## Step 5: Verify Deployment

```bash
# Run post-deploy checks
php artisan app:post-deploy

# Rebuild caches
php artisan config:cache
php artisan route:cache
```

---

## Updating the Application

```bash
ssh memospark
cd /home/yaku4132/memospark-laravel
./deploy.sh            # No migrations
./deploy.sh --migrate  # With migrations
```

Or manually:
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## File Permissions (o2switch)

```bash
# Storage and cache must be writable by Apache
chmod -R 775 storage bootstrap/cache
chown -R yaku4132:nobody storage bootstrap/cache
```

---

## Cron Job (Task Scheduling)

Add to crontab via cPanel **Cron Jobs**:
```
* * * * * cd /home/yaku4132/memospark-laravel && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## URL Structure

| URL | Handler | Auth |
|-----|---------|------|
| `memospark.net/` | Home page | Public |
| `memospark.net/guide` | Guide | Public |
| `memospark.net/help/*` | Help pages | Public |
| `memospark.net/contact` | Contact form | Public |
| `memospark.net/blog/*` | Blog | Public |
| `memospark.net/privacy-policy` | Privacy | Public |
| `memospark.net/terms-of-service` | Terms | Public |
| `app.memospark.net/login` | Auth | Public |
| `app.memospark.net/user/*` | User space | Auth |
| `app.memospark.net/admin/*` | Admin panel | Auth + Admin |
| `app.memospark.net/api/v1/*` | API for iOS | Sanctum |

---

## Troubleshooting

### 500 Error
```bash
tail -f storage/logs/laravel.log
php artisan config:clear
php artisan cache:clear
```

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

### Cache Issues
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Issues
```bash
php artisan migrate:status
php artisan app:post-deploy
```
