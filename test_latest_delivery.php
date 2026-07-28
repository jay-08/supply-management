<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$delivery = App\Models\Delivery::with('items')->latest('id')->first();
if ($delivery) {
    echo "Delivery ID: {$delivery->id}\n";
    echo "PO ID: {$delivery->purchase_order_id}\n";
    foreach($delivery->items as $i) {
        echo "Item: {$i->purchase_order_item_id}, Delivered: {$i->quantity_delivered}, Accepted: {$i->quantity_accepted}\n";
    }
    
    $po = App\Models\PurchaseOrder::find($delivery->purchase_order_id);
    echo "PO Progress: {$po->delivery_progress}%\n";
} else {
    echo "No deliveries.\n";
}
