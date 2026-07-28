<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issuance Receipt — {{ $issuance->issuance_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #2563EB; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; color: #2563EB; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #666; }
        .doc-title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin: 12px 0; }
        .meta { display: flex; gap: 20px; margin-bottom: 16px; }
        .meta-box { flex: 1; border: 1px solid #ddd; border-radius: 6px; padding: 10px; }
        .meta-box dt { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: .06em; }
        .meta-box dd { font-size: 13px; font-weight: 600; margin: 2px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #2563EB; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
        td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        .total-row td { background: #f0f7ff; font-weight: 700; font-size: 13px; }
        .signatures { display: flex; gap: 30px; margin-top: 30px; }
        .sig { flex: 1; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin-bottom: 6px; padding-top: 6px; font-size: 12px; font-weight: 600; }
        .sig-label { font-size: 10px; color: #666; }
        @media print { body { padding: 10px; } @page { margin: 10mm; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>&#128230; Supply Management System</h1>
        <p>Office Supply Issuance Receipt</p>
    </div>
    <div class="doc-title">Supply Issuance Receipt</div>

    <div class="meta">
        <div class="meta-box"><dl><dt>Issuance No.</dt><dd>{{ $issuance->issuance_number }}</dd></dl></div>
        <div class="meta-box"><dl><dt>Date Issued</dt><dd>{{ $issuance->issued_at?->format('F d, Y') }}</dd></dl></div>
        <div class="meta-box"><dl><dt>Issued To</dt><dd>{{ $issuance->recipient?->name }}</dd></dl></div>
        <div class="meta-box"><dl><dt>Department</dt><dd>{{ $issuance->department?->name ?? 'N/A' }}</dd></dl></div>
    </div>
    @if($issuance->supplyRequest)
    <p style="margin-bottom:12px;font-size:12px"><strong>Related Request:</strong> {{ $issuance->supplyRequest->request_number }} &mdash; {{ $issuance->supplyRequest->purpose }}</p>
    @endif

    <table>
        <thead><tr><th>#</th><th>Item</th><th>Code</th><th>Category</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th style="text-align:right">Subtotal</th></tr></thead>
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
                <td style="text-align:right">&#8369;{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row"><td colspan="7" style="text-align:right">TOTAL VALUE:</td><td style="text-align:right">&#8369;{{ number_format($issuance->total_value, 2) }}</td></tr>
        </tfoot>
    </table>

    @if($issuance->remarks)
    <p style="font-size:12px;margin-bottom:16px"><strong>Remarks:</strong> {{ $issuance->remarks }}</p>
    @endif

    <div class="signatures">
        <div class="sig"><div class="sig-line">{{ $issuance->recipient?->name }}</div><div class="sig-label">Received by</div></div>
        <div class="sig"><div class="sig-line">{{ $issuance->issuer?->name }}</div><div class="sig-label">Issued by</div></div>
        <div class="sig"><div class="sig-line">&nbsp;</div><div class="sig-label">Noted by (Department Head)</div></div>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
