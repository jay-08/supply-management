<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supply Portal') — Supply Management System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338ca;
            --bg-body: #f8fafc;
        }
        body {
            background-color: var(--bg-body);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }
        .container, .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
        }
        .navbar-brand-icon {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 12px;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .content-wrapper {
            flex-grow: 1;
            padding: 3rem 0;
        }
        .footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 2rem 0;
            margin-top: auto;
            text-align: center;
            color: #64748b;
        }
    </style>
    @stack('styles')
</head>
<body>

    {{-- TOP NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-3">
        <div class="container-fluid px-4 px-md-5">
            <a class="navbar-brand d-flex align-items-center fw-bold text-dark" href="{{ route('home') }}">
                <div class="navbar-brand-icon">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <span>Supply MS <span class="text-muted fw-normal ms-1 fs-6 d-none d-sm-inline">Portal</span></span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold text-primary' : '' }}" href="{{ route('home') }}">Track Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.catalog') ? 'active fw-bold text-primary' : '' }}" href="{{ route('public.catalog') }}">Supply Catalog</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    {{-- Cart Button --}}
                    @if(request()->routeIs('public.catalog') || request()->routeIs('public.checkout'))
                        <button class="btn btn-light position-relative rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                            <i class="bi bi-cart3 fs-5 text-dark"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="globalCartBadge" style="font-size: 0.65rem;">0</span>
                        </button>
                    @endif
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary rounded-pill px-4">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">Staff Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="content-wrapper">
        <div class="container-fluid px-4 px-md-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert" style="border-radius:12px; border-left: 5px solid #198754;">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert" style="border-radius:12px; border-left: 5px solid #dc3545;">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container-fluid px-4 px-md-5">
            <p class="mb-0">&copy; {{ date('Y') }} Supply Management System. All rights reserved.</p>
        </div>
    </footer>

    {{-- Cart Offcanvas (Only include on catalog pages) --}}
    @if(request()->routeIs('public.catalog') || request()->routeIs('public.checkout'))
    <div class="offcanvas offcanvas-end shadow" tabindex="-1" id="cartOffcanvas" style="width: 450px; max-width: 100%;">
        <div class="offcanvas-header border-bottom px-4">
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-cart3 text-primary"></i> Request Cart
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0 bg-light">
            <div class="flex-grow-1 overflow-auto p-4" id="cartItemsContainer">
                <div class="text-center text-muted my-5" id="emptyCartMessage">
                    <i class="bi bi-cart-x mb-2 d-block" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h5>Your cart is empty</h5>
                    <p class="fs-6">Browse supplies and add them to your request.</p>
                    <button type="button" data-bs-dismiss="offcanvas" class="btn btn-primary mt-2 btn-sm rounded-pill px-4">Continue Browsing</button>
                </div>
                <div id="cartItemsList" class="d-flex flex-column gap-3"></div>
            </div>
            <div class="bg-white border-top p-4 shadow-sm" id="checkoutSection" style="display: none;">
                <a href="{{ route('public.checkout') }}" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-pill">
                    Review and Confirm Request <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if(request()->routeIs('public.catalog') || request()->routeIs('public.checkout'))
    <script>
        const cartKey = '{{ auth()->check() ? "supplyCart_user_" . auth()->id() : "supplyCart_guest_" . session()->getId() }}';
        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

        function saveCart() {
            localStorage.setItem(cartKey, JSON.stringify(cart));
            updateCartUI();
        }

        function addToCart(item) {
            const existing = cart.find(i => i.id == item.id);
            if (existing) {
                if (existing.quantity + item.quantity <= item.stock) {
                    existing.quantity += item.quantity;
                } else {
                    Swal.fire({ icon: 'error', title: 'Limit Exceeded', text: 'Cannot add more than available stock.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    return;
                }
            } else {
                if (item.quantity > item.stock) {
                    Swal.fire({ icon: 'error', title: 'Limit Exceeded', text: 'Cannot add more than available stock.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    return;
                }
                cart.push(item);
            }
            saveCart();
            Swal.fire({ icon: 'success', title: 'Added to Request', text: `${item.name} added.`, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id != id);
            saveCart();
        }

        function updateCartItemQty(id, newQty) {
            const item = cart.find(i => i.id == id);
            if (item) {
                let qty = parseInt(newQty);
                if (qty > item.stock) qty = item.stock;
                if (qty < 1) qty = 1;
                item.quantity = qty;
                saveCart();
            }
        }

        function updateCartUI() {
            const badge = document.getElementById('globalCartBadge');
            if (badge) {
                if (cart.length > 0) {
                    badge.textContent = cart.length;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            }

            const emptyMsg = document.getElementById('emptyCartMessage');
            const itemsList = document.getElementById('cartItemsList');
            const checkoutSec = document.getElementById('checkoutSection');
            
            if (itemsList && emptyMsg && checkoutSec) {
                if (cart.length === 0) {
                    emptyMsg.style.display = 'block';
                    itemsList.innerHTML = '';
                    checkoutSec.style.display = 'none';
                } else {
                    emptyMsg.style.display = 'none';
                    checkoutSec.style.display = 'block';
                    itemsList.innerHTML = '';
                    
                    cart.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'd-flex align-items-center bg-white p-3 rounded-3 shadow-sm border mb-3';
                        div.innerHTML = `
                            <div class="bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px; font-size:1.5rem;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate" style="font-size:14px;" title="${item.name}">${item.name}</div>
                                <div class="text-muted" style="font-size:11px;">Max: ${item.stock} ${item.unit}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-2">
                                <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" value="${item.quantity}" min="1" max="${item.stock}" onchange="updateCartItemQty(${item.id}, this.value)">
                                <button type="button" class="btn btn-sm btn-light text-danger p-1" onclick="removeFromCart(${item.id})"><i class="bi bi-trash"></i></button>
                            </div>
                        `;
                        itemsList.appendChild(div);
                    });
                }
            }
        }

        function clearAllCartKeys() {
            try {
                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('supplyCart')) {
                        localStorage.removeItem(key);
                    }
                });
            } catch(e) {}
            cart = [];
            updateCartUI();
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateCartUI();
            
            @if(session('success'))
                clearAllCartKeys();
            @endif
        });
    </script>
    @endif
    
    @stack('scripts')
    @include('partials.chatbot')
</body>
</html>
