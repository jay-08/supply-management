@extends('layouts.app')
@section('title', 'Departments')
@section('breadcrumb')
    <li class="breadcrumb-item active">Departments</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">Departments</h1><p class="page-subtitle">Manage organizational departments.</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="bi bi-plus-lg"></i> Add Department</button>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Head</th><th>Users</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $dept->code }}</span></td>
                        <td class="fw-semibold">{{ $dept->name }}</td>
                        <td style="font-size:13px">{{ $dept->head ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $dept->users_count }}</span></td>
                        <td>{!! $dept->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light" onclick="editDept({{ json_encode($dept) }})" style="border-radius:6px"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete {{ $dept->name }}?" style="border-radius:6px"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $departments->links() }}</div>
    </div>
</div>

<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('departments.store') }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
                <div class="col-md-8"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required placeholder="ADM"></div>
                <div class="col-12"><label class="form-label">Department Head</label><input type="text" name="head" class="form-control"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="editDeptModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="editDeptForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body row g-3">
                <div class="col-md-8"><label class="form-label">Name *</label><input type="text" name="name" id="ed_name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Code *</label><input type="text" name="code" id="ed_code" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Head</label><input type="text" name="head" id="ed_head" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Update</button></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
function editDept(d) {
    document.getElementById('editDeptForm').action = `/departments/${d.id}`;
    document.getElementById('ed_name').value = d.name;
    document.getElementById('ed_code').value = d.code;
    document.getElementById('ed_head').value = d.head || '';
    new bootstrap.Modal(document.getElementById('editDeptModal')).show();
}
</script>
@endpush
