@extends('layouts.app')
@section('title', 'Supply Catalog')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requests.index') }}" class="text-decoration-none">Requests</a></li>
    <li class="breadcrumb-item active">Catalog</li>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Supply Catalog</h1>
        <p class="page-subtitle mb-0">Browse and add supplies to your request cart.</p>
    </div>
    <button class="btn btn-primary shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
        <i class="bi bi-cart3 me-1"></i> View Cart
    </button>
</div>

<div class="row g-4">
    @forelse($items as $item)
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; transition: transform 0.2s;">
                <div class="card-body text-center p-4">
                    {{-- Placeholder Icon for Supply Item --}}
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-seam" style="font-size: 2.5rem;"></i>
                    </div>
                    
                    <h5 class="card-title fw-bold text-truncate mb-1" title="{{ $item->name }}">{{ $item->name }}</h5>
                    <div class="badge bg-light text-secondary mb-3">{{ $item->category?->name ?? 'Uncategorized' }}</div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <span class="text-muted" style="font-size: 13px;">Available:</span>
                        @if($item->quantity > 0)
                            <span class="fw-bold text-success">{{ $item->quantity }} <small class="text-muted fw-normal">{{ $item->unit }}</small></span>
                        @else
                            <span class="fw-bold text-danger">Out of Stock</span>
                        @endif
                    </div>

                    @if($item->quantity > 0)
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text bg-light border-end-0">Qty</span>
                            <input type="number" class="form-control text-center border-start-0 ps-0" id="qty-{{ $item->id }}" value="1" min="1" max="{{ $item->quantity }}">
                        </div>
                        <button class="btn btn-outline-primary w-100 fw-medium" style="border-radius: 8px;" 
                            onclick="addToCart({ 
                                id: {{ $item->id }}, 
                                name: '{{ addslashes($item->name) }}', 
                                unit: '{{ addslashes($item->unit) }}', 
                                stock: {{ $item->quantity }}, 
                                quantity: parseInt(document.getElementById('qty-{{ $item->id }}').value) || 1 
                            })">
                            <i class="bi bi-cart-plus me-1"></i> Add to Request
                        </button>
                    @else
                        <button class="btn btn-secondary w-100 fw-medium disabled" style="border-radius: 8px;">
                            <i class="bi bi-x-circle me-1"></i> Unavailable
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <h5>No supplies available</h5>
            </div>
        </div>
    @endforelse
</div>

@endsection

@push('styles')
<style>
    .card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
</style>
@endpush
