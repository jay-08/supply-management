@extends('layouts.app')
@section('title', $supplier->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Suppliers</a></li>
    <li class="breadcrumb-item active">{{ $supplier->name }}</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $supplier->name }}</h1>
        <p class="page-subtitle">{{ $supplier->code }} &mdash; Supplier profile</p>
    </div>
    {!! $supplier->status_badge !!}
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Contact Information</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['Contact Person', $supplier->contact_person ?? '—'],
                    ['Phone', $supplier->phone ?? '—'],
                    ['Email', $supplier->email ?? '—'],
                    ['Website', $supplier->website ?? '—'],
                    ['Address', $supplier->address ?? '—'],
                    ['City', $supplier->city ?? '—'],
                ] as [$label, $value])
                <div class="d-flex justify-content-between px-4 py-2" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $label }}</span>
                    <span style="font-size:13px;font-weight:500;text-align:right;max-width:200px">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        @if($supplier->notes)
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Notes</h6>
                <p class="mb-0" style="font-size:13px;color:var(--text-muted)">{{ $supplier->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        {{-- Items supplied --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-archive-fill text-primary me-2"></i>Items Supplied ({{ $supplier->inventoryItems->count() }})</h5>
            </div>
            <div class="card-body p-0">
                @if($supplier->inventoryItems->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Code</th><th>Item</th><th>Unit</th><th>Stock</th><th>Unit Cost</th></tr></thead>
                        <tbody>
                            @foreach($supplier->inventoryItems as $item)
                            <tr>
                                <td style="font-size:12px">{{ $item->item_code }}</td>
                                <td><a href="{{ route('inventory.show', $item->id) }}" class="text-decoration-none fw-semibold" style="font-size:13px">{{ $item->name }}</a></td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $item->unit }}</td>
                                <td class="{{ $item->isLowStock() ? 'text-danger' : 'text-success' }} fw-bold">{{ $item->quantity }}</td>
                                <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state py-4"><p class="mb-0 text-muted">No items linked to this supplier.</p></div>
                @endif
            </div>
        </div>

        {{-- Purchase History --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-receipt text-success me-2"></i>Purchase History</h5>
            </div>
            <div class="card-body p-0">
                @if($purchases->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>PO #</th><th>Item</th><th>Qty</th><th>Total</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($purchases as $p)
                            <tr>
                                <td class="fw-semibold" style="font-size:12px">{{ $p->po_number }}</td>
                                <td style="font-size:13px">{{ $p->inventoryItem?->name }}</td>
                                <td>{{ $p->quantity }}</td>
                                <td>₱{{ number_format($p->total_cost, 2) }}</td>
                                <td style="font-size:11px;color:var(--text-muted)">{{ $p->purchase_date?->format('M d, Y') }}</td>
                                <td><span class="badge {{ $p->status === 'received' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($p->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $purchases->links() }}</div>
                @else
                <div class="empty-state py-4"><p class="mb-0 text-muted">No purchase records yet.</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
