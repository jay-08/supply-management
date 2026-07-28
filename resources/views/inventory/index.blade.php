@extends('layouts.app')
@section('title', 'Inventory')

@section('breadcrumb')
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">In Stocks</h1>
        <p class="page-subtitle">Manage all supply items, stock levels, and item details.</p>
    </div>
    @role('admin|supply-officer')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="bi bi-plus-lg"></i> Add Item
    </button>
    @endrole
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                    <input type="text" name="search" class="form-control ps-4" placeholder="Search items..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="lowStockFilter" {{ request('low_stock') ? 'checked' : '' }}>
                    <label class="form-check-label" for="lowStockFilter" style="font-size:13px">Low Stock Only</label>
                </div>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Items Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="bi bi-archive-fill text-primary me-2"></i>
            Items
            <span class="badge bg-primary-subtle text-primary ms-2" style="font-size:12px">{{ $items->total() }}</span>
        </h5>
        <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;border-radius:8px">
            <i class="bi bi-tag"></i> Categories
        </a>
    </div>
    <div class="card-body p-0">
        @if($items->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Cost</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                     style="width:38px;height:38px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
                                <div>
                                    <a href="{{ route('inventory.show', $item->id) }}" class="text-decoration-none fw-bold" style="font-size:13px">{{ $item->name }}</a>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $item->item_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $item->category?->name }}</span></td>
                        <td style="font-size:13px;color:var(--text-muted)">{{ $item->unit }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold {{ $item->quantity == 0 ? 'text-danger' : ($item->isLowStock() ? 'text-warning' : 'text-success') }}">
                                    {{ $item->quantity }}
                                </span>
                                {!! $item->stock_badge !!}
                            </div>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $item->reorder_level }}</td>
                        <td style="font-size:13px">₱{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{!! $item->status_badge !!}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-sm btn-light" title="View" style="border-radius:6px">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @role('admin|supply-officer')
                                <button class="btn btn-sm btn-light" title="Edit" style="border-radius:6px"
                                        onclick="openEditModal({{ json_encode($item) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete {{ $item->name }}?" style="border-radius:6px">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $items->links() }}</div>
        @else
        <div class="empty-state">
            <i class="bi bi-archive"></i>
            <h5>No inventory items found</h5>
            <p class="text-muted">Try adjusting your filters or add a new item.</p>
        </div>
        @endif
    </div>
</div>

{{-- ===== ADD ITEM MODAL ===== --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Item Code *</label>
                            <input type="text" name="item_code" class="form-control" required placeholder="e.g. OFF-001">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Item Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Stapler (Standard)">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">Select supplier</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit *</label>
                            <select name="unit" class="form-select" required>
                                <option value="piece">Piece</option>
                                <option value="box">Box</option>
                                <option value="ream">Ream</option>
                                <option value="roll">Roll</option>
                                <option value="bottle">Bottle</option>
                                <option value="pack">Pack</option>
                                <option value="set">Set</option>
                                <option value="liter">Liter</option>
                                <option value="kilogram">Kilogram</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Initial Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reorder Level *</label>
                            <input type="number" name="reorder_level" class="form-control" min="0" value="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Cost (₱) *</label>
                            <input type="number" name="unit_cost" class="form-control" min="0" step="0.01" value="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Storage Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Cabinet A, Shelf 2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Item Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-fill text-warning me-2"></i>Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Item Code *</label>
                            <input type="text" name="item_code" id="edit_item_code" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Item Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" id="edit_supplier_id" class="form-select">
                                <option value="">None</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit *</label>
                            <input type="text" name="unit" id="edit_unit" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reorder Level *</label>
                            <input type="number" name="reorder_level" id="edit_reorder_level" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Cost (₱) *</label>
                            <input type="number" name="unit_cost" id="edit_unit_cost" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="discontinued">Discontinued</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-floppy"></i> Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(item) {
    document.getElementById('editItemForm').action = `/inventory/${item.id}`;
    document.getElementById('edit_item_code').value  = item.item_code;
    document.getElementById('edit_name').value        = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_category_id').value = item.category_id;
    document.getElementById('edit_supplier_id').value = item.supplier_id || '';
    document.getElementById('edit_unit').value         = item.unit;
    document.getElementById('edit_reorder_level').value = item.reorder_level;
    document.getElementById('edit_unit_cost').value   = item.unit_cost;
    document.getElementById('edit_status').value      = item.status;
    document.getElementById('edit_location').value    = item.location || '';
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}
</script>
@endpush
