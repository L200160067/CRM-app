---
name: cwp-curl
description: Fix cURL errors in Laravel on CWP shared hosting. Use when encountering cURL 28, cURL 35, cURL 60, cURL 6, cURL 7, SSL errors, timeout, or HTTP client failures.
---

# Skill: cURL Error Fix (CWP Safe)

## Error Reference
| Code | Cause | Fix |
|------|-------|-----|
| cURL 60 / 35 | SSL certificate verify failed | Point to CA bundle or disable verify (dev only) |
| cURL 28 | Timeout — endpoint unreachable | Increase timeout values |
| cURL 6 | DNS resolve failed | Check endpoint URL, check Cloudflare DNS |
| cURL 7 | Connection refused | Endpoint down or wrong port |
| cURL 77 | CA bundle missing/expired | Ask hosting support for CA bundle path |

## Fix: SSL Verification
```php
// Production — point to CA bundle
Http::withOptions([
    'verify' => '/etc/ssl/certs/ca-certificates.crt',
    // alt: '/etc/pki/tls/certs/ca-bundle.crt'
])->get($url);

// Development only — disable (NEVER in production)
Http::withOptions(['verify' => false])->get($url);
```

## Fix: Timeout
```php
Http::timeout(30)
    ->connectTimeout(10)
    ->get($url);
```

## Fix: Internal Requests via Cloudflare (Prevent Loop)
```php
// WRONG — triggers Cloudflare redirect loop
$url = 'http://domain.com/api/endpoint';

// CORRECT — always use HTTPS via config
$url = config('app.url') . '/api/endpoint';
// or
$url = secure_url('/api/endpoint');
```

## Global Config (AppServiceProvider::boot)
```php
use Illuminate\Support\Facades\Http;

Http::globalOptions([
    'timeout'         => 30,
    'connect_timeout' => 10,
    'verify'          => env('CURL_SSL_VERIFY', true),
]);
```

## .env
```env
APP_URL=https://domain-kamu.com   # must be HTTPS
CURL_SSL_VERIFY=true              # false only for local debug
```
