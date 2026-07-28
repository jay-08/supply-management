@extends('layouts.app')
@section('title', 'Suppliers')
@section('breadcrumb')
    <li class="breadcrumb-item active">Suppliers</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">Suppliers</h1>
        <p class="page-subtitle">Manage your supply vendors and purchase history.</p>
    </div>
    @role('admin|supply-officer')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="bi bi-plus-lg"></i> Add Supplier
    </button>
    @endrole
</div>

<form method="GET" class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex gap-2">
            <div class="flex-grow-1 position-relative">
                <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Search suppliers..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        @if($suppliers->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Supplier</th><th>Contact</th><th>Phone/Email</th><th>City</th><th>Items</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($suppliers as $sup)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size:13px">{{ $sup->name }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $sup->code }}</div>
                        </td>
                        <td style="font-size:13px">{{ $sup->contact_person ?? '—' }}</td>
                        <td>
                            <div style="font-size:12px">{{ $sup->phone ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $sup->email ?? '' }}</div>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $sup->city ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $sup->inventory_items_count }}</span></td>
                        <td>{!! $sup->status_badge !!}</td>
                        <td class="text-end">
                            <a href="{{ route('suppliers.show', $sup->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-eye"></i></a>
                            @role('admin|supply-officer')
                            <button class="btn btn-sm btn-light" onclick="editSupplier({{ json_encode($sup) }})" style="border-radius:6px"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete {{ $sup->name }}?" style="border-radius:6px"><i class="bi bi-trash"></i></button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $suppliers->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-truck"></i><h5>No suppliers found</h5></div>
        @endif
    </div>
</div>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Add Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
                <div class="col-md-8"><label class="form-label">Supplier Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required placeholder="NBS"></div>
                <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Website</label><input type="url" name="website" class="form-control"></div>
                <div class="col-md-8"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Supplier</button></div>
        </form>
    </div></div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title text-warning"><i class="bi bi-pencil-fill me-2"></i>Edit Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="editSupplierForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body row g-3">
                <div class="col-md-8"><label class="form-label">Name *</label><input type="text" name="name" id="es_name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Code *</label><input type="text" name="code" id="es_code" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" id="es_contact" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" id="es_phone" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="es_email" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" id="es_city" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Status</label>
                    <select name="status" id="es_status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" id="es_notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Update</button></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
function editSupplier(s) {
    document.getElementById('editSupplierForm').action = `/suppliers/${s.id}`;
    ['name','code','contact_person','phone','email','city','status','notes'].forEach(f => {
        const el = document.getElementById(`es_${f}`);
        if (el) el.value = s[f] || '';
    });
    new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
}
</script>
@endpush
