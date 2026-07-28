@extends('layouts.app')
@section('title', $delivery->grn_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.deliveries.index') }}" class="text-decoration-none">Deliveries</a></li>
    <li class="breadcrumb-item active">{{ $delivery->grn_number }}</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $delivery->grn_number }}</h1>
        <p class="page-subtitle">{{ $delivery->purchaseOrder?->supplier?->name }} · Received {{ $delivery->delivery_date?->format('M d, Y') }}</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        {!! $delivery->status_badge !!}
        @if($delivery->inventory_updated)<span class="badge bg-success"><i class="bi bi-check-lg"></i> Inventory Updated</span>@endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Delivery Details</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['GRN Number', $delivery->grn_number],
                    ['PO Reference', $delivery->purchaseOrder?->po_number],
                    ['Supplier', $delivery->purchaseOrder?->supplier?->name],
                    ['DR Number', $delivery->dr_number ?? '—'],
                    ['Invoice Number', $delivery->invoice_number ?? '—'],
                    ['Delivery Date', $delivery->delivery_date?->format('M d, Y')],
                    ['Received By', $delivery->receiver?->name],
                    ['Inspected By', $delivery->inspector?->name ?? '—'],
                ] as [$l, $v])
                <div class="d-flex justify-content-between px-4 py-2" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $l }}</span>
                    <span style="font-size:13px;font-weight:500">{{ $v }}</span>
                </div>
                @endforeach
                @if($delivery->attachment)
                <div class="px-4 py-3 border-top">
                    <a href="{{ $delivery->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-paperclip me-1"></i> View Attachment
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Items Received</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Item</th><th>Delivered</th><th>Accepted</th><th>Rejected</th><th>Condition</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @foreach($delivery->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:13px">{{ $item->inventoryItem?->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $item->inventoryItem?->item_code }}</div>
                            </td>
                            <td class="fw-bold">{{ $item->quantity_delivered }}</td>
                            <td class="text-success fw-bold">{{ $item->quantity_accepted }}</td>
                            <td class="{{ $item->quantity_rejected > 0 ? 'text-danger' : 'text-muted' }} fw-bold">{{ $item->quantity_rejected }}</td>
                            <td>
                                <span class="badge {{ match($item->condition) {
                                    'good'=>'bg-success', 'damaged'=>'bg-warning text-dark',
                                    'defective'=>'bg-danger', 'expired'=>'bg-secondary', default=>'bg-light text-dark'
                                } }}">{{ ucfirst($item->condition) }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $item->remarks ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($delivery->remarks)
        <div class="card mt-4"><div class="card-body"><h6>Remarks</h6><p class="mb-0" style="font-size:13px">{{ $delivery->remarks }}</p></div></div>
        @endif
    </div>
</div>
@endsection
