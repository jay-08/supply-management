<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{{ $purchaseOrder->po_number }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#111;padding:15px}
.hdr{border-bottom:2px solid #1e40af;padding-bottom:10px;margin-bottom:14px;display:flex;justify-content:space-between}
.hdr h1{font-size:14px;color:#1e40af;font-weight:800}.doc-title{text-align:center;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin:10px 0 14px}
.meta{width:100%;margin-bottom:12px}.meta td{padding:3px 6px;font-size:10px}
table.items{width:100%;border-collapse:collapse;margin-bottom:10px}
table.items th{background:#1e40af;color:#fff;padding:5px 6px;font-size:9px;text-transform:uppercase}
table.items td{padding:5px 6px;border-bottom:1px solid #e5e7eb}
.totals{width:220px;margin-left:auto}.totals td{padding:3px 6px}
.grand td{font-weight:800;font-size:12px;color:#1e40af;border-top:1px solid #1e40af;padding-top:4px}
.sigs{display:flex;gap:20px;margin-top:30px}.sig{flex:1;text-align:center}
.sig div:first-child{border-top:1px solid #333;padding-top:4px;font-weight:700;font-size:10px;margin-top:30px}
.sig div:last-child{font-size:9px;color:#666}
</style>
</head>
<body>
<div class="hdr"><h1>Supply Management System</h1><div><strong>{{ $purchaseOrder->po_number }}</strong> · {{ $purchaseOrder->po_date?->format('M d, Y') }}</div></div>
<div class="doc-title">Purchase Order</div>
<table class="meta">
    <tr><td><strong>Supplier:</strong> {{ $purchaseOrder->supplier?->name }}</td><td><strong>PO Date:</strong> {{ $purchaseOrder->po_date?->format('M d, Y') }}</td><td><strong>Delivery Date:</strong> {{ $purchaseOrder->delivery_date?->format('M d, Y') ?? 'ASAP' }}</td></tr>
    <tr><td><strong>Contact:</strong> {{ $purchaseOrder->supplier?->contact_person ?? '—' }}</td><td><strong>Payment Terms:</strong> {{ $purchaseOrder->payment_terms ?? 'COD' }}</td><td><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$purchaseOrder->status)) }}</td></tr>
</table>
<table class="items">
    <thead><tr><th>#</th><th>Item Description</th><th>Unit</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Amount</th></tr></thead>
    <tbody>
        @foreach($purchaseOrder->items as $i => $item)
        <tr><td>{{ $i+1 }}</td><td><strong>{{ $item->item_name }}</strong>@if($item->specifications)<br><span style="font-size:8px;color:#666">{{ $item->specifications }}</span>@endif</td>
        <td>{{ $item->unit }}</td><td style="text-align:right">{{ $item->quantity_ordered }}</td>
        <td style="text-align:right">&#8369;{{ number_format($item->unit_price,2) }}</td>
        <td style="text-align:right;font-weight:700">&#8369;{{ number_format($item->total_price,2) }}</td></tr>
        @endforeach
    </tbody>
</table>
<table class="totals">
    <tr><td>Subtotal:</td><td style="text-align:right">&#8369;{{ number_format($purchaseOrder->subtotal,2) }}</td></tr>
    <tr><td>Tax ({{ $purchaseOrder->tax_rate }}%):</td><td style="text-align:right">&#8369;{{ number_format($purchaseOrder->tax_amount,2) }}</td></tr>
    <tr class="grand"><td>TOTAL:</td><td style="text-align:right">&#8369;{{ number_format($purchaseOrder->total_amount,2) }}</td></tr>
</table>
@if($purchaseOrder->notes)<p style="font-size:10px;margin-top:10px"><strong>Notes:</strong> {{ $purchaseOrder->notes }}</p>@endif
<div class="sigs">
    <div class="sig"><div>{{ $purchaseOrder->creator?->name }}</div><div>Prepared by</div></div>
    <div class="sig"><div>{{ $purchaseOrder->approvals->where('level', 'budget_officer')->first()?->approver?->name ?? '&nbsp;' }}</div><div>Budget Officer</div></div>
    <div class="sig"><div>{{ $purchaseOrder->approvals->where('level', 'accounting')->first()?->approver?->name ?? '&nbsp;' }}</div><div>Accounting</div></div>
    <div class="sig"><div>{{ $purchaseOrder->approvals->where('level', 'regional_director')->first()?->approver?->name ?? '&nbsp;' }}</div><div>Regional Director</div></div>
    <div class="sig"><div>&nbsp;</div><div>Supplier</div></div>
</div>
</body></html>
