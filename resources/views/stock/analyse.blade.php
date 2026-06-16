@extends('layouts.app')

@section('page-title', '📊 Analyse du stock')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Pilotage & valorisation</h5>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('fournisseurs.manage'))
        <form action="{{ route('stock.commandes.generate') }}" method="POST">
            @csrf
            <input type="hidden" name="site_id" value="{{ $siteId }}">
            <button class="btn btn-primary btn-sm">🛒 Générer commandes fournisseur</button>
        </form>
        @endif
        <a href="{{ route('stock.index', ['site_id' => $siteId]) }}" class="btn btn-outline-secondary btn-sm">← Stock</a>
    </div>
</div>

@include('stock._site_filter')

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Valeur totale stock</small><div class="fs-5 fw-bold">{{ number_format($stats['total_value'], 0, ',', ' ') }} FCFA</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Quantité totale</small><div class="fs-5 fw-bold">{{ number_format($stats['total_qty'], 0, ',', ' ') }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Ruptures / faible</small><div class="fs-5 fw-bold text-danger">{{ $stats['low_count'] }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Surstocks</small><div class="fs-5 fw-bold text-warning">{{ $stats['over_count'] }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">💰 Valorisation par catégorie</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Catégorie</th><th>Qté</th><th>Valeur</th></tr></thead>
                    <tbody>
                        @forelse($byCategory as $row)
                        <tr>
                            <td>{{ $row->category }}</td>
                            <td>{{ $row->total_qty }}</td>
                            <td>{{ number_format($row->total_value, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">⚠️ À réapprovisionner</h6></div>
            <div class="card-body p-0" style="max-height:280px;overflow:auto">
                <table class="table mb-0 table-sm">
                    <thead class="table-light"><tr><th>Produit</th><th>Qté</th><th>Min</th><th>Fournisseur</th></tr></thead>
                    <tbody>
                        @forelse($replenishment as $s)
                        <tr>
                            <td>{{ $s->product->name }}</td>
                            <td class="text-danger fw-bold">{{ $s->quantity }}</td>
                            <td>{{ $s->min_quantity }}</td>
                            <td>{{ $s->product->fournisseur?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">Stock OK</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">📦 Surstocks (rotation lente)</h6></div>
            <div class="card-body p-0" style="max-height:280px;overflow:auto">
                <table class="table mb-0 table-sm">
                    <thead class="table-light"><tr><th>Produit</th><th>Qté</th><th>Max</th><th>Vendu 90j</th></tr></thead>
                    <tbody>
                        @forelse($slowMovers as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->quantity }}</td>
                            <td>—</td>
                            <td>{{ $p->sold_qty }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">Aucun produit immobilisé détecté</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($overstock->isNotEmpty())
        <div class="card card-modern mt-3">
            <div class="card-header bg-warning"><h6 class="mb-0">📈 Au-delà du stock max</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0 table-sm">
                    @foreach($overstock as $s)
                    <tr><td>{{ $s->product->name }}</td><td>{{ $s->quantity }} / max {{ $s->max_quantity }}</td></tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">🔄 Rotation (30 jours)</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0 table-sm">
                    <thead class="table-light"><tr><th>Produit</th><th>Vendu</th><th>Stock</th><th>Jours couverture</th></tr></thead>
                    <tbody>
                        @forelse($rotation as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->sold_qty }}</td>
                            <td>{{ $p->quantity }}</td>
                            <td>{{ $p->coverage_days ?? '—' }} j</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">Pas assez de ventes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">📉 Pertes inventaire (écarts négatifs)</h6></div>
            <div class="card-body p-0" style="max-height:260px;overflow:auto">
                @forelse($losses as $line)
                <div class="p-2 border-bottom small">
                    <strong>{{ $line->product->name }}</strong> — {{ $line->variance_qty }} unités
                    ({{ number_format(abs($line->variance_value), 0, ',', ' ') }} FCFA)
                    <br><span class="text-muted">{{ $line->session->reference }} — {{ $line->reasonLabel() }}</span>
                </div>
                @empty
                <p class="text-muted p-3 mb-0">Aucune perte enregistrée via inventaire.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">📜 Mouvements (30 jours)</h6></div>
            <div class="card-body">
                <p class="mb-1">Entrées : <strong>{{ $movementStats['entrees'] }}</strong></p>
                <p class="mb-1">Sorties : <strong>{{ $movementStats['sorties'] }}</strong></p>
                <p class="mb-1">Inventaires : <strong>{{ $movementStats['inventaires'] }}</strong></p>
                <p class="mb-0">Total opérations : <strong>{{ $movementStats['total_movements'] }}</strong></p>
                <a href="{{ route('stock.mouvements') }}" class="btn btn-sm btn-outline-primary mt-2">Voir le détail</a>
            </div>
        </div>
    </div>
</div>
@endsection
