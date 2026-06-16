@extends('layouts.app')

@section('page-title', '📄 Rapport inventaire '.$session->reference)

@section('styles')
<style>
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .content-area { padding: 0 !important; }
    }
    .report-header { border-bottom: 2px solid #1e1b4b; padding-bottom: 1rem; margin-bottom: 1.5rem; }
</style>
@endsection

@section('content')
<div class="no-print mb-3 d-flex gap-2">
    <a href="{{ route('stock.inventories.show', $session) }}" class="btn btn-outline-secondary">← Retour</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
</div>

<div class="report-header">
    <h4 class="mb-1">Rapport d'inventaire — {{ $session->reference }}</h4>
    <p class="mb-0 text-muted">
        Date : {{ $session->inventory_date->format('d/m/Y') }} |
        Site : {{ $session->site?->name ?? '—' }} |
        Statut : {{ ucfirst($session->status) }}
    </p>
    <p class="mb-0 small text-muted">
        Créé par {{ $session->user->name }}
        @if($session->validator)
        — Validé par {{ $session->validator->name }} le {{ $session->validated_at?->format('d/m/Y H:i') }}
        @endif
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><strong>Stock théorique</strong><br>{{ $session->total_theoretical_qty }} unités</div>
    <div class="col-md-3"><strong>Stock compté</strong><br>{{ $session->total_counted_qty }} unités</div>
    <div class="col-md-3"><strong>Écart quantité</strong><br>{{ $session->total_variance_qty >= 0 ? '+' : '' }}{{ $session->total_variance_qty }}</div>
    <div class="col-md-3"><strong>Écart valeur</strong><br>{{ number_format($session->total_variance_value, 0, ',', ' ') }} FCFA</div>
</div>

@if($session->notes)
<p><strong>Notes :</strong> {{ $session->notes }}</p>
@endif

<h6 class="mt-4">Produits avec écart</h6>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Produit</th>
            <th>Théorique</th>
            <th>Compté</th>
            <th>Écart</th>
            <th>Valeur écart</th>
            <th>Motif</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($session->lines->where('variance_qty', '!=', 0) as $line)
        <tr>
            <td>{{ $line->product->name }} <small class="text-muted">({{ $line->product->sku }})</small></td>
            <td>{{ $line->theoretical_qty }}</td>
            <td>{{ $line->counted_qty }}</td>
            <td>{{ $line->variance_qty >= 0 ? '+' : '' }}{{ $line->variance_qty }}</td>
            <td>{{ number_format($line->variance_value, 0, ',', ' ') }} FCFA</td>
            <td>{{ $line->reasonLabel() ?? '—' }}</td>
            <td>{{ $line->notes ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted">Aucun écart — inventaire conforme.</td></tr>
        @endforelse
    </tbody>
</table>

<h6 class="mt-4">Historique des ajustements</h6>
<p class="small text-muted">Les mouvements de type « Inventaire » avec référence <code>{{ $session->reference }}</code> sont enregistrés dans le journal des mouvements de stock.</p>
@endsection
