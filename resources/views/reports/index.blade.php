@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Reports</h1>
    <p class="page-subtitle">Generate and export inventory and supply chain reports.</p>
</div>
<div class="row g-4">
    @foreach([
        ['Inventory Report', 'Current stock levels for all items', 'bi-archive-fill', 'primary', route('reports.inventory'), route('reports.export.pdf','inventory')],
        ['Low Stock Report', 'Items below reorder level', 'bi-exclamation-triangle-fill', 'danger', route('reports.low-stock'), route('reports.export.pdf','low_stock')],
        ['Monthly Issuance', 'Issuances summary by month', 'bi-box-arrow-right', 'success', route('reports.issuance'), route('reports.export.pdf','issuance')],
        ['Request History', 'All supply requests and statuses', 'bi-cart-plus-fill', 'warning', route('reports.requests'), route('reports.export.pdf','requests')],
        ['Activity Log', 'User actions and audit trail', 'bi-activity', 'info', route('reports.activity'), route('reports.export.pdf','activity')],
    ] as [$title, $desc, $icon, $color, $viewRoute, $pdfRoute])
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon {{ $color }}" style="flex-shrink:0"><i class="bi {{ $icon }}"></i></div>
                    <div>
                        <h5 class="mb-1" style="font-size:15px;font-weight:700">{{ $title }}</h5>
                        <p class="mb-0 text-muted" style="font-size:12px">{{ $desc }}</p>
                    </div>
                </div>
                <div class="mt-auto d-flex gap-2">
                    <a href="{{ $viewRoute }}" class="btn btn-outline-{{ $color }} btn-sm flex-grow-1"><i class="bi bi-eye"></i> Preview</a>
                    <a href="{{ $pdfRoute }}" class="btn btn-{{ $color }} btn-sm"><i class="bi bi-file-pdf"></i> PDF</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
