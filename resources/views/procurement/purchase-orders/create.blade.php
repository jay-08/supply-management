@extends('layouts.app')
@section('title', 'New Purchase Order')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
    <li class="breadcrumb-item active">New PO</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">New Purchase Order</h1>
    <p class="page-subtitle">Create a new purchase order for a supplier.</p>
</div>
<form action="{{ route('procurement.purchase-orders.store') }}" method="POST" enctype="multipart/form-data" id="poForm">
@csrf
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
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">PO Date *</label>
                    <input type="date" name="po_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control" placeholder="e.g. 30 days net, COD">
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <input type="text" name="delivery_address" class="form-control" placeholder="Office — Supply Room">
                </div>
                <div class="mb-3">
                    <label class="form-label">VAT / Tax Rate (%)</label>
                    <input type="number" name="tax_rate" class="form-control" value="12" min="0" max="100" step="0.01" id="taxRate" onchange="updateTotals()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Amount *</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="total_amount" class="form-control form-control-lg text-success fw-bold" step="0.01" min="0" required placeholder="0.00">
                    </div>
                </div>

                <!-- Legacy PO Toggle -->
                <div class="mb-3 border-top pt-3 mt-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_legacy" value="1" id="isLegacyToggle" onchange="toggleLegacyFields()">
                        <label class="form-check-label fw-bold text-primary" for="isLegacyToggle">Add as Old / Legacy PO</label>
                    </div>
                    <div id="legacyFields" class="bg-light p-3 rounded border d-none">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <select name="legacy_status" class="form-select" id="legacyStatus">
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="budget_approved">Budget Approved</option>
                                <option value="accounting_approved">Accounting Approved</option>
                                <option value="ard_approved">ARD Approved</option>
                                <option value="sent">Sent to Supplier</option>
                                <option value="partially_delivered">Partially Delivered</option>
                                <option value="delivered">Fully Delivered</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tracking / Legacy Remarks</label>
                            <textarea name="legacy_remarks" class="form-control" rows="3" placeholder="Enter any historical tracking details (e.g. 'Approved by Accounting on Jan 15, Delivered on Jan 20')"></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4" id="standardActions">
                    <button type="submit" name="status" value="draft" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-floppy"></i> Draft</button>
                    <button type="submit" name="status" value="pending" class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg"></i> Create PO</button>
                </div>
                <div class="d-flex gap-2 mt-4 d-none" id="legacyActions">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Save Legacy PO</button>
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
                <i class="bi bi-cloud-arrow-up text-muted mb-3" style="font-size: 4rem;"></i>
                <h5 class="fw-bold mb-2">Upload Purchase Order</h5>
                <p class="text-muted mb-4">Attach the scanned or digital Purchase Order document (PDF or Image).</p>
                
                <input type="file" name="attachment" id="poAttachment" class="form-control w-75" accept=".pdf,image/*">
            </div>
        </div>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
function toggleLegacyFields() {
    const isLegacy = document.getElementById('isLegacyToggle').checked;
    
    // Toggle Legacy Fields visibility
    const legacyFields = document.getElementById('legacyFields');
    if(isLegacy) legacyFields.classList.remove('d-none'); else legacyFields.classList.add('d-none');
    
    // Toggle Actions visibility
    const standardActions = document.getElementById('standardActions');
    const legacyActions = document.getElementById('legacyActions');
    
    if (isLegacy) {
        standardActions.classList.add('d-none');
        legacyActions.classList.remove('d-none');
    } else {
        standardActions.classList.remove('d-none');
        legacyActions.classList.add('d-none');
    }
    
    document.getElementById('legacyStatus').required = isLegacy;
}
</script>
@endpush
