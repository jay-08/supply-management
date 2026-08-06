<!DOCTYPE html>
<html lang="en" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Supply Management System</title>
    <meta name="description" content="Office Supply Unit Management System">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    {{-- Custom --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
<body>

{{-- ===== GLOBAL LOADING SCREEN ===== --}}
<div id="pageLoader">
    <div class="loader-content">
        <div class="loader-icon-box">
            <div class="loader-spinner"></div>
            <i class="bi bi-box-seam-fill loader-brand-icon"></i>
        </div>
        <div class="loader-text">Supply Management System</div>
        <div class="loader-subtitle" id="loaderSubtext">
            <span>Loading workspace</span>
            <span class="loader-dots"><span></span><span></span><span></span></span>
        </div>
    </div>
</div>

<div class="app-wrapper" id="appWrapper">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="sidebar-brand-text">
                Supply MS
                <small>Management System</small>
            </div>
        </a>

        <nav class="sidebar-nav">
            @unlessrole('client')
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-label">Inventory</div>

            <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="bi bi-archive-fill"></i>
                <span>In Stocks</span>
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tag-fill"></i>
                <span>Categories</span>
            </a>
            @endunlessrole

            <div class="nav-section-label">Supply Chain</div>

            <a href="{{ route('requests.index') }}" class="sidebar-link {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                <i class="bi bi-cart-plus-fill"></i>
                <span>Supply Requests</span>
                @php $pending = \App\Models\SupplyRequest::where('status','pending')->count(); @endphp
                @if($pending > 0)
                    <span class="sidebar-badge">{{ $pending }}</span>
                @endif
            </a>
            @hasanyrole('admin|supply-officer|auditor|supply-staff')
            <a href="{{ route('issuances.index') }}" class="sidebar-link {{ request()->routeIs('issuances.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-right"></i>
                <span>Issuances</span>
            </a>
            @endhasanyrole

            @hasanyrole('admin|supply-officer|supply-staff')
            <a href="{{ route('returns.index') }}" class="sidebar-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-left"></i>
                <span>Returns</span>
            </a>
            @endhasanyrole

            @hasanyrole('admin|supply-officer|auditor|supply-staff')
            <div class="nav-section-label">Suppliers</div>

            <a href="{{ route('suppliers.index') }}" class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>
            @endhasanyrole

            <div class="nav-section-label">Procurement</div>

            <a href="{{ route('procurement.dashboard') }}" class="sidebar-link {{ request()->routeIs('procurement.dashboard') ? 'active' : '' }}">
                <i class="bi bi-cart4"></i>
                <span>Proc. Dashboard</span>
            </a>
            <a href="{{ route('procurement.purchase-orders.index') }}" class="sidebar-link {{ request()->routeIs('procurement.purchase-orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Purchase Orders</span>
            </a>
            @hasanyrole('admin|supply-officer|supply-staff')
            <a href="{{ route('procurement.deliveries.index') }}" class="sidebar-link {{ request()->routeIs('procurement.deliveries.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Deliveries / GRN</span>
            </a>
            @endhasanyrole
            <a href="{{ route('procurement.reports.index') }}" class="sidebar-link {{ request()->routeIs('procurement.reports.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Proc. Reports</span>
            </a>

            @unlessrole('client')
            <div class="nav-section-label">Reports</div>

            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Reports</span>
            </a>
            @endunlessrole


            @role('admin')
            <div class="nav-section-label">Administration</div>

            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('departments.index') }}" class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>Departments</span>
            </a>
            <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-fill"></i>
                <span>Roles</span>
            </a>
            @endrole
        </nav>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div class="main-area" id="mainArea">

        {{-- TOP BAR --}}
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>

            {{-- Breadcrumb / Page title on desktop --}}
            <div class="d-none d-md-block">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="topbar-actions ms-auto">

                {{-- Cart Button --}}
                <button class="topbar-btn position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" title="Request Cart">
                    <i class="bi bi-cart3"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="globalCartBadge" style="font-size: 0.65rem; padding: 0.35em 0.5em;">
                        0
                    </span>
                </button>

                {{-- Dark Mode Toggle --}}
                <button class="topbar-btn ms-2" id="darkModeToggle" title="Toggle Dark Mode">
                    <i class="bi {{ session('theme') === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' }}"></i>
                </button>

                {{-- Notifications --}}
                <div class="dropdown">
                    <button class="topbar-btn" data-bs-toggle="dropdown" id="notifBtn" title="Notifications">
                        <i class="bi bi-bell-fill"></i>
                        <span class="notif-badge d-none" id="notifCount"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width:340px; border-radius:12px; overflow:hidden; border:1px solid var(--border); box-shadow:var(--shadow-md)">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-bottom:1px solid var(--border)">
                            <strong style="font-size:13px">Notifications</strong>
                            <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0 text-muted" style="font-size:11px">Mark all read</button>
                            </form>
                        </div>
                        <div id="notifList" style="max-height:320px; overflow-y:auto">
                            <div class="text-center p-3 text-muted" style="font-size:12px">Loading...</div>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="d-block text-center py-2 text-decoration-none" style="font-size:12px; border-top:1px solid var(--border); color:var(--primary)">
                            View all notifications
                        </a>
                    </div>
                </div>

                {{-- User menu --}}
                <div class="dropdown">
                    <a class="topbar-user" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="topbar-avatar">
                        <div class="topbar-user-info d-none d-md-block">
                            <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                            <div class="topbar-user-role">{{ auth()->user()->role_name }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted ms-1 d-none d-md-block" style="font-size:10px"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius:10px; border:1px solid var(--border); min-width:180px">
                        <li>
                            <div class="px-3 py-2" style="border-bottom:1px solid var(--border)">
                                <div style="font-weight:600;font-size:13px">{{ auth()->user()->name }}</div>
                                <div style="font-size:11px; color:var(--text-muted)">{{ auth()->user()->email }}</div>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="page-content">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius:10px">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius:10px">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Cart Offcanvas --}}
<div class="offcanvas offcanvas-end glass-cart border-0" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="width: 440px;">
    <div class="offcanvas-header glass-cart-header px-4 py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="glass-cart-icon">
                <i class="bi bi-cart3"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold m-0" id="cartOffcanvasLabel">Request Cart</h5>
                <small class="text-muted" style="font-size: 11px;">Review items in your order</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body glass-cart-body d-flex flex-column p-0">
        
        {{-- Cart Items List --}}
        <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column" id="cartItemsContainer">
            <div class="text-center my-auto px-3" id="emptyCartMessage">
                <div class="glass-empty-card">
                    <div class="glass-empty-icon">
                        <i class="bi bi-cart-x-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Your Cart is Empty</h5>
                    <p class="text-muted small mb-4">Explore our supply catalog and add items to your request list.</p>
                    <a href="{{ route('requests.create') }}" class="btn glass-btn-gradient rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-compass me-1"></i> Browse Supplies
                    </a>
                </div>
            </div>
            <div id="cartItemsList" class="d-flex flex-column gap-3"></div>
        </div>

        {{-- Checkout Action --}}
        <div class="glass-footer p-4" id="checkoutSection" style="display: none;">
            <a href="{{ route('requests.checkout') }}" class="btn glass-btn-gradient w-100 fw-bold py-2.5 rounded-pill shadow-sm" id="btnReviewRequest">
                Review & Confirm Request <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

    </div>
</div>

{{-- Mobile overlay --}}
<div class="d-md-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" id="sidebarOverlay" style="z-index:1039"></div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// --- Sidebar Toggle ---
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const overlay = document.getElementById('sidebarOverlay');

sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth < 768) {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('d-none');
    } else {
        sidebar.classList.toggle('collapsed');
        document.getElementById('mainArea').style.marginLeft =
            sidebar.classList.contains('collapsed') ? '68px' : '260px';
    }
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.add('d-none');
});

// --- Dark Mode Toggle ---
const dmToggle = document.getElementById('darkModeToggle');
dmToggle.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    dmToggle.querySelector('i').className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';

    fetch('/profile/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ _method: 'PUT', _theme: next })
    });

    // Save preference in session via cookie
    document.cookie = `theme=${next};path=/;max-age=31536000`;
});

// --- Notifications ---
function loadNotifications() {
    fetch('{{ route("notifications.count") }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifCount');
            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }).catch(() => {});
}
loadNotifications();
setInterval(loadNotifications, 30000);

// Load notifications dropdown
document.getElementById('notifBtn').closest('.dropdown').addEventListener('show.bs.dropdown', function () {
    fetch('{{ route("notifications.index") }}?ajax=1')
        .then(r => r.text())
        .then(html => {
            document.getElementById('notifList').innerHTML = html || '<div class="text-center p-3 text-muted" style="font-size:12px">No notifications</div>';
        });
});

// --- DataTables default init ---
$(document).ready(function() {
    $('[data-datatable]').DataTable({
        pageLength: 15,
        responsive: true,
        language: { search: '', searchPlaceholder: 'Search...' }
    });
});

// --- Confirm Delete ---
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const msg = this.dataset.confirm || 'Are you sure?';
        Swal.fire({ title: msg, icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#EF4444', confirmButtonText: 'Yes, proceed' })
            .then(r => { if (r.isConfirmed) this.closest('form').submit(); });
    });
});
</script>
@stack('scripts')
<script>
// --- Global Cart System ---
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
                div.className = 'glass-cart-item d-flex align-items-center p-3';
                div.innerHTML = `
                    <div class="glass-item-icon text-primary rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:46px; height:46px; font-size:1.35rem;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-truncate" style="font-size:13.5px;" title="${item.name}">${item.name}</div>
                        <div class="text-muted" style="font-size:11px;">Max Stock: ${item.stock} ${item.unit}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-2">
                        <input type="number" class="form-control form-control-sm text-center shadow-none" style="width: 58px; border-radius: 8px;" value="${item.quantity}" min="1" max="${item.stock}" onchange="updateCartItemQty(${item.id}, this.value)">
                        <button type="button" class="btn btn-sm btn-link text-danger p-1 text-decoration-none" onclick="removeFromCart(${item.id})" title="Remove item"><i class="bi bi-trash3-fill"></i></button>
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

// Page Loader Handlers
(function() {
    function hideLoader() {
        var loader = document.getElementById('pageLoader');
        if (loader) {
            setTimeout(function() { loader.classList.add('fade-out'); }, 80);
        }
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        hideLoader();
    } else {
        window.addEventListener('DOMContentLoaded', hideLoader);
        window.addEventListener('load', hideLoader);
    }

    // Safety fallback: auto-hide after 1.2s
    setTimeout(hideLoader, 1200);
})();

window.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    @if(session('success'))
        clearAllCartKeys();
    @endif
});

// Show loader on form submission
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'aiChatbotForm') return;
    const loader = document.getElementById('pageLoader');
    const subtext = document.getElementById('loaderSubtext');
    if (loader) {
        if (subtext) subtext.innerHTML = '<span>Processing request</span><span class="loader-dots"><span></span><span></span><span></span></span>';
        loader.classList.remove('fade-out');
    }
});

// Show loader on navigating link clicks
document.addEventListener('click', function(e) {
    const a = e.target.closest('a');
    if (a && a.href && !a.href.startsWith('javascript:') && !a.href.includes('#') && !a.target && a.origin === window.location.origin) {
        const loader = document.getElementById('pageLoader');
        const subtext = document.getElementById('loaderSubtext');
        if (loader) {
            if (subtext) subtext.innerHTML = '<span>Loading page</span><span class="loader-dots"><span></span><span></span><span></span></span>';
            loader.classList.remove('fade-out');
        }
    }
});

</script>
@include('partials.chatbot')
</body>
</html>
