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
            height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background: #34495e;
            color: white;
            padding: 1rem 0;
            overflow-y: auto;
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
            padding: 2rem;
            overflow-y: auto;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
    </style>
</head>
<body>
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
            <a href="{{ route('customers') }}" class="nav-item {{ request()->routeIs('customers') ? 'active' : '' }}">
                Customers
            </a>
            <a href="{{ route('transactions') }}" class="nav-item {{ request()->routeIs('transactions*') ? 'active' : '' }}">
                Transactions
            </a>
            <a href="{{ route('suppliers') }}" class="nav-item {{ request()->routeIs('suppliers') ? 'active' : '' }}">
                Suppliers
            </a>
            <a href="{{ route('services') }}" class="nav-item {{ request()->routeIs('services') ? 'active' : '' }}">
                Services
            </a>
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
</body>
</html>