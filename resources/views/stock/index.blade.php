@extends('layouts.app')

@section('page-title', '📦 Gestion du Stock')

@section('content')
@if($lowCount > 0)
<div class="alert alert-warning">⚠️ <strong>{{ $lowCount }}</strong> produit(s) en stock faible !
    <a href="?alerte=1" class="alert-link">Voir les alertes</a>
</div>
@endif

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0">📋 État du stock</h5>
                <a href="{{ route('stock.mouvements') }}" class="btn btn-sm btn-outline-primary">📜 Mouvements</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Produit</th><th>Catégorie</th><th>Quantité</th><th>Min</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                        <tr>
                            <td>{{ $stock->product->name }}</td>
                            <td>{{ $stock->product->category?->name ?? '—' }}</td>
                            <td><strong>{{ $stock->quantity }}</strong></td>
                            <td>{{ $stock->min_quantity }}</td>
                            <td>
                                @if($stock->isLow())
                                    <span class="badge bg-danger">⚠️ Faible</span>
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
    </div>
</div>
@endsection
