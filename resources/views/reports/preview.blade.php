@extends('layouts.app')
@section('title', $title)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">Generated on {{ now()->format('F d, Y H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.pdf', $type) }}" class="btn btn-danger"><i class="bi bi-file-pdf"></i> Export PDF</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        @if($type === 'inventory' || $type === 'low_stock')
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Code</th><th>Item</th><th>Category</th><th>Qty</th><th>Unit</th><th>Reorder Level</th><th>Unit Cost</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->item_code }}</td>
                        <td class="fw-semibold">{{ $item->name }}</td>
                        <td>{{ $item->category?->name }}</td>
                        <td class="{{ $item->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ $item->reorder_level }}</td>
                        <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{!! $item->status_badge !!}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @elseif($type === 'issuance')
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Issuance #</th><th>Recipient</th><th>Department</th><th>Items</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($issuances as $iss)
                    <tr>
                        <td>{{ $iss->issuance_number }}</td>
                        <td>{{ $iss->recipient?->name }}</td>
                        <td>{{ $iss->department?->name }}</td>
                        <td>{{ $iss->items->count() }}</td>
                        <td>₱{{ number_format($iss->total_value, 2) }}</td>
                        <td>{{ $iss->issued_at?->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No issuances found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @elseif($type === 'requests')
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Request #</th><th>Requester</th><th>Department</th><th>Items</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->request_number }}</td>
                        <td>{{ $req->requester_name }}</td>
                        <td>{{ $req->department?->name }}</td>
                        <td>{{ $req->items->count() }}</td>
                        <td>{!! $req->status_badge !!}</td>
                        <td>{{ $req->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @elseif($type === 'activity')
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $log->action }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $log->module }}</span></td>
                        <td style="font-size:12px">{{ $log->description }}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $log->ip_address }}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
