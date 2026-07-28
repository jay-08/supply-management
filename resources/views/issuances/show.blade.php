@extends('layouts.app')
@section('title', $issuance->issuance_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('issuances.index') }}" class="text-decoration-none">Issuances</a></li>
    <li class="breadcrumb-item active">{{ $issuance->issuance_number }}</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ $issuance->issuance_number }}</h1>
        <p class="page-subtitle">Issued on {{ $issuance->issued_at?->format('F d, Y \a\t H:i') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('issuances.print', $issuance->id) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</a>
        <a href="{{ route('issuances.pdf', $issuance->id) }}" class="btn btn-danger"><i class="bi bi-file-pdf"></i> PDF</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Issuance Details</h5></div>
            <div class="card-body p-0">
                @foreach([
                    ['Issuance #', $issuance->issuance_number],
                    ['Issued To', $issuance->recipient?->name],
                    ['Issued By', $issuance->issuer?->name],
                    ['Department', $issuance->department?->name ?? 'N/A'],
                    ['Related Request', $issuance->supplyRequest?->request_number ?? 'Direct'],
                    ['Date Issued', $issuance->issued_at?->format('M d, Y H:i')],
                    ['Total Value', '₱' . number_format($issuance->total_value, 2)],
                ] as [$l, $v])
                <div class="d-flex justify-content-between px-4 py-2" style="border-bottom:1px solid var(--border)">
                    <span style="font-size:12px;color:var(--text-muted)">{{ $l }}</span>
                    <span style="font-size:13px;font-weight:600">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @if($issuance->remarks)
        <div class="card"><div class="card-body"><h6>Remarks</h6><p class="mb-0" style="font-size:13px">{{ $issuance->remarks }}</p></div></div>
        @endif
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Items Issued</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Item</th><th>Category</th><th>Qty</th><th>Unit Cost</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($issuance->items as $item)
                        <tr>
                            <td><div class="fw-semibold" style="font-size:13px">{{ $item->inventoryItem?->name }}</div><div style="font-size:11px;color:var(--text-muted)">{{ $item->inventoryItem?->item_code }}</div></td>
                            <td><span class="badge bg-light text-dark">{{ $item->inventoryItem?->category?->name }}</span></td>
                            <td class="fw-bold">{{ $item->quantity }} {{ $item->inventoryItem?->unit }}</td>
                            <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-semibold">₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--body-bg)">
                            <td colspan="4" class="text-end fw-bold">Total Value:</td>
                            <td class="text-end fw-bold text-primary" style="font-size:15px">₱{{ number_format($issuance->total_value, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
