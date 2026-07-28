@extends('layouts.app')
@section('title', 'Edit User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Users</a></li>
    <li class="breadcrumb-item active">Edit {{ $user->name }}</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit User</h1>
</div>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">User Details</h5></div>
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="{{ $user->name }}" required></div>
                        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ $user->email }}" required></div>
                        <div class="col-md-6"><label class="form-label">Role *</label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $r)<option value="{{ $r->name }}" {{ $user->hasRole($r->name) ? 'selected':'' }}>{{ ucfirst($r->name) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">None</option>
                                @foreach($departments as $d)<option value="{{ $d->id }}" {{ $user->department_id == $d->id ? 'selected':'' }}>{{ $d->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Position</label><input type="text" name="position" class="form-control" value="{{ $user->position }}"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $user->phone }}"></div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
