@extends('layouts.app')
@section('title', 'Categories')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Manage supply item categories.</p>
    </div>
    @role('admin|supply-officer')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCatModal">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
    @endrole
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Items</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $cat->code }}</span></td>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $cat->description ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $cat->inventory_items_count }} items</span></td>
                        <td>{!! $cat->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-end">
                            @role('admin|supply-officer')
                            <button class="btn btn-sm btn-light" onclick="editCat({{ json_encode($cat) }})" style="border-radius:6px"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete category '{{ $cat->name }}'?" style="border-radius:6px"><i class="bi bi-trash"></i></button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $categories->links() }}</div>
    </div>
</div>

<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
                <div class="col-8"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required placeholder="OFF"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="editCatForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body row g-3">
                <div class="col-8"><label class="form-label">Name *</label><input type="text" name="name" id="eCatName" class="form-control" required></div>
                <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" id="eCatCode" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="eCatDesc" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Update</button></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
function editCat(c) {
    document.getElementById('editCatForm').action = `/categories/${c.id}`;
    document.getElementById('eCatName').value = c.name;
    document.getElementById('eCatCode').value = c.code;
    document.getElementById('eCatDesc').value = c.description || '';
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}
</script>
@endpush
