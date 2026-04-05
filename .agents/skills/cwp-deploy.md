---
name: cwp-deploy
description: Deploy Laravel application to CWP shared hosting. Use when asked about deployment, release, going live, switching version, rollback, or production setup.
---

# Skill: Deployment Flow (CWP Safe)

## Pre-Deploy — Run Locally
```bash
php artisan optimize:clear   # OK — clears all caches
npm run build                # build assets
```

## NEVER run on server:
- `php artisan config:cache`  ← freezes config, breaks production
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan migrate`       ← use phpMyAdmin instead

## Database Migration Strategy
```
1. Run migration locally
2. Export SQL: phpMyAdmin → Export → SQL format
3. Import on server: phpMyAdmin → Import
```

## Files to REMOVE before upload
```
.env
node_modules/
tests/
.git/ (optional)
```

## Files to INCLUDE (must have)
```
vendor/          ← composer install locally, upload vendor/
public/
bootstrap/
config/
database/
resources/
routes/
storage/         ← directory structure only, no content needed
artisan
composer.json
composer.lock
```

## Upload & Folder Structure
```
/public_html/
├── releases/
│   ├── v1/          ← old versions
│   └── v2/          ← new version upload here first
├── current/         ← live version (symlink-free, just rename)
└── backup/          ← previous live version
```

## Deployment Steps
```
Step 1 — Upload to: /public_html/releases/vX
Step 2 — Create .env fresh on server (never copy from local)
Step 3 — Import SQL via phpMyAdmin
Step 4 — Set permissions via CWP File Manager:
          storage/           → 755
          bootstrap/cache/   → 755
          public/uploads/    → 755
Step 5 — Test at: yourdomain.com/releases/vX/public
Step 6 — Switch live:
          rename current → backup
          rename releases/vX → current
```

## .env Required Values (Production)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
APP_KEY=base64:xxx    # generate locally: php artisan key:generate --show

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=password_db

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
LOG_CHANNEL=single
LOG_LEVEL=error
CURL_SSL_VERIFY=true
```

## Post-Deploy Checklist
```
[ ] Homepage loads
[ ] Login works
[ ] Upload file works
[ ] Database query works
[ ] No mixed content warning in browser console
[ ] No errors in storage/logs/laravel.log
```

## Rollback
```
rename current → current_broken
rename backup  → current
```
