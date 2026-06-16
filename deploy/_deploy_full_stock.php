<?php
declare(strict_types=1);
$token = 'DEPLOY_STOCK_FULL_2026';
if (($_GET['token'] ?? '') !== $token) {
    http_response_code(404);
    exit('Not found');
}

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kernel->call('migrate', ['--force' => true]);

foreach (config('permissions.roles') as $slug => $permissions) {
    \App\Models\Role::where('slug', $slug)->update(['permissions' => $permissions]);
}

$kernel->call('config:clear');
$kernel->call('route:clear');
$kernel->call('view:clear');
$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');

@unlink(__FILE__);
header('Content-Type: text/plain; charset=utf-8');
echo "OK migrations + caches\n";
