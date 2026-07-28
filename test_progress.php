<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pos = App\Models\PurchaseOrder::with('items')->get();
foreach($pos as $po) {
    echo "PO ID: {$po->id}, Ordered: {$po->items()->sum('quantity_ordered')}, Received: {$po->items()->sum('quantity_received')}, Progress: {$po->delivery_progress}%\n";
}
