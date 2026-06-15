@extends('layouts.app')
@section('page-title', '📊 Tableau de bord')
@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['💰 CA Jour', number_format($stats['ca_jour'],0,',',' ').' FCFA', 'primary'],
        ['📈 Bénéfice Jour', number_format($stats['benefice_jour'],0,',',' ').' FCFA', 'success'],
        ['📅 CA Mois', number_format($stats['ca_mois'],0,',',' ').' FCFA', 'info'],
        ['🧾 Ventes', $stats['ventes_jour'], 'secondary'],
        ['💳 Dettes clients', number_format($stats['dettes_clients'],0,',',' ').' FCFA', 'warning'],
        ['🏭 Dettes fournisseurs', number_format($stats['dettes_fournisseurs'],0,',',' ').' FCFA', 'danger'],
    ] as [$label, $value, $color])
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card bg-{{ $color }} {{ in_array($color,['warning','secondary']) ? 'text-dark' : 'text-white' }}">
            <div class="card-body py-3"><small>{{ $label }}</small><h5 class="mb-0">{{ $value }}</h5></div>
        </div>
    </div>
    @endforeach
</div>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-modern"><div class="card-header bg-white"><h6 class="mb-0">🏆 Top produits</h6></div>
        <div class="card-body">@forelse($topProducts as $p)<div class="d-flex justify-content-between py-1 border-bottom"><span>{{ $p->name }}</span><span class="badge bg-primary">{{ $p->total_vendu }}</span></div>@empty<p class="text-muted mb-0">Aucune vente</p>@endforelse</div></div>
    </div>
    <div class="col-md-6">
        <div class="card card-modern"><div class="card-header bg-white"><h6 class="mb-0">⚠️ Stocks critiques</h6></div>
        <div class="card-body">@forelse($lowStock as $s)<div class="d-flex justify-content-between py-1 border-bottom"><span>📦 {{ $s->product->name }}</span><span class="badge bg-danger">{{ $s->quantity }}</span></div>@empty<p class="text-success mb-0">✅ Stocks OK</p>@endforelse</div></div>
    </div>
</div>
<div class="row g-3 mt-2">
    <div class="col-md-3"><div class="card card-modern text-center py-3"><h4>{{ $stats['credits_en_retard'] }}</h4><small class="text-muted">Crédits en retard</small></div></div>
    <div class="col-md-3"><div class="card card-modern text-center py-3"><h4>{{ $stats['factures_en_attente'] }}</h4><small class="text-muted">Factures en attente</small></div></div>
    <div class="col-md-3"><div class="card card-modern text-center py-3"><h4>{{ $stats['retours_mois'] }}</h4><small class="text-muted">Retours ce mois</small></div></div>
    <div class="col-md-3"><div class="card card-modern text-center py-3"><h4>{{ $stats['annulations_mois'] }}</h4><small class="text-muted">Annulations ce mois</small></div></div>
</div>
@endsection
