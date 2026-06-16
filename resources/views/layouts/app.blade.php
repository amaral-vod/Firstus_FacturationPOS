<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Firstus POS') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --sidebar-width: 260px;
            --bg: #f1f5f9;
        }
        body { background: var(--bg); font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            position: fixed;
            left: 0; top: 0;
            z-index: 1000;
            transition: transform .3s;
        }
        .sidebar .brand { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .75rem 1.5rem;
            border-radius: 0;
            transition: all .2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            position: sticky; top: 0; z-index: 100;
        }
        .content-area { padding: 1.5rem; }
        .stat-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .card-modern {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .badge-role { font-size: .75rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        @yield('styles')
    </style>
</head>
<body>
    @auth
    <nav class="sidebar" id="sidebar">
        <div class="brand text-white">
            <h5 class="mb-0">🛒 Firstus POS</h5>
            <small class="text-white-50">Gestion Commerciale</small>
        </div>
        <ul class="nav flex-column py-3" style="max-height:calc(100vh - 80px);overflow-y:auto">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">📊 Tableau de bord</a></li>

            <li class="nav-item mt-2"><small class="text-white-50 px-3">💼 Commercial</small></li>
            @if(auth()->user()->hasPermission('vente.create'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('caisse.index') ? 'active' : '' }}" href="{{ route('caisse.index') }}">💰 POS</a></li>@endif
            @if(auth()->user()->hasPermission('caisse.session'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('caisse.sessions.*') ? 'active' : '' }}" href="{{ route('caisse.sessions.index') }}">🏧 Ouverture/Fermeture</a></li>@endif
            @if(auth()->user()->hasPermission('vente.view'))<li class="nav-item"><a class="nav-link" href="{{ route('caisse.historique') }}">🧾 Ventes</a></li>@endif
            @if(auth()->user()->hasPermission('facturation.view'))
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('facturation.index') || request()->routeIs('facturation.show') ? 'active' : '' }}" href="{{ route('facturation.index') }}">📄 Facturation</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('facturation.create') ? 'active' : '' }}" href="{{ route('facturation.create') }}">📋 Proforma</a></li>
            @endif
            @if(auth()->user()->hasPermission('clients.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">🤝 Clients</a></li>@endif
            @if(auth()->user()->hasPermission('credits.manage'))<li class="nav-item"><a class="nav-link" href="{{ route('clients.credits') }}">💳 Crédits</a></li>@endif

            <li class="nav-item mt-2"><small class="text-white-50 px-3">📦 Stocks</small></li>
            @if(auth()->user()->hasPermission('stock.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('stock.index') ? 'active' : '' }}" href="{{ route('stock.index') }}">📋 Stock</a></li>@endif
            @if(auth()->user()->hasPermission('stock.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('stock.analyse') ? 'active' : '' }}" href="{{ route('stock.analyse') }}">📊 Analyse stock</a></li>@endif
            @if(auth()->user()->hasPermission('stock.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('stock.inventories.*') ? 'active' : '' }}" href="{{ route('stock.inventories.index') }}">📝 Inventaires</a></li>@endif
            @if(auth()->user()->hasPermission('fournisseurs.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('stock.commandes.*') ? 'active' : '' }}" href="{{ route('stock.commandes.index') }}">🛒 Commandes fourn.</a></li>@endif
            @if(auth()->user()->hasPermission('products.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">🏷️ Produits</a></li>@endif
            @if(auth()->user()->hasPermission('categories.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">📂 Catégories</a></li>@endif
            @if(auth()->user()->hasPermission('fournisseurs.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}" href="{{ route('fournisseurs.index') }}">🏭 Fournisseurs</a></li>@endif

            <li class="nav-item mt-2"><small class="text-white-50 px-3">↩️ Retours</small></li>
            @if(auth()->user()->hasPermission('retour.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('retours.*') ? 'active' : '' }}" href="{{ route('retours.index') }}">↩️ Retours</a></li>@endif
            @if(auth()->user()->hasPermission('annulation.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('annulations.*') ? 'active' : '' }}" href="{{ route('annulations.index') }}">🚫 Annulations</a></li>@endif

            <li class="nav-item mt-2"><small class="text-white-50 px-3">📈 Analyse</small></li>
            @if(auth()->user()->hasPermission('rapports.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}" href="{{ route('rapports.index') }}">📈 Rapports & BI</a></li>@endif
            @if(auth()->user()->hasPermission('journal.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}" href="{{ route('journal.index') }}">📜 Audit Trail</a></li>@endif
            @if(auth()->user()->hasPermission('notifications.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">🔔 Notifications</a></li>@endif

            <li class="nav-item mt-2"><small class="text-white-50 px-3">⚙️ Administration</small></li>
            @if(auth()->user()->hasPermission('users.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">👥 Utilisateurs</a></li>@endif
            @if(auth()->user()->hasPermission('roles.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">🔐 Rôles</a></li>@endif
            @if(auth()->user()->hasPermission('permissions.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">🔑 Permissions</a></li>@endif
            @if(auth()->user()->hasPermission('groups.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}" href="{{ route('admin.groups.index') }}">👥 Groupes</a></li>@endif
            @if(auth()->user()->hasPermission('login_history.view'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}" href="{{ route('admin.login-history.index') }}">📜 Connexions</a></li>@endif
            @if(auth()->user()->hasPermission('security.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}" href="{{ route('security.index') }}">🛡️ Sécurité</a></li>@endif
            @if(auth()->user()->hasPermission('settings.manage'))<li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">⚙️ Paramètres</a></li>@endif
        </ul>
    </nav>
  @endauth

    <div class="main-content">
        @auth
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">☰</button>
                <span class="ms-2 fw-semibold">@yield('page-title', 'Tableau de bord')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary badge-role">{{ auth()->user()->role?->name ?? 'Sans rôle' }}</span>
                <span class="text-muted">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">🚪 Déconnexion</button>
                </form>
            </div>
        </div>
        @endauth

        <div class="@auth content-area @else container py-5 @endauth">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
