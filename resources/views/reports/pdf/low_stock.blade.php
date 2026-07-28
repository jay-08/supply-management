<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#111;padding:15px}h1{font-size:14px;color:#EF4444;text-align:center;margin-bottom:10px}table{width:100%;border-collapse:collapse}th{background:#EF4444;color:#fff;padding:5px 7px;font-size:9px;text-align:left}td{padding:5px 7px;border-bottom:1px solid #eee;color:#EF4444;font-weight:bold}</style>
</head><body>
<h1>{{ $title }}</h1>
<p style="text-align:center;font-size:9px;color:#999">Generated: {{ now()->format('F d, Y H:i') }}</p>
<table><thead><tr><th>Code</th><th>Item</th><th>Category</th><th>Current Qty</th><th>Reorder Level</th><th>Deficit</th></tr></thead>
<tbody>
@foreach($items as $item)
<tr><td>{{ $item->item_code }}</td><td>{{ $item->name }}</td><td>{{ $item->category?->name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->reorder_level }}</td><td>{{ $item->reorder_level - $item->quantity }}</td></tr>
@endforeach
</tbody></table>
</body></html>
