<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

$role = Role::firstOrCreate(['name' => 'client']);

$user = User::firstOrCreate(
    ['email' => 'client@example.com'],
    [
        'name' => 'Client User',
        'password' => Hash::make('password'),
    ]
);

$user->assignRole($role);

echo "Client role and user created.\n";
