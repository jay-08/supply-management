@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="d-flex align-items-center justify-content-center w-100" style="min-height: 75vh;">
    <div class="text-center p-5 bg-white shadow-sm" style="border-radius: 16px; max-width: 600px;">
        <div class="mb-4">
            <i class="bi bi-shield-lock-fill text-danger" style="font-size: 6rem; opacity: 0.8;"></i>
        </div>
        
        <h1 class="fw-bold mb-3" style="color: var(--text-dark); font-size: 2.5rem;">Access Denied</h1>
        
        <p class="text-muted fs-5 mb-2">
            Oops! It looks like you don't have the correct roles or permissions to view this page.
        </p>
        
        <p class="text-muted mb-5" style="font-size: 14px;">
            {{ $exception->getMessage() === 'User does not have the right roles.' ? 'If you believe this is a mistake, please contact your system administrator.' : ($exception->getMessage() ?: 'If you believe this is a mistake, please contact your system administrator.') }}
        </p>

        <div class="d-flex justify-content-center gap-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 8px; font-weight: 500;">
                <i class="bi bi-arrow-left me-2"></i> Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-weight: 500;">
                <i class="bi bi-house-door me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
