@extends('layouts.app')
@section('title', 'Procurement Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Procurement Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Procurement Dashboard</h1>
    <p class="page-subtitle">Overview of purchase requests, orders, and supplier performance.</p>
</div>

{{-- KPI Cards --}}
<div class="row g-4 mb-4">
    @foreach([
        ['Active POs',    $activePO,    'bi-cart-fill',               'primary',  route('procurement.purchase-orders.index')],
        ['Delivered POs', $deliveredPO, 'bi-truck',                   'info',     route('procurement.purchase-orders.index', ['status'=>'delivered'])],
        ['Cancelled POs', $cancelledPO, 'bi-x-circle-fill',           'danger',   route('procurement.purchase-orders.index', ['status'=>'cancelled'])],
        ['Overdue POs',   $overduePOs,  'bi-exclamation-triangle-fill','danger',  route('procurement.purchase-orders.index')],
    ] as [$label, $val, $icon, $color, $link])
    <div class="col-6 col-lg-2">
        <a href="{{ $link }}" class="text-decoration-none">
            <div class="card text-center hover-lift" style="transition:.2s">
                <div class="card-body py-3">
                    <div class="stat-icon {{ $color }} mx-auto mb-2" style="width:44px;height:44px;font-size:20px"><i class="bi {{ $icon }}"></i></div>
                    <div style="font-size:24px;font-weight:800;color:var(--{{ $color === 'warning' ? 'warning' : ($color === 'success' ? 'success' : ($color === 'primary' ? 'primary' : ($color === 'info' ? 'info' : 'danger'))) }})">{{ $val }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $label }}</div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon success" style="width:52px;height:52px;font-size:24px;flex-shrink:0"><i class="bi bi-currency-dollar"></i></div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em">Total Spend ({{ now()->year }})</div>
                    <div style="font-size:28px;font-weight:800;color:var(--success)">₱{{ number_format($yearTotal, 0) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Monthly Spending Chart --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Procurement Spending</h5>
                <small class="text-muted">Last 6 months</small>
            </div>
            <div class="card-body"><canvas id="monthlyChart" height="220"></canvas></div>
        </div>
    </div>
    {{-- Top Suppliers --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Suppliers by Spend</h5></div>
            <div class="card-body p-0">
                @forelse($topSuppliers as $i => $sup)
                <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--border)">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:var(--primary)">{{ $i+1 }}</div>
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600">{{ $sup->supplier?->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $sup->po_count }} orders</div>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:var(--success)">₱{{ number_format($sup->total_spend, 0) }}</div>
                </div>
                @empty
                <div class="empty-state py-3"><p>No POs yet.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent PRs --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title"><i class="bi bi-info-circle-fill me-2 text-info"></i>Procurement Info</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Purchase Orders manage the formal request for goods from external suppliers. Create POs manually, send them, and record deliveries via GRNs to auto-update inventory.</p>
            </div>
        </div>
    </div>
    {{-- Recent POs --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title"><i class="bi bi-receipt me-2 text-success"></i>Recent Purchase Orders</h5>
                <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-sm btn-outline-success" style="border-radius:20px;font-size:12px">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentPOs as $po)
                <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid var(--border)">
                    <div class="flex-grow-1">
                        <a href="{{ route('procurement.purchase-orders.show', $po->id) }}" class="fw-semibold text-decoration-none" style="font-size:13px;color:var(--success)">{{ $po->po_number }}</a>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $po->supplier?->name }}</div>
                    </div>
                    <div class="text-end">
                        {!! $po->status_badge !!}
                        <div style="font-size:12px;font-weight:600;color:var(--success)">₱{{ number_format($po->total_amount, 0) }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-state py-3"><p>No purchase orders.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
const labels  = @json(array_column($monthlySeries, 'month'));
const amounts = @json(array_column($monthlySeries, 'total'));

new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Procurement Spend (₱)',
            data: amounts,
            backgroundColor: 'rgba(37,99,235,0.8)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
@endpush
