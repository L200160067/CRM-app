# Project Context — mutudev (Laravel on CWP Shared Hosting)

## Server Environment
- Type: CWP Shared Hosting
- PHP: 8.2
- Database: MySQL (access via phpMyAdmin only)
- DNS/CDN: Cloudflare
- Storage: Local filesystem only

## Hard Constraints
- NO SSH access
- NO terminal execution on server
- NO composer install on server
- NO artisan execution on server
- NO queue workers
- NO cron jobs
- NO reliable symlink (storage:link will fail)

## Architecture Rule
Server = "Dumb PHP Runtime"
All build, optimization, and preparation MUST happen locally.
Server only: serves HTTP, connects to database, reads files.

## Stack
- Framework: Laravel (TALL stack)
- File uploads: public/uploads/ only
- Queue: QUEUE_CONNECTION=sync (no heavy jobs)
- Cache: file driver
- Session: file driver

## Deployment Method
1. Build locally
2. Export SQL locally → import via phpMyAdmin
3. Upload project zip to /public_html/releases/vX
4. Create .env fresh on server (never upload local .env)
5. Switch: rename current → backup, releases/vX → current
