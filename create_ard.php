<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

$role = Role::firstOrCreate(['name' => 'assistant-regional-director']);

$user = User::firstOrCreate(
    ['email' => 'ard@example.com'],
    [
        'name' => 'Assistant Regional Director',
        'password' => Hash::make('password'),
    ]
);

$user->assignRole($role);

echo "ARD role and user created.\n";
