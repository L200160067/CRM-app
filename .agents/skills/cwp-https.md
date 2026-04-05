---
name: cwp-https
description: Fix HTTPS, HTTP mixed content, Cloudflare SSL, redirect loop, or asset URL issues in Laravel on CWP shared hosting.
---

# Skill: HTTPS & Cloudflare Fix (CWP Safe)

## Root Cause: Missing TrustProxies
Cloudflare acts as a proxy. Without TrustProxies, Laravel thinks requests are HTTP
even when Cloudflare sends them as HTTPS — causing wrong URLs and redirect loops.

## Fix 1: TrustProxies (MANDATORY for Cloudflare)
```php
// app/Http/Middleware/TrustProxies.php
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies = '*';

    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
```
Ensure `TrustProxies` is registered in `app/Http/Kernel.php` global `$middleware`.

## Fix 2: Force HTTPS in AppServiceProvider
```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

## Fix 3: Mixed Content — Always Use asset()
```blade
{{-- WRONG --}}
<img src="http://domain.com/uploads/foto.jpg">
<link href="http://domain.com/css/app.css">

{{-- CORRECT --}}
<img src="{{ asset('uploads/foto.jpg') }}">
<link href="{{ asset('css/app.css') }}">
```

## Fix 4: .htaccess (inside public/)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Force HTTPS — only if Cloudflare is set to Full or Full (Strict)
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## WARNING: Cloudflare SSL Mode
| Cloudflare Mode | Force HTTPS in .htaccess | Result |
|-----------------|--------------------------|--------|
| Flexible | NO — will cause infinite redirect loop | Loop |
| Full | YES — safe | OK |
| Full (Strict) | YES — safe | OK |

## .env (Production)
```env
APP_URL=https://domain-kamu.com
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```
