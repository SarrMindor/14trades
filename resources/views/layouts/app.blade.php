<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', '14Trades'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Ton CSS personnalisé -->
    <style>
        :root {
            --bg: #0B1220;
            --panel: #121B2F;
            --text: #FFFFFF;
            --muted: #C7CFDD;
            --muted2: #8FA3C8;
            --gold: #FFD700;
            --gold-light: #FFE26A;
            --blue: #1A237E;
            --line: rgba(199, 207, 221, .14);
            --shadow: 0 18px 45px rgba(0,0,0,.35);
            --radius: 16px;
            --radius-lg: 22px;
            --success: #2ecc71;
            --error: #e74c3c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 600px at 75% 10%, rgba(26,35,126,.25), transparent 55%),
                radial-gradient(900px 500px at 15% 20%, rgba(255,215,0,.08), transparent 60%),
                linear-gradient(180deg, #050914, var(--bg) 35%, #050914 120%);
            min-height: 100vh;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Container personnalisé */
        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            background: rgba(5,9,20,.85);
            border-bottom: 1px solid var(--line);
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: .2px;
            font-size: 18px;
            color: var(--text);
            cursor: pointer;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 99px;
            background: var(--gold);
            box-shadow: 0 0 0 6px rgba(255,215,0,.10);
        }

        /* Dashboard Layout */
        .dashboard-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: rgba(18,27,47,.85);
            border-right: 1px solid var(--line);
            padding: 24px 0;
            position: sticky;
            top: 70px;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .sidebar-heading {
            padding: 0 24px;
            margin-bottom: 24px;
        }

        .sidebar-heading h2 {
            color: var(--gold);
            font-size: 18px;
            font-weight: 700;
        }

        .nav-links-sidebar {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 0 16px;
        }

        .nav-link-sidebar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--muted);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .nav-link-sidebar:hover {
            background: rgba(255,215,0,.05);
            color: var(--gold);
        }

        .nav-link-sidebar.active {
            background: rgba(255,215,0,.1);
            color: var(--gold);
            border-left: 3px solid var(--gold);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Cards */
        .card {
            background: linear-gradient(180deg, rgba(18,27,47,.9), rgba(18,27,47,.62));
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,.25);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(180deg, rgba(18,27,47,.9), rgba(18,27,47,.62));
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,215,0,.3);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Buttons */
        .btn {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(180deg, var(--gold-light), var(--gold));
            color: var(--bg);
            box-shadow: 0 4px 15px rgba(255,215,0,.2);
        }

        .btn-primary:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(255,215,0,.3);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,215,0,.55);
            color: var(--gold);
        }

        .btn-secondary:hover {
            border-color: rgba(255,215,0,.95);
            background: rgba(255,215,0,.05);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(46,204,113,.1);
            border-color: rgba(46,204,113,.3);
            color: var(--success);
        }

        .alert-warning {
            background: rgba(255,215,0,.1);
            border-color: rgba(255,215,0,.3);
            color: var(--gold);
        }

        .alert-info {
            background: rgba(52,152,219,.1);
            border-color: rgba(52,152,219,.3);
            color: #3498db;
        }

        .alert-danger {
            background: rgba(231,76,60,.1);
            border-color: rgba(231,76,60,.3);
            color: var(--error);
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: rgba(18,27,47,.8);
            color: var(--muted);
            font-weight: 600;
            text-align: left;
            padding: 16px;
            border-bottom: 1px solid var(--line);
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid var(--line);
            color: var(--text);
        }

        .table tr:hover {
            background: rgba(255,215,0,.03);
        }

        /* Form elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(5,9,20,.6);
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--text);
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(255,215,0,.5);
            box-shadow: 0 0 0 3px rgba(255,215,0,.1);
        }

        /* User menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--bg);
            font-size: 16px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            color: var(--text);
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            color: var(--gold);
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .nav-links-sidebar {
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 10px;
            }

            .nav-link-sidebar {
                white-space: nowrap;
            }

            .main-content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .container-custom {
                padding: 0 16px;
            }
        }

        @media (max-width: 480px) {
            .nav {
                flex-direction: column;
                gap: 12px;
            }

            .user-menu {
                width: 100%;
                justify-content: center;
            }

            .main-content {
                padding: 16px;
            }

            .card {
                padding: 16px;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @stack('scripts')
</head>
<body>
<!-- Header -->
<header>
    <div class="container-custom">
        <div class="nav">
            <!-- Logo -->
            <div class="brand">
                <span class="dot"></span>
                <span>14TRADES</span>
            </div>

            <!-- Navigation -->
            <nav class="nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('client.accounts.index') }}" class="{{ request()->routeIs('client.accounts*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i> Comptes
                    </a>
                    <a href="{{ route('client.trades') }}" class="{{ request()->routeIs('client.trades*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i> Trades
                    </a>
                    <a href="{{ route('client.payments.checkout') }}">Effectuer un paiement
                        <i class="bi bi-credit-card"></i> Paiement
                    </a>
                @endauth
            </nav>

            <!-- User Menu / Auth Links -->
            <div class="user-menu">
                @auth
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->role === 'admin' ? 'Administrateur' : 'Client' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Connexion
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Inscription
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Layout -->
@auth
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-heading">
                <h2>Navigation</h2>
            </div>
            <div class="nav-links-sidebar">
                <a href="{{ route('dashboard') }}" class="nav-link-sidebar {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="{{ route('client.accounts.index') }}" class="nav-link-sidebar {{ request()->routeIs('client.accounts*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Comptes MT5</span>
                </a>
                <a href="{{ route('client.trades') }}" class="nav-link-sidebar {{ request()->routeIs('client.trades*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>
                    <span>Trades</span>
                </a>
                <!-- Dans la sidebar -->
                <a href="{{ route('client.performance') }}" class="nav-link-sidebar {{ request()->routeIs('client.performance*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i>
                    <span>Performance</span>
                </a>
                <a href="{{ route('client.analytics') }}" class="nav-link-sidebar {{ request()->routeIs('client.analytics*') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i>
                    <span>Analytiques</span>
                </a>
                <a href="{{ route('client.payment') }}" class="nav-link-sidebar {{ request()->routeIs('client.payment*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span>Paiements</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-link-sidebar {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Mon Profil</span>
                </a>
                @if(auth()->user()->role === 'admin')
                    <div class="sidebar-heading mt-4">
                        <h2>Administration</h2>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link-sidebar {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="nav-link-sidebar">
                        <i class="bi bi-people"></i>
                        <span>Utilisateurs</span>
                    </a>
                @endif
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>
    </div>
@else
    <!-- Content for non-authenticated users -->
    <main>
        @yield('content')
    </main>
@endauth

<!-- Footer -->
<footer style="background: rgba(5,9,20,.35); border-top: 1px solid var(--line); padding: 40px 0;">
    <div class="container-custom">
        <div class="footer-content">
            <div>
                <div class="brand" style="margin-bottom: 20px;">
                    <span class="dot"></span>
                    <span>14TRADES</span>
                </div>
                <p class="disclaimer" style="color: var(--muted2); font-size: 12.5px; line-height: 1.6; max-width: 600px;">
                    Trading involves significant risk of loss and is not suitable for all investors. Past performance is not indicative of future results. The information on this website is for educational purposes only.
                </p>
            </div>
            <div class="footer-links" style="display: flex; gap: 20px; justify-content: flex-end; color: var(--muted2); font-size: 14px;">
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">Contact</a>
                <a href="#">Support</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Additional Scripts -->
@stack('footer-scripts')
</body>
</html>
