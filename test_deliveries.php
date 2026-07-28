<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deliveries = App\Models\Delivery::with('items')->get();
if ($deliveries->isEmpty()) {
    echo "No deliveries found.\n";
}
foreach($deliveries as $d) {
    echo "Delivery ID: {$d->id}, GRN: {$d->grn_number}, PO ID: {$d->purchase_order_id}, Status: {$d->status}\n";
    foreach($d->items as $i) {
        echo "  - Item ID: {$i->purchase_order_item_id}, Delivered: {$i->quantity_delivered}, Accepted: {$i->quantity_accepted}\n";
    }
}
