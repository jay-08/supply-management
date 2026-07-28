<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — {{ $purchaseOrder->po_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 30px; }
        .letterhead { display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 3px solid #1e40af; padding-bottom: 16px; margin-bottom: 20px; }
        .org-name { font-size: 20px; font-weight: 800; color: #1e40af; }
        .org-sub { font-size: 11px; color: #555; margin-top: 2px; }
        .doc-title { font-size: 22px; font-weight: 800; text-align: center; text-transform: uppercase; letter-spacing: 3px; color: #1e40af; margin: 16px 0 20px; }
        .po-meta { display: flex; gap: 20px; margin-bottom: 20px; }
        .meta-box { flex: 1; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px 14px; }
        .meta-label { font-size: 9px; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: 3px; }
        .meta-value { font-size: 13px; font-weight: 700; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #1e40af; color: #fff; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        tr:nth-child(even) td { background: #f8faff; }
        .total-section { margin-left: auto; width: 280px; margin-top: 8px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 12px; }
        .grand-total { display: flex; justify-content: space-between; padding: 8px 0; font-size: 16px; font-weight: 800; border-top: 2px solid #1e40af; color: #1e40af; margin-top: 4px; }
        .notes-box { border: 1px solid #d1d5db; border-radius: 6px; padding: 10px 14px; margin-bottom: 20px; }
        .signatures { display: flex; gap: 30px; margin-top: 40px; }
        .sig { flex: 1; text-align: center; }
        .sig-line { border-top: 1px solid #333; padding-top: 6px; font-weight: 700; font-size: 12px; margin-top: 40px; }
        .sig-label { font-size: 10px; color: #666; }
        .status-chip { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; }
        @media print {
            body { padding: 10px; }
            @page { margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;text-align:right">
        <button onclick="window.print()" style="padding:8px 20px;background:#1e40af;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px">🖨 Print / Save PDF</button>
    </div>

    <div class="letterhead">
        <div>
            <div class="org-name">&#128230; Supply Management System</div>
            <div class="org-sub">Procurement Division · Office Supply Management</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:11px;color:#555">Date Issued: {{ $purchaseOrder->po_date?->format('F d, Y') }}</div>
            <div style="font-size:11px;color:#555">Status: <span class="status-chip">{{ ucfirst(str_replace('_',' ',$purchaseOrder->status)) }}</span></div>
        </div>
    </div>

    <div class="doc-title">Purchase Order</div>

    <div class="po-meta">
        <div class="meta-box"><div class="meta-label">PO Number</div><div class="meta-value">{{ $purchaseOrder->po_number }}</div></div>
        <div class="meta-box"><div class="meta-label">PO Date</div><div class="meta-value">{{ $purchaseOrder->po_date?->format('M d, Y') }}</div></div>
        <div class="meta-box"><div class="meta-label">Delivery Date</div><div class="meta-value">{{ $purchaseOrder->delivery_date?->format('M d, Y') ?? 'ASAP' }}</div></div>
        <div class="meta-box"><div class="meta-label">Payment Terms</div><div class="meta-value">{{ $purchaseOrder->payment_terms ?? 'COD' }}</div></div>
    </div>

    <div class="po-meta">
        <div class="meta-box" style="flex:2">
            <div class="meta-label">Supplier</div>
            <div class="meta-value">{{ $purchaseOrder->supplier?->name }}</div>
            <div style="font-size:11px;color:#555;margin-top:3px">
                {{ $purchaseOrder->supplier?->contact_person ? 'Attn: ' . $purchaseOrder->supplier->contact_person : '' }}<br>
                {{ $purchaseOrder->supplier?->phone }} {{ $purchaseOrder->supplier?->email ? '· ' . $purchaseOrder->supplier->email : '' }}
            </div>
        </div>
        <div class="meta-box" style="flex:1">
            <div class="meta-label">Deliver To</div>
            <div style="font-size:12px">{{ $purchaseOrder->delivery_address ?? 'Office — Supply Room' }}</div>
        </div>
    </div>



    <table>
        <thead>
            <tr>
                <th style="width:32px">#</th>
                <th>Item Description</th>
                <th style="width:70px">Unit</th>
                <th style="width:70px;text-align:right">Qty</th>
                <th style="width:100px;text-align:right">Unit Price</th>
                <th style="width:110px;text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->specifications)<br><span style="font-size:10px;color:#666">{{ $item->specifications }}</span>@endif
                </td>
                <td>{{ $item->unit }}</td>
                <td style="text-align:right">{{ $item->quantity_ordered }}</td>
                <td style="text-align:right">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:right;font-weight:700">&#8369;{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display:flex;gap:20px">
        @if($purchaseOrder->notes)
        <div class="notes-box" style="flex:1">
            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Notes & Special Instructions</div>
            <p style="font-size:12px;margin:0">{{ $purchaseOrder->notes }}</p>
        </div>
        @else<div style="flex:1"></div>@endif
        <div class="total-section">
            <div class="total-row"><span>Subtotal:</span><span>&#8369;{{ number_format($purchaseOrder->subtotal, 2) }}</span></div>
            <div class="total-row"><span>Tax ({{ $purchaseOrder->tax_rate }}%):</span><span>&#8369;{{ number_format($purchaseOrder->tax_amount, 2) }}</span></div>
            <div class="grand-total"><span>TOTAL:</span><span>&#8369;{{ number_format($purchaseOrder->total_amount, 2) }}</span></div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig"><div class="sig-line">{{ $purchaseOrder->creator?->name }}</div><div class="sig-label">Prepared by (Supply Officer)</div></div>
        <div class="sig"><div class="sig-line">{{ $purchaseOrder->approvals->where('level', 'budget_officer')->first()?->approver?->name ?? '&nbsp;' }}</div><div class="sig-label">Budget Officer</div></div>
        <div class="sig"><div class="sig-line">{{ $purchaseOrder->approvals->where('level', 'accounting')->first()?->approver?->name ?? '&nbsp;' }}</div><div class="sig-label">Accounting</div></div>
        <div class="sig"><div class="sig-line">{{ $purchaseOrder->approvals->where('level', 'regional_director')->first()?->approver?->name ?? '&nbsp;' }}</div><div class="sig-label">Regional Director</div></div>
        <div class="sig"><div class="sig-line">&nbsp;</div><div class="sig-label">Received by (Supplier)</div></div>
    </div>

    <script>window.addEventListener('DOMContentLoaded', function() { /* auto-print disabled for web view */ });</script>
</body>
</html>
