@extends('layouts.app')
@section('title', 'Returns')
@section('breadcrumb')
    <li class="breadcrumb-item active">Returns</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Returns</h1>
        <p class="page-subtitle">Manage returned supply items.</p>
    </div>
    <a href="{{ route('returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Process Return</a>
</div>
<div class="card">
    <div class="card-body p-0">
        @if($returns->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Return #</th><th>Item</th><th>Issuance</th><th>Qty</th><th>Condition</th><th>Returned By</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($returns as $ret)
                    <tr>
                        <td class="fw-semibold" style="color:var(--primary)">{{ $ret->return_number }}</td>
                        <td style="font-size:13px">{{ $ret->inventoryItem?->name }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $ret->issuance?->issuance_number }}</td>
                        <td class="fw-bold">{{ $ret->quantity }}</td>
                        <td>
                            <span class="badge {{ match($ret->condition) { 'good' => 'bg-success', 'damaged' => 'bg-warning text-dark', 'defective' => 'bg-danger', default => 'bg-secondary' } }}">
                                {{ ucfirst($ret->condition) }}
                            </span>
                        </td>
                        <td style="font-size:12px">{{ $ret->returnedBy?->name }}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $ret->returned_at?->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $returns->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-box-arrow-in-left"></i><h5>No returns recorded</h5></div>
        @endif
    </div>
</div>
@endsection
