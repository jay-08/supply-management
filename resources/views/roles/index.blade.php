@extends('layouts.app')
@section('title', 'Roles')
@section('breadcrumb')
    <li class="breadcrumb-item active">Roles</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">Roles</h1><p class="page-subtitle">Manage user roles.</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="bi bi-plus-lg"></i> Add Role</button>
</div>
<div class="row g-4">
    @foreach($roles as $role)
    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue" style="width:40px;height:40px;font-size:18px"><i class="bi bi-shield-fill"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size:14px">{{ ucfirst($role->name) }}</h5>
                            <small class="text-muted">{{ $role->users_count }} users</small>
                        </div>
                    </div>
                    @php
                        $sysRoles = ['admin','supply-officer','staff','auditor','budget-officer','accounting','regional-director','assistant-regional-director','client','supply-staff','budget-staff','accounting-staff','ard-staff','rd-staff'];
                    @endphp
                    @if(!in_array($role->name, $sysRoles))
                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete role '{{ $role->name }}'?"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </div>
                <div class="p-2 rounded" style="background:var(--body-bg);font-size:11px;color:var(--text-muted)">
                    @if(in_array($role->name, $sysRoles))
                        System role — cannot be deleted.
                    @else
                        Custom role
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <label class="form-label">Role Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. manager">
                <div class="form-text">Use lowercase, hyphenated names.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create Role</button></div>
        </form>
    </div></div>
</div>
@endsection
