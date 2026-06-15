@extends('layouts.app')

@section('page-title', '📈 Rapports & Statistiques')

@section('content')
<div class="mb-4">
    <div class="btn-group">
        <a href="?periode=jour" class="btn btn-{{ $periode === 'jour' ? 'primary' : 'outline-primary' }}">📅 Jour</a>
        <a href="?periode=mois" class="btn btn-{{ $periode === 'mois' ? 'primary' : 'outline-primary' }}">📆 Mois</a>
        <a href="?periode=annee" class="btn btn-{{ $periode === 'annee' ? 'primary' : 'outline-primary' }}">🗓️ Année</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body text-center py-4">
                <h2>{{ number_format($ca, 0, ',', ' ') }} FCFA</h2>
                <p class="mb-0">💰 Chiffre d'affaires</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card bg-success text-white">
            <div class="card-body text-center py-4">
                <h2>{{ $nbVentes }}</h2>
                <p class="mb-0">🧾 Nombre de ventes</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">🏆 Produits les plus vendus</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Produit</th><th>Qté</th><th>CA</th></tr></thead>
                    <tbody>
                        @foreach($topProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->qty }}</td>
                            <td>{{ number_format($p->revenue, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">📦 État du stock</h6></div>
            <div class="card-body p-0" style="max-height:300px;overflow:auto">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Produit</th><th>Qté</th><th></th></tr></thead>
                    <tbody>
                        @foreach($etatStock as $s)
                        <tr>
                            <td>{{ $s->product->name }}</td>
                            <td>{{ $s->quantity }}</td>
                            <td>{!! $s->isLow() ? '<span class="badge bg-danger">⚠️</span>' : '<span class="badge bg-success">✅</span>' !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">↩️ Rapport des retours ({{ $retours->count() }})</h6></div>
            <div class="card-body p-0" style="max-height:250px;overflow:auto">
                @forelse($retours as $r)
                <div class="p-2 border-bottom small">
                    <strong>{{ $r->numero_retour }}</strong> — {{ number_format($r->montant_rembourse, 0, ',', ' ') }} FCFA
                    <br><span class="text-muted">{{ $r->created_at->format('d/m/Y') }} par {{ $r->user->name }}</span>
                </div>
                @empty
                <p class="text-muted p-3 mb-0">Aucun retour</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">🚫 Rapport des annulations ({{ $annulations->count() }})</h6></div>
            <div class="card-body p-0" style="max-height:250px;overflow:auto">
                @forelse($annulations as $a)
                <div class="p-2 border-bottom small">
                    <strong>{{ $a->vente->numero_facture }}</strong>
                    <br><span class="text-muted">{{ Str::limit($a->motif, 60) }}</span>
                </div>
                @empty
                <p class="text-muted p-3 mb-0">Aucune annulation</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
