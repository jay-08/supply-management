@extends('layouts.app')
@section('title', 'Edit PO: ' . $purchaseOrder->po_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders.show', $purchaseOrder->id) }}" class="text-decoration-none">{{ $purchaseOrder->po_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Purchase Order</h1>
    <p class="page-subtitle">Update purchase order: {{ $purchaseOrder->po_number }}</p>
</div>
<form action="{{ route('procurement.purchase-orders.update', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data" id="poForm">
@csrf
@method('PUT')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">PO Details</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Supplier *</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ $purchaseOrder->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">PO Date *</label>
                    <input type="date" name="po_date" class="form-control" value="{{ $purchaseOrder->po_date->format('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" value="{{ $purchaseOrder->delivery_date?->format('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control" placeholder="e.g. 30 days net, COD" value="{{ $purchaseOrder->payment_terms }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <input type="text" name="delivery_address" class="form-control" placeholder="Office — Supply Room" value="{{ $purchaseOrder->delivery_address }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">VAT / Tax Rate (%)</label>
                    <input type="number" name="tax_rate" class="form-control" value="{{ $purchaseOrder->tax_rate }}" min="0" max="100" step="0.01" id="taxRate" onchange="updateTotals()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $purchaseOrder->notes }}</textarea>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="total_amount" class="form-control form-control-lg text-success fw-bold" step="0.01" min="0" required value="{{ $purchaseOrder->total_amount }}">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="status" value="draft" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-floppy"></i> Save Draft</button>
                    <button type="submit" name="status" value="pending" class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg"></i> Submit PO</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title">PO Attachment</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5" style="border: 2px dashed #cbd5e1; border-radius: 12px; margin: 20px;">
                @if($purchaseOrder->attachment)
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-check text-success mb-2" style="font-size: 3rem;"></i>
                        <h6 class="fw-bold">Current Attachment</h6>
                        <a href="{{ asset('storage/' . $purchaseOrder->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-box-arrow-up-right me-1"></i> View File</a>
                    </div>
                    <p class="text-muted">Upload a new file to replace the current attachment.</p>
                @else
                    <i class="bi bi-cloud-arrow-up text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mb-2">Upload Purchase Order</h5>
                    <p class="text-muted mb-4">Attach the scanned or digital Purchase Order document (PDF or Image).</p>
                @endif
                <input type="file" name="attachment" id="poAttachment" class="form-control w-75" accept=".pdf,image/*">
            </div>
        </div>
    </div>
</div>
</form>
@endsection
