<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;padding:15px}h1{font-size:13px;text-align:center}table{width:100%;border-collapse:collapse}th{background:#2563EB;color:#fff;padding:5px}td{padding:5px;border-bottom:1px solid #eee}</style>
</head><body><h1>{{ $title }}</h1>
<table><thead><tr><th>Request #</th><th>Requester</th><th>Department</th><th>Items</th><th>Status</th><th>Date</th></tr></thead>
<tbody>@foreach($requests as $req)<tr><td>{{ $req->request_number }}</td><td>{{ $req->requester_name }}</td><td>{{ $req->department?->name }}</td><td>{{ $req->items->count() }}</td><td>{{ $req->status }}</td><td>{{ $req->created_at->format('M d Y') }}</td></tr>@endforeach</tbody>
</table></body></html>
