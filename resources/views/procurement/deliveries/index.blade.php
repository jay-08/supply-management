@extends('layouts.app')
@section('title', 'Deliveries / GRN')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Procurement</a></li>
    <li class="breadcrumb-item active">Deliveries</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">Deliveries / GRN</h1><p class="page-subtitle">Goods Received Notes and delivery inspection records.</p></div>
    <a href="{{ route('procurement.deliveries.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Receive Delivery</a>
</div>
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach([''=> 'All', 'pending'=>'Pending', 'partial'=>'Partial', 'complete'=>'Complete', 'rejected'=>'Rejected'] as $val=>$label)
    <a href="{{ route('procurement.deliveries.index', array_merge(request()->query(), ['status'=>$val])) }}"
       class="btn btn-sm {{ request('status')===$val ? 'btn-primary' : 'btn-outline-secondary' }}" style="border-radius:20px">{{ $label }}</a>
    @endforeach
</div>
<div class="card">
    <div class="card-body p-0">
        @if($deliveries->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>GRN Number</th><th>PO Reference</th><th>Supplier</th><th>DR Number</th><th>Delivery Date</th><th>Status</th><th>Inv. Updated</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($deliveries as $del)
                    <tr>
                        <td class="fw-bold" style="color:var(--primary)">{{ $del->grn_number }}</td>
                        <td><a href="{{ route('procurement.purchase-orders.show', $del->purchaseOrder?->id) }}" class="text-decoration-none" style="font-size:12px;color:var(--success)">{{ $del->purchaseOrder?->po_number }}</a></td>
                        <td style="font-size:13px">{{ $del->purchaseOrder?->supplier?->name }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $del->dr_number ?? '—' }}</td>
                        <td style="font-size:12px">{{ $del->delivery_date?->format('M d, Y') }}</td>
                        <td>{!! $del->status_badge !!}</td>
                        <td>@if($del->inventory_updated)<span class="badge bg-success"><i class="bi bi-check-lg"></i> Updated</span>@else<span class="badge bg-secondary">Pending</span>@endif</td>
                        <td class="text-end">
                            <a href="{{ route('procurement.deliveries.show', $del->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $deliveries->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-truck"></i><h5>No delivery records found</h5></div>
        @endif
    </div>
</div>
@endsection
