<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;padding:15px}h1{font-size:13px;text-align:center}table{width:100%;border-collapse:collapse}th{background:#2563EB;color:#fff;padding:5px}td{padding:5px;border-bottom:1px solid #eee}</style>
</head><body><h1>{{ $title }}</h1>
<table><thead><tr><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th>Date</th></tr></thead>
<tbody>@foreach($logs as $log)<tr><td>{{ $log->user?->name ?? 'System' }}</td><td>{{ $log->action }}</td><td>{{ $log->module }}</td><td>{{ $log->description }}</td><td>{{ $log->ip_address }}</td><td>{{ $log->created_at->format('M d Y H:i') }}</td></tr>@endforeach</tbody>
</table></body></html>
