# Deployment Guide

This document covers deploying the CMS / page-builder to a production Linux server using the traditional PHP stack: **nginx + php-fpm + MySQL**. No Docker required.

---

## Architecture

```
                    ┌────────────┐
                    │   Browser  │
                    └─────┬──────┘
                          │ HTTPS
                          ▼
                  ┌───────────────┐
                  │   nginx       │
                  │   (TLS term.) │
                  └──────┬────────┘
                         │ fastcgi
                         ▼
                  ┌───────────────┐
                  │   php-fpm     │
                  │   (Laravel)   │
                  └──────┬────────┘
                         │
                         ▼
                  ┌───────────────┐
                  │   MySQL 8     │
                  └───────────────┘

                  + cron → php artisan schedule:run (every minute)
```

---

## Prerequisites

| Requirement | Minimum | Notes |
|---|---|---|
| **OS** | Ubuntu 22.04 / Debian 12 / RHEL 9 | Any modern Linux |
| **PHP** | 8.2+ | with extensions: `pdo_mysql`, `gd`, `intl`, `bcmath`, `zip`, `mbstring`, `xml`, `curl`, `openssl`, `fileinfo` |
| **Composer** | 2.x | https://getcomposer.org |
| **Node.js** | 20+ | only needed at build time, not at runtime |
| **MySQL** | 8.0+ (or MariaDB 10.6+) | |
| **nginx** | any recent | or Apache with `mod_php` |
| **cron** | built in | for scheduled publishes |

---

## Local development setup (test database)

If you plan to run the PHPUnit suite locally, create a separate test database so the tests can refresh schema without touching your dev data:

```sql
CREATE DATABASE page_builder_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;
```

The connection details are baked into `phpunit.xml` (`<env>` block) and only apply when running `./vendor/bin/phpunit`. Your `.env` is unaffected.

Run the suite:

```bash
composer test            # pretty test names
composer test:fast       # compact output
npm test                 # frontend (Vitest)
```

---

## Production deployment

### 1. Generate secrets

On your local machine (or any box with PHP installed):

```bash
# APP_KEY — Laravel's encryption key
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"

# PREVIEW_TOKEN — shared secret for /api/public/preview
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"
```

Keep these somewhere safe — you'll paste them into `.env` on the server.

### 2. Install dependencies

On the production server:

```bash
cd /var/www/html

# PHP dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# JS dependencies + build production assets
npm ci
npm run build
```

### 3. Configure environment

```bash
cp .env.example .env
```

Edit `.env` and set at minimum:

```env
APP_NAME="My CMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cms.example.com
APP_KEY=base64:<from step 1>
PREVIEW_TOKEN=<from step 1>

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=page_builder
DB_USERNAME=builder
DB_PASSWORD=<strong password>

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=<smtp user>
MAIL_PASSWORD=<smtp password>
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="My CMS"
```

### 4. Create the database

```sql
CREATE DATABASE page_builder
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

CREATE USER 'builder'@'localhost' IDENTIFIED BY '<strong password>';
GRANT ALL PRIVILEGES ON page_builder.* TO 'builder'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Migrate + seed (first deploy only)

```bash
php artisan migrate --force
php artisan db:seed --force
```

This creates permissions, roles, the default admin user (`admin@example.com` / `password123` — **change immediately**), and 9 demo sections.

### 6. Storage symlink

```bash
php artisan storage:link
```

Links `public/storage` to `storage/app/public` so uploaded assets are web-accessible.

### 7. Cache for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. File permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### 9. Cron for scheduled publishes

The CMS has a scheduled-publish feature that needs Laravel's scheduler running every minute:

```bash
echo "* * * * * www-data cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" \
    | sudo tee /etc/cron.d/cms-schedule
```

### 10. nginx config

Create `/etc/nginx/sites-available/cms` with:

```nginx
server {
    listen 443 ssl http2;
    server_name cms.example.com;

    ssl_certificate     /etc/letsencrypt/live/cms.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/cms.example.com/privkey.pem;

    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache built assets aggressively
    location ^~ /build/ {
        access_log off;
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ^~ /storage/ {
        access_log off;
        expires 30d;
        add_header Cache-Control "public";
        try_files $uri =404;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name cms.example.com;
    return 301 https://$host$request_uri;
}
```

Then enable it:

```bash
sudo ln -s /etc/nginx/sites-available/cms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 11. Log in and change the default password

Open `https://cms.example.com/login`, sign in as `admin@example.com` / `password123`, and immediately change the password from the Profile screen.

---

## Critical environment variables

| Var | Purpose | Example |
|---|---|---|
| `APP_KEY` | Laravel encryption key. **Never commit this.** | `base64:...` |
| `APP_URL` | Used for asset URLs and absolute links | `https://cms.example.com` |
| `APP_DEBUG` | **Must be `false` in production** | `false` |
| `DB_*` | Database connection | `mysql` host/db/user/pass |
| `MAIL_*` | SMTP for password reset emails | smtp.example.com, port 587, TLS |
| `PREVIEW_TOKEN` | Shared secret for `/api/public/preview` | random 64 hex chars |
| `CACHE_STORE` | `file` is fine; `redis` recommended at scale | `file` or `redis` |
| `SESSION_DRIVER` | `file`, `database`, or `redis` | `database` |
| `QUEUE_CONNECTION` | `database` is fine; `redis` at scale | `database` |

---

## Day-2 operations

### Updates

```bash
cd /var/www/html
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart php-fpm to pick up any code changes
sudo systemctl reload php8.2-fpm
```

### Backups

```bash
# Database — runs daily via cron
mysqldump -u builder -p page_builder > /backups/db-$(date +%F).sql

# Uploaded assets — rsync to offsite storage
rsync -av /var/www/html/storage/app/public/assets/ /backups/assets/
```

Recommended cron:

```
0 3 * * * root mysqldump -u builder -p'<pass>' page_builder | gzip > /backups/db-$(date +\%F).sql.gz
0 4 * * * root rsync -aq /var/www/html/storage/app/public/ /backups/storage/
```

### Logs

```bash
# Laravel application log
tail -f /var/www/html/storage/logs/laravel.log

# nginx access / error
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# php-fpm
tail -f /var/log/php8.2-fpm.log
```

Set up **logrotate** for `storage/logs/laravel.log`:

```bash
sudo tee /etc/logrotate.d/laravel <<EOF
/var/www/html/storage/logs/*.log {
    daily
    rotate 14
    compress
    missingok
    notifempty
    create 0644 www-data www-data
}
EOF
```

### Reset preview token (if compromised)

```bash
NEW=$(php -r "echo bin2hex(random_bytes(32));")
sudo sed -i "s/^PREVIEW_TOKEN=.*/PREVIEW_TOKEN=$NEW/" /var/www/html/.env
sudo php /var/www/html/artisan config:cache
```

All admins will need to paste the new token on the **Publish** screen in the admin UI.

### Run scheduled publishes manually

```bash
cd /var/www/html
php artisan publish:run-scheduled
```

(The cron runs this every minute automatically.)

### Clear the Laravel cache

If config/routes/views seem stale after a deploy:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Then re-cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Hardening checklist

Before pointing real users at the site, verify all of these:

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_KEY` generated and unique per environment
- [ ] `PREVIEW_TOKEN` set to a random ≥32 byte value
- [ ] Default `admin@example.com` password changed immediately after first login
- [ ] HTTPS configured with a valid certificate (Let's Encrypt / certbot)
- [ ] HTTP → HTTPS redirect in nginx
- [ ] `MAIL_MAILER` set to a real SMTP provider (default `log` only writes to file)
- [ ] Database backups scheduled (daily, rotated weekly)
- [ ] Storage/assets backed up (they're not in the database)
- [ ] Cron running `schedule:run` every minute
- [ ] `logrotate` configured for `storage/logs/laravel.log`
- [ ] `APP_URL` matches your real domain
- [ ] Rate limits reviewed in `app/Providers/AppServiceProvider.php` (login throttle)
- [ ] MySQL user has only the privileges it needs (`ALL` on the single app database, nothing else)
- [ ] `storage/` and `bootstrap/cache/` owned by `www-data`, writable

---

## Troubleshooting

**Public site at `/` is empty after deploy**
You didn't run `php artisan db:seed --force`. Run it once — it's idempotent, so safe to re-run on the same DB. Only the sections seeder is guarded against duplicates.

**"Storage symlink already exists"**
Delete the existing directory and re-create the symlink:
```bash
sudo rm -rf public/storage
php artisan storage:link
```

**"Failed to connect to database"**
Check that MySQL is running (`systemctl status mysql`), that the credentials in `.env` match the SQL `GRANT`, and that the user can connect:
```bash
mysql -u builder -p page_builder
```

**Scheduled publishes not running**
```bash
# Check the cron is installed
sudo cat /etc/cron.d/cms-schedule

# Check cron is running at all
sudo systemctl status cron

# Manually test the schedule
cd /var/www/html && php artisan schedule:run
```

**Email not sending**
If you see password reset emails landing in `storage/logs/laravel.log` instead of the recipient's inbox, `MAIL_MAILER=log` is still set. Switch to `smtp` in `.env` and re-cache config.

**Vite assets not loading**
`public/build/manifest.json` is missing. Run `npm run build` on the server (you need Node installed) and make sure `public/build/` is writable.

**502 Bad Gateway**
php-fpm is down or the socket path in nginx is wrong. Check:
```bash
sudo systemctl status php8.2-fpm
ls -la /var/run/php/php8.2-fpm.sock
```

**"Class 'GD' not found" on image upload**
Install the GD extension:
```bash
sudo apt install php8.2-gd
sudo systemctl reload php8.2-fpm
```

---

## Rolling back

If a deploy goes bad:

```bash
cd /var/www/html

# Roll git back to the previous release
git log --oneline -5            # find the previous good commit
git reset --hard <commit-sha>

# Rebuild
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache route:cache view:cache
sudo systemctl reload php8.2-fpm

# If migrations were part of the bad deploy
php artisan migrate:rollback --step=1
```

For content-level rollback (bad publish), use the **Publish History** page in the admin — every publish creates a full snapshot and the previous live state can be restored.
