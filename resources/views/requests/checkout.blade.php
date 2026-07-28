@extends('layouts.app')
@section('title', 'Review Request')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requests.index') }}" class="text-decoration-none">Requests</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requests.create') }}" class="text-decoration-none">Catalog</a></li>
    <li class="breadcrumb-item active">Review & Confirm</li>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Review Request</h1>
        <p class="page-subtitle mb-0">Provide details and confirm your supply request.</p>
    </div>
</div>

<div class="row g-4" id="checkoutMainContainer">
    {{-- Form Section --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-2 border-bottom-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>Request Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('requests.store') }}" method="POST" id="checkoutForm">
                    @csrf
                    <div id="checkoutHiddenInputs"></div>

                    <div class="mb-4">
                        <label class="form-label fw-medium text-dark">Department *</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">Select department</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ auth()->user()->department_id == $d->id ? 'selected':'' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium text-dark">Purpose *</label>
                        <textarea name="purpose" class="form-control" rows="3" required placeholder="State the reason or project for this request..."></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-dark">Needed By <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="date" name="needed_by" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-dark">Remarks <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="text" name="remarks" class="form-control" placeholder="Any additional notes...">
                        </div>
                    </div>

                    <hr class="my-4 text-muted">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('requests.create') }}" class="btn btn-light px-4">Back to Catalog</a>
                        <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill shadow-sm" id="btnConfirmSubmit">
                            Submit Request <i class="bi bi-send ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Order Summary Section --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0" style="background: var(--bs-gray-100);">
            <div class="card-header bg-transparent pt-4 pb-2 border-bottom-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-bag-check text-primary me-2"></i>Items Requested</h5>
            </div>
            <div class="card-body p-4" id="checkoutSummaryList">
                <div class="text-center text-muted my-4">
                    <div class="spinner-border spinner-border-sm" role="status"></div> Loading cart...
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Empty Cart Warning --}}
<div class="text-center py-5 d-none" id="emptyCheckoutWarning">
    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
    <h3 class="mt-3 fw-bold text-dark">Your cart is empty</h3>
    <p class="text-muted fs-5">You haven't added any supplies to your request yet.</p>
    <a href="{{ route('requests.create') }}" class="btn btn-primary px-4 mt-2 rounded-pill">Browse Catalog</a>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const summaryList = document.getElementById('checkoutSummaryList');
    const hiddenInputs = document.getElementById('checkoutHiddenInputs');
    const mainContainer = document.getElementById('checkoutMainContainer');
    const emptyWarning = document.getElementById('emptyCheckoutWarning');
    const submitBtn = document.getElementById('btnConfirmSubmit');

    function renderCheckoutCart() {
        if (!cart || cart.length === 0) {
            mainContainer.classList.add('d-none');
            emptyWarning.classList.remove('d-none');
            return;
        }

        summaryList.innerHTML = '';
        hiddenInputs.innerHTML = '';

        cart.forEach((item, index) => {
            // Render UI
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center bg-white p-3 rounded-3 shadow-sm mb-3 border border-light';
            div.innerHTML = `
                <div class="bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px; font-size:1.5rem;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate" style="font-size:15px;" title="${item.name}">${item.name}</div>
                    <div class="text-muted" style="font-size:12px;">Requested: <strong class="text-dark">${item.quantity}</strong> ${item.unit}</div>
                </div>
            `;
            summaryList.appendChild(div);

            // Render Hidden Inputs
            hiddenInputs.innerHTML += `<input type="hidden" name="items[${index}][inventory_item_id]" value="${item.id}">`;
            hiddenInputs.innerHTML += `<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`;
        });
    }

    renderCheckoutCart();

    // Prevent submitting empty cart & clear cart on submit
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        if (!cart || cart.length === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Empty Cart', text: 'Please add items to your request first.' });
            return;
        }
        if (typeof clearAllCartKeys === 'function') {
            clearAllCartKeys();
        }
    });
});
</script>
@endpush
