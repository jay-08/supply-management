@extends('layouts.app')
@section('title', 'Process Return')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Returns</a></li>
    <li class="breadcrumb-item active">Process Return</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Process Return</h1>
</div>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Return Details</h5></div>
            <div class="card-body">
                <form action="{{ route('returns.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Issuance *</label>
                        <select name="issuance_id" class="form-select" required id="issuanceSelect" onchange="loadItems()">
                            <option value="">Choose issuance...</option>
                            @foreach($issuances as $iss)
                            <option value="{{ $iss->id }}" data-items='@json($iss->items->map(fn($i)=>["id"=>$i->inventory_item_id,"name"=>$i->inventoryItem?->name,"qty"=>$i->quantity]))'>
                                {{ $iss->issuance_number }} — {{ $iss->recipient?->name }} ({{ $iss->issued_at?->format('M d Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item *</label>
                        <select name="inventory_item_id" class="form-select" required id="itemSelect">
                            <option value="">Select issuance first...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition *</label>
                        <select name="condition" class="form-select" required>
                            <option value="good">Good (will be restocked)</option>
                            <option value="damaged">Damaged (no restock)</option>
                            <option value="defective">Defective (no restock)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Reason for return..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('returns.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Process Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function loadItems() {
    const sel = document.getElementById('issuanceSelect');
    const itemSel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    const items = opt.dataset.items ? JSON.parse(opt.dataset.items) : [];
    itemSel.innerHTML = '<option value="">Select item...</option>';
    items.forEach(i => {
        const o = document.createElement('option');
        o.value = i.id; o.textContent = `${i.name} (issued: ${i.qty})`;
        itemSel.appendChild(o);
    });
}
</script>
@endpush
