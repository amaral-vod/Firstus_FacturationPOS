@extends('layouts.app')

@section('page-title', '➕ Nouvel inventaire')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h5 class="mb-0">Créer une session d'inventaire</h5></div>
            <div class="card-body">
                <form action="{{ route('stock.inventories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Date de l'inventaire</label>
                        <input type="date" name="inventory_date" class="form-control" value="{{ old('inventory_date', today()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Magasin / dépôt</label>
                        <select name="site_id" class="form-select" required>
                            @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(old('site_id', $defaultSiteId) == $site->id)>
                                {{ $site->name }} ({{ $site->city }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Ex. inventaire trimestriel, zone A…">{{ old('notes') }}</textarea>
                    </div>
                    <p class="small text-muted">
                        Le système charge tous les produits actifs avec le stock théorique actuel.
                        Vous saisirez ensuite les quantités comptées physiquement.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('stock.inventories.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        <button class="btn btn-primary">Créer et saisir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
