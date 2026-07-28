@extends('layouts.app')
@section('title', 'Procurement Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Procurement</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Procurement Reports</h1>
    <p class="page-subtitle">Generate and export procurement reports.</p>
</div>

<div class="row g-4">
    @foreach([
        ['Purchase Order Report','Track POs by supplier, status, and spending.','bi-receipt','success',route('procurement.reports.purchase-orders')],
        ['Delivery Report','GRN history and delivery inspection results.','bi-truck','info',route('procurement.reports.deliveries')],
        ['Supplier Performance','Compare suppliers by spend, PO count, and on-time delivery.','bi-graph-up','warning',route('procurement.reports.supplier-performance')],
    ] as [$title, $desc, $icon, $color, $link])
    <div class="col-md-6 col-lg-3">
        <a href="{{ $link }}" class="text-decoration-none">
            <div class="card hover-lift text-center" style="transition:.2s;cursor:pointer">
                <div class="card-body py-4">
                    <div class="stat-icon {{ $color }} mx-auto mb-3" style="width:56px;height:56px;font-size:26px"><i class="bi {{ $icon }}"></i></div>
                    <h5 class="card-title" style="font-size:14px">{{ $title }}</h5>
                    <p class="text-muted" style="font-size:12px;margin:0">{{ $desc }}</p>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
