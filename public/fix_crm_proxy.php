<?php
// ⚠️ HAPUS FILE INI SETELAH DIGUNAKAN
// Script untuk MENGAPLIKASIKAN fix proxy/HTTP untuk aplikasi CRM
// Memperbaiki error "Mixed Content" dan 405 Method Not Allowed pada Livewire

$basePath = dirname(__DIR__);
$bootstrapAppPath = $basePath . '/bootstrap/app.php';
$appServiceProviderPath = $basePath . '/app/Providers/AppServiceProvider.php';

echo "<pre style='font-family:monospace;background:#111;color:#0f0;padding:20px'>";
echo "=== FIX PROXY & HTTPS (CRM APP) ===\n\n";

// 1. FIX bootstrap/app.php
if (file_exists($bootstrapAppPath)) {
    $content = file_get_contents($bootstrapAppPath);
    
    // Cek apakah fix proxy sudah ada
    if (strpos($content, '$request->headers->set(\'X-Forwarded-Proto\', \'https\');') === false) {
        $customProxyFix = <<<'EOT'
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);
        
        // Fix for Cloudflare Flexible SSL / CWP Proxy
        $middleware->trustProxies(at: '*');
        $middleware->append(function ($request, $next) {
            $request->server->set('HTTPS', 'on');
            $request->headers->set('X-Forwarded-Proto', 'https');
            $request->headers->set('X-Forwarded-Port', '443');
            return $next($request);
        });
    })
EOT;

        $original = <<<'EOT'
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);
    })
EOT;
        
        if (strpos($content, $original) !== false) {
            $content = str_replace($original, $customProxyFix, $content);
            file_put_contents($bootstrapAppPath, $content);
            echo "✅ Middleware proxy berhasil ditambahkan ke bootstrap/app.php\n";
        } else {
            echo "⚠️ Gagal replace otomatis di bootstrap/app.php. Format mungkin berbeda.\n";
        }
    } else {
        echo "ℹ️ Middleware proxy sudah ada di bootstrap/app.php\n";
    }
}

// 2. FIX app/Providers/AppServiceProvider.php
if (file_exists($appServiceProviderPath)) {
    $content = file_get_contents($appServiceProviderPath);
    
    if (strpos($content, 'URL::forceScheme(\'https\');') === false) {
        // Find boot method content
        if (strpos($content, 'public function boot(): void') !== false) {
            $forceHttpsCode = <<<'EOT'
    public function boot(): void
    {
        // Force HTTPS URL generation
        if (env('APP_ENV') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

EOT;
            // Replace the function declaration
            $content = str_replace("public function boot(): void\n    {", $forceHttpsCode, $content);
            file_put_contents($appServiceProviderPath, $content);
            echo "✅ URL::forceScheme('https') ditambahkan ke AppServiceProvider.php\n";
        } else {
             echo "⚠️ Fungsi boot() tidak ditemukan di AppServiceProvider.php\n";
        }
    } else {
        echo "ℹ️ URL::forceScheme('https') sudah ada di AppServiceProvider.php\n";
    }
}

// 3. HAPUS CACHE
echo "\n--- Menghapus Cache ---\n";
$cacheFiles = glob($basePath . '/bootstrap/cache/*.php');
if ($cacheFiles) {
    foreach ($cacheFiles as $f) {
        unlink($f);
        echo "🗑️  " . basename($f) . "\n";
    }
}
echo "✅ Config cache dibersihkan\n";

echo "\n🎉 SELESAI! Silakan coba upload Livewire / fitur di CRM Anda.\n";
echo "Pastikan di .env CRM server Anda:\n";
echo "APP_URL=https://crm-mone.alfarez.my.id\n";
echo "SESSION_SECURE_COOKIE=true\n";
echo "\n</pre>";
echo "<b style='color:red'>⚠️ HAPUS file ini setelah diakses!</b>";
