@extends('layouts.app')

@section('page-title', '📦 Gestion du Stock')

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('import_errors') && count(session('import_errors')))
<div class="alert alert-warning">
    <strong>Détails import :</strong>
    <ul class="mb-0 mt-2">
        @foreach(array_slice(session('import_errors'), 0, 10) as $err)
        <li>{{ $err }}</li>
        @endforeach
        @if(count(session('import_errors')) > 10)
        <li>… et {{ count(session('import_errors')) - 10 }} autre(s) erreur(s)</li>
        @endif
    </ul>
</div>
@endif
@if($lowCount > 0)
<div class="alert alert-warning">⚠️ <strong>{{ $lowCount }}</strong> produit(s) en stock faible !
    <a href="?alerte=1&site_id={{ $siteId }}" class="alert-link">Voir les alertes</a>
    <a href="{{ route('stock.analyse', ['site_id' => $siteId]) }}" class="alert-link ms-2">Analyse complète</a>
</div>
@endif

@include('stock._site_filter')

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Valeur stock</small><div class="fw-bold">{{ number_format($stats['total_value'] ?? 0, 0, ',', ' ') }} FCFA</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Produits</small><div class="fw-bold">{{ $stats['product_count'] ?? 0 }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Ruptures</small><div class="fw-bold text-danger">{{ $stats['low_count'] ?? $lowCount }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Surstocks</small><div class="fw-bold text-warning">{{ $stats['over_count'] ?? 0 }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0">📋 État du stock</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('stock.analyse', ['site_id' => $siteId]) }}" class="btn btn-sm btn-outline-primary">📊 Analyse</a>
                    <a href="{{ route('stock.inventories.index') }}" class="btn btn-sm btn-outline-primary">📝 Inventaires</a>
                    <a href="{{ route('stock.mouvements') }}" class="btn btn-sm btn-outline-primary">📜 Mouvements</a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Produit</th><th>Catégorie</th><th>Quantité</th><th>Min</th><th>Max</th><th>Valeur</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                        <tr>
                            <td>{{ $stock->product->name }}</td>
                            <td>{{ $stock->product->category?->name ?? '—' }}</td>
                            <td><strong>{{ $stock->quantity }}</strong></td>
                            <td>{{ $stock->min_quantity }}</td>
                            <td>{{ $stock->max_quantity > 0 ? $stock->max_quantity : '—' }}</td>
                            <td>{{ number_format($stock->valuation(), 0, ',', ' ') }}</td>
                            <td>
                                @if($stock->isLow())
                                    <span class="badge bg-danger">⚠️ Faible</span>
                                @elseif($stock->isOverstock())
                                    <span class="badge bg-warning text-dark">📦 Surstock</span>
                                @else
                                    <span class="badge bg-success">✅ OK</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $stocks->links() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern mb-3">
            <div class="card-header bg-success text-white"><h6 class="mb-0">📥 Entrée de stock</h6></div>
            <div class="card-body">
                <form action="{{ route('stock.entree') }}" method="POST">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $siteId }}">
                    <select name="product_id" class="form-select mb-2" required>
                        <option value="">Produit...</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                    <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantité" min="1" required>
                    <input type="text" name="notes" class="form-control mb-2" placeholder="Notes">
                    <button class="btn btn-success w-100">📥 Enregistrer</button>
                </form>
            </div>
        </div>
        <div class="card card-modern mb-3">
            <div class="card-header bg-warning"><h6 class="mb-0">📤 Sortie de stock</h6></div>
            <div class="card-body">
                <form action="{{ route('stock.sortie') }}" method="POST">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $siteId }}">
                    <select name="product_id" class="form-select mb-2" required>
                        <option value="">Produit...</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                    <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantité" min="1" required>
                    <input type="text" name="notes" class="form-control mb-2" placeholder="Notes">
                    <button class="btn btn-warning w-100">📤 Enregistrer</button>
                </form>
            </div>
        </div>
        <div class="card card-modern">
            <div class="card-header bg-info text-white"><h6 class="mb-0">📝 Inventaire</h6></div>
            <div class="card-body">
                <form action="{{ route('stock.inventaire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $siteId }}">
                    <select name="product_id" class="form-select mb-2" required>
                        <option value="">Produit...</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                    <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantité réelle" min="0" required>
                    <input type="text" name="notes" class="form-control mb-2" placeholder="Notes">
                    <button class="btn btn-info w-100 text-white">📝 Mettre à jour</button>
                </form>
            </div>
        </div>
        @if(auth()->user()->hasPermission('stock.import'))
        <div class="card card-modern mt-3">
            <div class="card-header bg-primary text-white"><h6 class="mb-0">📥 Import stock (CSV / Excel)</h6></div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    Colonnes attendues : <strong>ID_Produit</strong>, <strong>Nom du Produit</strong>, <strong>Stock</strong>
                </p>
                <form action="{{ route('stock.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $siteId }}">
                    <input type="file" name="file" class="form-control mb-2" accept=".csv,.txt,.xlsx" required>
                    <button class="btn btn-primary w-100">📥 Importer et mettre à jour</button>
                </form>
                <p class="small text-muted mt-2 mb-0">
                    L’ID peut être l’ID interne, le code SKU ou le code-barres. Le stock est remplacé par la valeur du fichier.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
