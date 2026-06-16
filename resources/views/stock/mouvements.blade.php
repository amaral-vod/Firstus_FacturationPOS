@extends('layouts.app')

@section('page-title', '📜 Mouvements de Stock')

@section('content')
<div class="card card-modern mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Du</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Au</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach(['entree'=>'Entrée','sortie'=>'Sortie','inventaire'=>'Inventaire','retour'=>'Retour','annulation'=>'Annulation'] as $k=>$v)
                    <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Produit</label>
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('stock.mouvements') }}" class="btn btn-outline-secondary btn-sm w-100">↺</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card card-modern p-2 px-3"><small>Entrées</small><strong>{{ $movementStats['entrees'] }}</strong></div></div>
    <div class="col-md-3"><div class="card card-modern p-2 px-3"><small>Sorties</small><strong>{{ $movementStats['sorties'] }}</strong></div></div>
    <div class="col-md-3"><div class="card card-modern p-2 px-3"><small>Inventaires</small><strong>{{ $movementStats['inventaires'] }}</strong></div></div>
    <div class="col-md-3"><div class="card card-modern p-2 px-3"><small>Opérations</small><strong>{{ $movementStats['total_movements'] }}</strong></div></div>
</div>

<div class="card card-modern">
    <div class="card-header bg-white d-flex justify-content-between">
        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
        <a href="{{ route('stock.analyse') }}" class="btn btn-sm btn-outline-primary">📊 Analyse</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr><th>Date</th><th>Produit</th><th>Type</th><th>Qté</th><th>Avant</th><th>Après</th><th>Écart</th><th>Utilisateur</th><th>Réf.</th><th>Notes</th></tr>
            </thead>
            <tbody>
                @foreach($mouvements as $m)
                @php $ecart = $m->quantity_after - $m->quantity_before; @endphp
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->product->name }}</td>
                    <td>
                        @switch($m->type)
                            @case('entree') <span class="badge bg-success">📥 Entrée</span> @break
                            @case('sortie') <span class="badge bg-warning">📤 Sortie</span> @break
                            @case('inventaire') <span class="badge bg-info">📝 Inventaire</span> @break
                            @case('retour') <span class="badge bg-primary">↩️ Retour</span> @break
                            @case('annulation') <span class="badge bg-danger">🚫 Annulation</span> @break
                        @endswitch
                    </td>
                    <td>{{ $m->quantity }}</td>
                    <td>{{ $m->quantity_before }}</td>
                    <td>{{ $m->quantity_after }}</td>
                    <td class="{{ $ecart > 0 ? 'text-success' : ($ecart < 0 ? 'text-danger' : '') }}">{{ $ecart >= 0 ? '+' : '' }}{{ $ecart }}</td>
                    <td>{{ $m->user->name }}</td>
                    <td><small>{{ $m->reference }}</small></td>
                    <td><small>{{ Str::limit($m->notes, 40) }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $mouvements->links() }}</div>
</div>
@endsection
