@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}! Here's what's happening today.</p>
</div>

{{-- KPI STAT CARDS --}}
<div class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-archive-fill"></i></div>
            <div>
                <div class="stat-value text-primary">{{ number_format($totalItems) }}</div>
                <div class="stat-label">Total Inventory Items</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="stat-value text-danger">{{ number_format($lowStockItems) }}</div>
                <div class="stat-label">Low Stock Alerts</div>
                @if($lowStockItems > 0)
                    <div class="stat-change down"><i class="bi bi-arrow-up"></i> Needs attention</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value text-warning">{{ number_format($pendingRequests) }}</div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <div class="stat-value text-success">{{ number_format($issuedThisMonth) }}</div>
                <div class="stat-label">Issued This Month</div>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Issuances</h5>
                <small class="text-muted">Last 6 months</small>
            </div>
            <div class="card-body">
                <canvas id="issuanceChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Stock by Category</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="categoryChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- TABLES ROW --}}
<div class="row g-4">
    {{-- Low Stock --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Low Stock Items</h5>
                <a href="{{ route('inventory.index') }}?low_stock=1" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:12px">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($lowStockList->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Reorder</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockList as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:13px">{{ $item->name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $item->item_code }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $item->category?->name }}</span></td>
                                <td>
                                    <span class="fw-bold {{ $item->quantity == 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);font-size:12px">≥ {{ $item->reorder_level }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state py-4">
                    <i class="bi bi-check-circle-fill text-success" style="opacity:1"></i>
                    <p class="mb-0 mt-2">All items are well-stocked!</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cart-plus-fill text-warning me-2"></i>Pending Requests</h5>
                <a href="{{ route('requests.index') }}?status=pending" class="btn btn-sm btn-outline-warning" style="border-radius:8px;font-size:12px">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($pendingList->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Request #</th><th>Requester</th><th>Items</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($pendingList as $req)
                            <tr>
                                <td>
                                    <a href="{{ route('requests.show', $req->id) }}" class="fw-bold text-decoration-none" style="font-size:13px;color:var(--primary)">
                                        {{ $req->request_number }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-size:13px">{{ $req->requester_name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $req->department?->name }}</div>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $req->items->count() }} items</span></td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $req->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state py-4">
                    <i class="bi bi-inbox" style="font-size:36px;opacity:.3;display:block;margin-bottom:8px"></i>
                    <p class="mb-0">No pending requests</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-activity text-info me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                @if($recentActivity->count())
                <div class="timeline">
                    @foreach($recentActivity as $log)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ match($log->action) { 'login','created' => 'green', 'deleted','rejected' => 'red', default => '' } }}"></div>
                        <div class="d-flex align-items-start gap-2">
                            <div class="flex-grow-1">
                                <div style="font-size:13px;font-weight:500">{{ $log->description }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">
                                    {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <span class="badge bg-light text-dark" style="font-size:10px">{{ $log->module }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="bi bi-clock-history"></i>
                    <p>No recent activity</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartColors = ['#2563EB','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];

fetch('{{ route("dashboard.chart-data") }}')
    .then(r => r.json())
    .then(data => {
        // Monthly Issuance Bar Chart
        new Chart(document.getElementById('issuanceChart'), {
            type: 'bar',
            data: {
                labels: data.monthly.map(m => m.month),
                datasets: [{
                    label: 'Issuances',
                    data: data.monthly.map(m => m.count),
                    backgroundColor: 'rgba(37,99,235,.15)',
                    borderColor: '#2563EB',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Category Doughnut
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: data.categories.map(c => c.label),
                datasets: [{
                    data: data.categories.map(c => c.value),
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--card-bg') || '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { position: 'right', labels: { font: { size: 11 }, padding: 12 } } }
            }
        });
    });
</script>
@endpush
