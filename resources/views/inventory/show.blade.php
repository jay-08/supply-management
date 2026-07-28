@extends('layouts.app')
@section('title', $inventory->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
    <li class="breadcrumb-item active">{{ $inventory->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center p-4">
                <img src="{{ $inventory->image_url }}" alt="{{ $inventory->name }}"
                     style="width:120px;height:120px;border-radius:16px;object-fit:cover;border:3px solid var(--border);margin-bottom:16px">
                <h4 class="fw-bold mb-1">{{ $inventory->name }}</h4>
                <div class="text-muted mb-2" style="font-size:13px">{{ $inventory->item_code }}</div>
                {!! $inventory->status_badge !!}
                {!! $inventory->stock_badge !!}
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Item Details</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['Category', $inventory->category?->name],
                    ['Supplier', $inventory->supplier?->name ?? 'N/A'],
                    ['Unit', $inventory->unit],
                    ['Current Stock', $inventory->quantity],
                    ['Reorder Level', $inventory->reorder_level],
                    ['Unit Cost', '₱' . number_format($inventory->unit_cost, 2)],
                    ['Location', $inventory->location ?? 'N/A'],
                ] as [$label, $value])
                <div class="d-flex justify-content-between align-items-center px-4 py-2" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $label }}</span>
                    <span style="font-size:13px;font-weight:600">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        @role('admin|supply-officer')
        <div class="card">
            <div class="card-header"><h5 class="card-title">Quick Adjust Stock</h5></div>
            <div class="card-body">
                <form action="{{ route('inventory.adjust', $inventory->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="type" class="form-select">
                            <option value="stock_in">Stock In (+)</option>
                            <option value="adjustment">Adjustment (−)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Reason for adjustment">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat"></i> Apply Adjustment</button>
                </form>
            </div>
        </div>
        @endrole
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-clock-history text-primary me-2"></i>Inventory History</h5>
            </div>
            <div class="card-body p-0">
                @if($history->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Type</th><th>Quantity</th><th>Before</th><th>After</th><th>Notes</th><th>By</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($history as $h)
                            <tr>
                                <td>{!! $h->type_badge !!}</td>
                                <td>
                                    <span class="{{ $h->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        {{ $h->quantity >= 0 ? '+' : '' }}{{ $h->quantity }}
                                    </span>
                                </td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $h->quantity_before }}</td>
                                <td style="font-size:12px;font-weight:600">{{ $h->quantity_after }}</td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $h->notes ?? '—' }}</td>
                                <td style="font-size:12px">{{ $h->user?->name ?? 'System' }}</td>
                                <td style="font-size:11px;color:var(--text-muted)">{{ $h->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $history->links() }}</div>
                @else
                <div class="empty-state"><i class="bi bi-clock-history"></i><p>No history yet.</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
