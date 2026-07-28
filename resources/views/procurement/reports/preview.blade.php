@extends('layouts.app')
@section('title', $title)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.reports.index') }}" class="text-decoration-none">Procurement Reports</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <h1 class="page-title">{{ $title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('procurement.reports.export-pdf', $type ?? 'pr') . '?' . http_build_query(request()->query()) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Export PDF</a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

{{-- Filter Form --}}
<div class="card mb-4 no-print">
    <form method="GET" class="card-body py-3">
        <div class="row g-2">

            <div class="col-md-3"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From Date"></div>
            <div class="col-md-3"><input type="date" name="to"   class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To Date"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button></div>
        </div>
    </form>
</div>



{{-- PO Report --}}
@if(isset($pos))
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">Purchase Orders ({{ $pos->count() }})</h5>
        <span class="badge bg-success">Total Spend: ₱{{ number_format($pos->whereNotIn('status',['cancelled','draft'])->sum('total_amount'), 2) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>PO Number</th><th>Supplier</th><th>PO Date</th><th>Delivery Date</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($pos as $po)
                <tr>
                    <td class="fw-bold">{{ $po->po_number }}</td>
                    <td>{{ $po->supplier?->name }}</td>
                    <td style="font-size:12px">{{ $po->po_date?->format('M d, Y') }}</td>
                    <td style="font-size:12px">{{ $po->delivery_date?->format('M d, Y') ?? '—' }}</td>
                    <td class="fw-semibold">₱{{ number_format($po->total_amount, 2) }}</td>
                    <td>{!! $po->status_badge !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Deliveries Report --}}
@if(isset($deliveries))
<div class="card">
    <div class="card-header"><h5 class="card-title">Deliveries / GRN ({{ $deliveries->count() }})</h5></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>GRN Number</th><th>PO Reference</th><th>Supplier</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($deliveries as $del)
                <tr>
                    <td class="fw-bold">{{ $del->grn_number }}</td>
                    <td>{{ $del->purchaseOrder?->po_number }}</td>
                    <td>{{ $del->purchaseOrder?->supplier?->name }}</td>
                    <td style="font-size:12px">{{ $del->delivery_date?->format('M d, Y') }}</td>
                    <td>{!! $del->status_badge !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Supplier Performance --}}
@if(isset($suppliers))
<div class="card">
    <div class="card-header"><h5 class="card-title">Supplier Performance ({{ $suppliers->count() }})</h5></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Supplier</th><th>Total POs</th><th>On-Time</th><th>Total Spend</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($suppliers->sortByDesc('total_spend') as $sup)
                <tr>
                    <td class="fw-semibold">{{ $sup->name }}</td>
                    <td>{{ $sup->po_count }}</td>
                    <td class="text-success fw-bold">{{ $sup->on_time }}</td>
                    <td class="fw-semibold">₱{{ number_format($sup->total_spend, 2) }}</td>
                    <td>{!! $sup->status_badge !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
