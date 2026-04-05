---
trigger: always_on
---

# CWP Server — Absolute Constraints

This project is on CWP Shared Hosting. No SSH. No terminal. No workers.

## NEVER suggest or generate:
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan event:cache`
- `php artisan storage:link`
- `php artisan migrate` (on server)
- Queue workers or listeners
- Cron jobs or schedulers
- SSH commands
- `Storage::disk('public')` with default disk
- `storage/app/public` as upload destination
- Uploading `.env` from local to server

## ALWAYS use instead:
- `public/uploads/` for all file storage
- `$file->move(public_path('uploads'), $filename)` for uploads
- `asset($path)` for generating file URLs
- Export SQL locally → import via phpMyAdmin for migrations
- `QUEUE_CONNECTION=sync` — but avoid heavy synchronous jobs
- Lazy computation / compute-on-read instead of background jobs
- Paginate all data queries — never use `Model::all()`
- Eager load all relations — never lazy load in loops

## Controller Rule
Keep controllers lightweight.
No heavy loops, no heavy computation inside controllers.

## Error Handling
`APP_DEBUG=false` in production. Always.
