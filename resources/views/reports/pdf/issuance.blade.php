<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;padding:15px}h1{font-size:13px;color:#2563EB;text-align:center}table{width:100%;border-collapse:collapse}th{background:#2563EB;color:#fff;padding:5px}td{padding:5px;border-bottom:1px solid #eee}</style>
</head><body><h1>{{ $title }}</h1>
<table><thead><tr><th>Issuance #</th><th>Recipient</th><th>Department</th><th>Total Value</th><th>Date</th></tr></thead>
<tbody>@foreach($issuances as $iss)<tr><td>{{ $iss->issuance_number }}</td><td>{{ $iss->recipient?->name }}</td><td>{{ $iss->department?->name }}</td><td>&#8369;{{ number_format($iss->total_value,2) }}</td><td>{{ $iss->issued_at?->format('M d Y') }}</td></tr>@endforeach</tbody>
</table></body></html>
