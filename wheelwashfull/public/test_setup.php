<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Constants\UserRole;
use App\Services\AuthService;

// Create 4 users with different roles
$users = [
    'admin' => ['mobile' => '1000000001', 'role' => UserRole::ADMIN],
    'partner' => ['mobile' => '1000000002', 'role' => UserRole::PARTNER],
    'worker' => ['mobile' => '1000000003', 'role' => UserRole::WORKER],
    'driver' => ['mobile' => '1000000004', 'role' => UserRole::PICKUP_DRIVER],
];

$authService = app(AuthService::class);
$tokens = [];

foreach ($users as $key => $data) {
    $user = User::firstOrCreate(
        ['mobile_number' => $data['mobile']],
        ['name' => ucfirst($key)]
    );
    $user->forceFill(['role' => $data['role']])->save();
    
    // Generate a fixed OTP so node script knows it
    $user->update(['otp' => '123456', 'otp_expires_at' => now()->addMinutes(10)]);
}

echo "Test users setup successfully.\n";
