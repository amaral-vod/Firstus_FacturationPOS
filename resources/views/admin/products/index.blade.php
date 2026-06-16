@extends('layouts.app')

@section('page-title', '🏷️ Gestion des Produits')

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-header bg-white">
                <form class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="🔍 Rechercher..." value="{{ request('search') }}">
                    <button class="btn btn-primary">🔍</button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Produit</th><th>SKU</th><th>Prix</th><th>Coût</th><th>Stock</th><th>Min/Max</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}<br><small class="text-muted">{{ $product->category?->name }}</small></td>
                            <td>{{ $product->sku }}</td>
                            <td>
                                {{ number_format($product->price, 0, ',', ' ') }} FCFA
                                @if($product->isPromoActive())
                                    <br><small class="text-danger">🔥 {{ number_format($product->promo_price, 0, ',', ' ') }}</small>
                                @endif
                            </td>
                            <td>{{ number_format($product->cost ?? 0, 0, ',', ' ') }}</td>
                            <td>{{ $product->stock?->quantity ?? 0 }}</td>
                            <td><small>{{ $product->stock?->min_quantity ?? 5 }} / {{ $product->stock?->max_quantity ?: '—' }}</small></td>
                            <td>{!! $product->is_active ? '✅' : '🚫' !!}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit{{ $product->id }}">✏️</button>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $products->links() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern">
            <div class="card-header bg-primary text-white"><h6 class="mb-0">➕ Nouveau produit</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.products.store') }}" method="POST">
                    @csrf
                    <input type="text" name="name" class="form-control mb-2" placeholder="Nom" required>
                    <input type="text" name="sku" class="form-control mb-2" placeholder="SKU" required>
                    <select name="category_id" class="form-select mb-2">
                        <option value="">Catégorie</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                    <input type="number" name="price" class="form-control mb-2" placeholder="Prix vente" step="0.01" required>
                    <input type="number" name="cost" class="form-control mb-2" placeholder="Coût d'achat" step="0.01">
                    <input type="number" name="promo_price" class="form-control mb-2" placeholder="Prix promo" step="0.01">
                    <input type="number" name="initial_stock" class="form-control mb-2" placeholder="Stock initial" min="0">
                    <input type="number" name="min_quantity" class="form-control mb-2" placeholder="Stock minimum" min="0" value="5">
                    <button class="btn btn-primary w-100">➕ Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($products as $product)
<div class="modal fade" id="edit{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.products.update', $product) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">✏️ {{ $product->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control mb-2" value="{{ $product->name }}" required>
                    <input type="text" name="sku" class="form-control mb-2" value="{{ $product->sku }}" required>
                    <select name="category_id" class="form-select mb-2">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="price" class="form-control mb-2" value="{{ $product->price }}" step="0.01" required>
                    <input type="number" name="cost" class="form-control mb-2" value="{{ $product->cost }}" step="0.01" placeholder="Coût d'achat">
                    <input type="number" name="promo_price" class="form-control mb-2" value="{{ $product->promo_price }}" step="0.01" placeholder="Prix promo">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Stock min</label>
                            <input type="number" name="min_quantity" class="form-control" value="{{ $product->stock?->min_quantity ?? 5 }}" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Stock max (0 = illimité)</label>
                            <input type="number" name="max_quantity" class="form-control" value="{{ $product->stock?->max_quantity ?? 0 }}" min="0">
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $product->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
