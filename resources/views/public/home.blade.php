@extends('layouts.public')
@section('title', 'Welcome')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 text-center mt-5 mb-5">
        <h1 class="display-4 fw-bold mb-3" style="color: #1e293b;">Track Your Supplies</h1>
        <p class="lead text-muted mb-5">Enter your Request Number or Purchase Order Number below to check the real-time status of your items.</p>
        
        <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-5">
                <form action="{{ route('public.track') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-lg shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-white border-primary border-end-0 text-primary px-4">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="query" class="form-control border-primary border-start-0 py-3" placeholder="e.g. REQ-2026-0001 or PO-2026-0001" required style="font-size: 1.1rem; box-shadow: none;">
                        <button class="btn btn-primary px-5 fw-bold text-uppercase" type="submit" style="letter-spacing: 0.5px;">Track Status</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-5 pt-4">
            <h4 class="text-muted fw-normal mb-3">Need to request new supplies?</h4>
            <a href="{{ route('public.catalog') }}" class="btn btn-outline-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                <i class="bi bi-cart-plus me-2"></i> Browse Supply Catalog
            </a>
        </div>
    </div>
</div>
@endsection
