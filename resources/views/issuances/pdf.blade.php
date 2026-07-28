<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $issuance->issuance_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; background: #fff; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563EB; padding-bottom: 10px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; color: #2563EB; }
        .doc-title { text-align: center; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin: 10px 0; }
        .meta { width: 100%; margin-bottom: 12px; }
        .meta td { padding: 3px 8px; font-size: 11px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.items th { background: #2563EB; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .total-row td { background: #f0f7ff; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header"><h1>Supply Management System</h1><p>Supply Issuance Receipt — {{ $issuance->issuance_number }}</p></div>
    <div class="doc-title">Issuance Receipt</div>
    <table class="meta">
        <tr>
            <td><strong>Issuance No:</strong> {{ $issuance->issuance_number }}</td>
            <td><strong>Date:</strong> {{ $issuance->issued_at?->format('M d, Y') }}</td>
            <td><strong>Issued To:</strong> {{ $issuance->recipient?->name }}</td>
            <td><strong>Department:</strong> {{ $issuance->department?->name }}</td>
        </tr>
        <tr>
            <td><strong>Issued By:</strong> {{ $issuance->issuer?->name }}</td>
            <td><strong>Total Value:</strong> &#8369;{{ number_format($issuance->total_value, 2) }}</td>
            <td colspan="2"><strong>Remarks:</strong> {{ $issuance->remarks ?? '—' }}</td>
        </tr>
    </table>
    <table class="items">
        <thead><tr><th>#</th><th>Item</th><th>Code</th><th>Category</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach($issuance->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->inventoryItem?->name }}</td>
                <td>{{ $item->inventoryItem?->item_code }}</td>
                <td>{{ $item->inventoryItem?->category?->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->inventoryItem?->unit }}</td>
                <td>&#8369;{{ number_format($item->unit_cost, 2) }}</td>
                <td>&#8369;{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot><tr class="total-row"><td colspan="7" style="text-align:right"><strong>TOTAL:</strong></td><td><strong>&#8369;{{ number_format($issuance->total_value, 2) }}</strong></td></tr></tfoot>
    </table>
</body>
</html>
