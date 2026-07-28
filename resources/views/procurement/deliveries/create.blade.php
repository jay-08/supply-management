@extends('layouts.app')
@section('title', 'Record Delivery')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('procurement.deliveries.index') }}" class="text-decoration-none">Deliveries</a></li>
    <li class="breadcrumb-item active">Record Delivery</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Record Delivery / GRN</h1>
    <p class="page-subtitle">Enter goods received note for a purchase order delivery.</p>
</div>
<form action="{{ route('procurement.deliveries.store') }}" method="POST" enctype="multipart/form-data" id="grnForm">
@csrf
@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Delivery Details</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Purchase Order *</label>
                    <select name="purchase_order_id" class="form-select" required id="poSelect">
                        <option value="">Select PO...</option>
                        @foreach($pos as $po)
                        <option value="{{ $po->id }}" {{ request('po_id')==$po->id ? 'selected' : '' }}>
                            {{ $po->po_number }} — {{ $po->supplier?->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_final_delivery" value="1" id="isFinalDelivery">
                        <label class="form-check-label text-danger fw-bold" for="isFinalDelivery">Mark PO as Fully Delivered</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivery Date *</label>
                    <input type="date" name="delivery_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Supplier DR Number</label>
                    <input type="text" name="dr_number" class="form-control" placeholder="Delivery receipt #">
                </div>
                <div class="mb-3">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-control" placeholder="Invoice #">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.png">
                </div>
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg"></i> Save GRN & Update Inventory</button>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Items Received</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDeliveryRow()"><i class="bi bi-plus"></i> Add Item</button>
            </div>
            <div class="table-responsive">
                <table class="table mb-0" id="grnTable">
                    <thead>
                        <tr><th>Inventory Item *</th><th style="width:110px">Delivered *</th><th style="width:110px">Accepted *</th><th style="width:120px">Condition</th><th>Notes</th><th style="width:36px"></th></tr>
                    </thead>
                    <tbody id="grnBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-info mt-3 py-2" style="font-size:12px">
            <i class="bi bi-info-circle-fill me-2"></i>
            Only items with condition <strong>Good</strong> will be automatically added to inventory stock.
        </div>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
const inventoryItems = @json($inventoryItems);
let rowIdx = 0;

function addDeliveryRow() {
    let options = '<option value="">Select Item...</option>';
    inventoryItems.forEach(item => {
        options += `<option value="${item.id}">${item.name} (${item.unit})</option>`;
    });

    const tr = document.createElement('tr');
    tr.id = `delRow${rowIdx}`;
    tr.innerHTML = `
        <td>
            <select name="items[${rowIdx}][inventory_item_id]" class="form-select form-select-sm" required>
                ${options}
            </select>
        </td>
        <td><input type="number" name="items[${rowIdx}][quantity_delivered]" class="form-control form-control-sm"
                min="0.01" step="0.01" required value="1"></td>
        <td><input type="number" name="items[${rowIdx}][quantity_accepted]" class="form-control form-control-sm"
                min="0" step="0.01" required value="1"></td>
        <td>
            <select name="items[${rowIdx}][condition]" class="form-select form-select-sm">
                <option value="good">Good</option>
                <option value="damaged">Damaged</option>
                <option value="defective">Defective</option>
                <option value="expired">Expired</option>
            </select>
        </td>
        <td><input type="text" name="items[${rowIdx}][remarks]" class="form-control form-control-sm" placeholder="Optional"></td>
        <td><button type="button" class="btn btn-sm btn-light text-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>
    `;
    document.getElementById('grnBody').appendChild(tr);
    rowIdx++;
}

window.addEventListener('DOMContentLoaded', function() {
    addDeliveryRow(); // Add first row by default
});
</script>
@endpush
