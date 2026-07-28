@extends('layouts.app')
@section('title', 'Users')
@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage system users and role assignments.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus"></i> Add User</button>
</div>

<form method="GET" class="card mb-4"><div class="card-body py-3 d-flex gap-2 flex-wrap">
    <input type="text" name="search" class="form-control" style="max-width:250px" placeholder="Search name/email..." value="{{ request('search') }}">
    <select name="role" class="form-select" style="max-width:160px">
        <option value="">All Roles</option>
        @foreach($roles as $r)<option value="{{ $r->name }}" {{ request('role')===$r->name?'selected':'' }}>{{ ucfirst($r->name) }}</option>@endforeach
    </select>
    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i></button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
</div></form>

<div class="card">
    <div class="card-body p-0">
        @if($users->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>User</th><th>Role</th><th>Department</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url }}" class="avatar avatar-sm" alt="{{ $user->name }}">
                                <div>
                                    <div class="fw-semibold" style="font-size:13px">{{ $user->name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($user->role_name) }}</span></td>
                        <td style="font-size:12px">{{ $user->department?->name ?? '—' }}</td>
                        <td>{!! $user->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td style="font-size:11px;color:var(--text-muted)">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-light" style="border-radius:6px"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light {{ $user->is_active ? 'text-warning' : 'text-success' }}" style="border-radius:6px"
                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $user->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-light text-danger" data-confirm="Delete user {{ $user->name }}?" style="border-radius:6px"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $users->links() }}</div>
        @else
        <div class="empty-state"><i class="bi bi-people"></i><h5>No users found</h5></div>
        @endif
    </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-plus-fill text-primary me-2"></i>Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
                <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                <div class="col-md-6"><label class="form-label">Confirm Password *</label><input type="password" name="password_confirmation" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">None</option>
                        @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Position</label><input type="text" name="position" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create User</button></div>
        </form>
    </div></div>
</div>
@endsection
