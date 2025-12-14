<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Motorshop POS System')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .top-nav {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .top-nav h1 {
            font-size: 1.5rem;
        }

        .user-info {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background: #34495e;
            color: white;
            padding: 1rem 0;
            overflow-y: auto;
            /* keep sidebar visible below the sticky top bar */
            position: sticky;
            top: 60px; /* matches top-nav height */
            height: calc(100vh - 60px);
        }

        .nav-item {
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: background 0.3s;
            border-left: 3px solid transparent;
            display: block;
            color: white;
            text-decoration: none;
        }

        .nav-item:hover {
            background: #2c3e50;
            border-left-color: #3498db;
            color: white;
        }

        .nav-item.active {
            background: #2c3e50;
            border-left-color: #3498db;
        }

        .main-content {
            flex: 1;
            padding: 0.75rem;
            overflow: hidden; /* children will scroll individually */
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        /* Make card bodies scrollable to keep tabs fit to screen */
        .card .card-body, .card > div:not(.card-header) {
            overflow: auto;
            max-height: calc(100vh - 180px);
        }

        /* Remove underlines for most links and keep clear hover state */
        a {
            text-decoration: none;
            color: inherit;
        }
        a:hover { color: #3498db; text-decoration: underline; }

        .card-header {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .form-hint {
            display: block;
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #0ad100ff; /* solid color instead of gradient */
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card.green {
            background: #0097d8ff; 
        }

        .stat-card.blue {
            background: #4facfe; 
        }

        .stat-card.orange {
            background: #d40000ff; 
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Compact pagination styles for custom UI */
        .pagination {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pagination .page-item {
            margin: 0;
        }

        .pagination .page-link {
            display: inline-block;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            background: #ffffff;
            color: #2c3e50;
            text-decoration: none;
            font-size: 0.85rem;
            min-width: 30px;
            text-align: center;
        }

        .pagination .page-link:hover {
            background: #f0f0f0;
            color: #2c3e50;
        }

        .pagination .active .page-link, .pagination .page-link[aria-current="page"] {
            background: #3498db;
            color: #fff;
            border-color: #3498db;
        }

        .pagination .disabled .page-link {
            color: #bdbdbd;
            pointer-events: none;
            background: #fff;
        }

        /* very small pagination variant */
        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            min-width: 26px;
        }

        /* Sortable table header styles */
        th.sortable {
            position: relative;
            padding-right: 2.25rem;
            cursor: pointer;
            user-select: none;
        }
        th.sortable .sort-icon {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            flex-direction: column;
            gap: 0.08rem;
            font-size: 0.65rem;
            color: #7f8c8d;
            line-height: 0.75rem;
            text-align: center;
        }
        th.sortable .sort-icon .up, th.sortable .sort-icon .down { opacity: 0.35; }
        th.sortable.asc .sort-icon .up { opacity: 1; color: #3498db; }
        th.sortable.desc .sort-icon .down { opacity: 1; color: #e67e22; }
    </style>
</head>
<body>
    @php
        $isAuthRoute = request()->routeIs('login') || request()->routeIs('password.*');
    @endphp

    @if (!$isAuthRoute)
    <!-- Top Navigation -->
    <div class="top-nav">
        <h1>GEARPOINT</h1>
        <div class="user-info">
            @if(auth()->check())
                <span>{{ auth()->user()->name }}</span>
                <span>|</span>
                <span>{{ date('M d, Y') }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline-block; margin-left: 1rem;">
                    @csrf
                    <button class="btn btn-danger" type="submit">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            @endif
        </div>
    </div>

    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('pos') }}" class="nav-item {{ request()->routeIs('pos') ? 'active' : '' }}">
                Point of Sale
            </a>
            <a href="{{ route('inventory') }}" class="nav-item {{ request()->routeIs('inventory') ? 'active' : '' }}">
                Inventory
            </a>
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <a href="{{ route('customers') }}" class="nav-item {{ request()->routeIs('customers') ? 'active' : '' }}">
                Customers
            </a>
            @endif
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <a href="{{ route('transactions') }}" class="nav-item {{ request()->routeIs('transactions*') ? 'active' : '' }}">
                Transactions
            </a>
            @endif
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <a href="{{ route('suppliers') }}" class="nav-item {{ request()->routeIs('suppliers') ? 'active' : '' }}">
                Suppliers
            </a>
            @endif
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <a href="{{ route('services') }}" class="nav-item {{ request()->routeIs('services') ? 'active' : '' }}">
                Services
            </a>
            @endif
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <a href="{{ route('reports') }}" class="nav-item {{ request()->routeIs('reports') ? 'active' : '' }}">
                Reports
            </a>
            @endif
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users*') ? 'active' : '' }}">
                    Users
                </a>
            @endif
        </div>

        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    @else
        <div style="padding: 2rem;">@yield('content')</div>
    @endif
</body>
<script>
function initTableSorters() {
    function parseValue(type, val) {
        if (type === 'number') return parseFloat(val.replace(/[^0-9.-]+/g, '')) || 0;
        if (type === 'date') return Date.parse(val) || 0;
        if (type === 'string') return val.trim().toLowerCase();
        // default: try to guess number
        const num = parseFloat(val.replace(/[^0-9.-]+/g, ''));
        if (!isNaN(num)) return num;
        return val.trim().toLowerCase();
    }

    function getTypeFromHeader(th) {
        const t = th.getAttribute('data-type');
        if (t) return t;
        return 'string';
    }

    function createSortIcon() {
        const span = document.createElement('span');
        span.className = 'sort-icon';
        const up = document.createElement('span'); up.className = 'up'; up.textContent = '▲';
        const down = document.createElement('span'); down.className = 'down'; down.textContent = '▼';
        span.appendChild(up);
        span.appendChild(down);
        return span;
    }

    document.querySelectorAll('table').forEach(table => {
        const headers = table.querySelectorAll('th');
        headers.forEach((th, idx) => {
            if (th.classList.contains('sortable')) {
                // add icon if not present
                if (!th.querySelector('.sort-icon')) th.appendChild(createSortIcon());
                th.setAttribute('title','Click to sort');
                th.addEventListener('click', () => {
                    const type = getTypeFromHeader(th);
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const currentDir = th.classList.contains('asc') ? 'asc' : (th.classList.contains('desc') ? 'desc' : null);
                    // reset other headers' classes
                    headers.forEach(h => h.classList.remove('asc', 'desc'));
                    const dir = currentDir === 'asc' ? 'desc' : 'asc';
                    th.classList.add(dir);
                    rows.sort((a, b) => {
                        const aVal = parseValue(type, (a.cells[idx] ? a.cells[idx].textContent : '').trim());
                        const bVal = parseValue(type, (b.cells[idx] ? b.cells[idx].textContent : '').trim());
                        if (typeof aVal === 'number' && typeof bVal === 'number') return dir === 'asc' ? aVal - bVal : bVal - aVal;
                        if (aVal < bVal) return dir === 'asc' ? -1 : 1;
                        if (aVal > bVal) return dir === 'asc' ? 1 : -1;
                        return 0;
                    });
                    // reattach rows
                    rows.forEach(r => tbody.appendChild(r));
                });
            }
        });
    });
}
document.addEventListener('DOMContentLoaded', function () { initTableSorters(); });
</script>
</html>