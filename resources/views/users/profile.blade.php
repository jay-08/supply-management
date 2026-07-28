@extends('layouts.app')
@section('title', 'My Profile')
@section('breadcrumb')
    <li class="breadcrumb-item active">Profile</li>
@endsection
@section('content')
<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Update your personal information and password.</p>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-center p-4">
            <img src="{{ auth()->user()->avatar_url }}" class="avatar avatar-lg mx-auto mb-3" style="width:80px;height:80px" alt="{{ auth()->user()->name }}">
            <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
            <div class="text-muted mb-2" style="font-size:13px">{{ auth()->user()->email }}</div>
            <span class="badge bg-primary-subtle text-primary">{{ ucfirst(auth()->user()->role_name) }}</span>
            @if(auth()->user()->department)
            <div class="mt-2" style="font-size:12px;color:var(--text-muted)"><i class="bi bi-building me-1"></i>{{ auth()->user()->department->name }}</div>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Update Profile</h5></div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control" value="{{ auth()->user()->position }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12"><hr><h6>Change Password <small class="text-muted">(leave blank to keep current)</small></h6></div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Min. 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
