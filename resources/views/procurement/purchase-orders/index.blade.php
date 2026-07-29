@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Procurement</a></li>
    <li class="breadcrumb-item active">Purchase Orders</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Purchase Orders</h1>
        <p class="page-subtitle">Track and manage all purchase orders.</p>
    </div>
    @role('admin|supply-officer')
    <a href="{{ route('procurement.purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New PO</a>
    @endrole
</div>

<div class="card mb-4">
    <form method="GET" class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['draft','pending','sent','partially_delivered','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id')==$sup->id?'selected':'' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From"></div>
            <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button></div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($pos->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>PO Number</th><th>Supplier</th><th>PO Date</th><th>Delivery Date</th><th>Total</th><th>Status</th><th>Progress</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($pos as $po)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.purchase-orders.show', $po->id) }}" class="fw-bold text-decoration-none" style="color:var(--success)">{{ $po->po_number }}</a>
                            @if($po->attachment)
                                <a href="{{ $po->attachment_url }}" target="_blank" title="View Attachment" class="text-primary ms-1"><i class="bi bi-paperclip"></i></a>
                            @endif
                        </td>
                        <td style="font-size:13px">{{ $po->supplier?->name }}</td>
                        <td style="font-size:12px">{{ $po->po_date?->format('M d, Y') }}</td>
                        <td style="font-size:12px;color:{{ $po->delivery_date && $po->delivery_date->isPast() && !in_array($po->status,['delivered','cancelled']) ? 'var(--danger)' : 'var(--text-muted)' }}">
                            {{ $po->delivery_date?->format('M d, Y') ?? '—' }}
                        </td>
                        <td class="fw-semibold">₱{{ number_format($po->total_amount, 2) }}</td>
                        <td>{!! $po->status_badge !!}</td>
                        <td style="min-width:100px">
                            <div class="progress" style="height:6px;border-radius:10px">
                                <div class="progress-bar bg-success" style="width:{{ $po->workflow_progress }}%"></div>
                            </div>
                            <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $po->workflow_progress }}%</div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('procurement.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('procurement.purchase-orders.print', $po->id) }}" target="_blank" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-printer"></i></a>
                            <a href="{{ route('procurement.purchase-orders.pdf', $po->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-file-pdf text-danger"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $pos->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-receipt"></i><h5>No purchase orders found</h5></div>
        @endif
    </div>
</div>
@endsection
