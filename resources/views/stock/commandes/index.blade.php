@extends('layouts.app')

@section('page-title', '🛒 Commandes fournisseur')

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('commande_skipped') && count(session('commande_skipped')))
<div class="alert alert-warning">
    <strong>Produits ignorés (sans fournisseur) :</strong>
    <ul class="mb-0 mt-2">@foreach(session('commande_skipped') as $msg)<li>{{ $msg }}</li>@endforeach</ul>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Bons de commande</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('stock.analyse', ['site_id' => $siteId]) }}" class="btn btn-outline-secondary btn-sm">📊 Analyse</a>
        <form action="{{ route('stock.commandes.generate') }}" method="POST">
            @csrf
            <input type="hidden" name="site_id" value="{{ $siteId }}">
            <button class="btn btn-primary btn-sm">⚡ Générer depuis stock faible</button>
        </form>
    </div>
</div>

@include('stock._site_filter')

<div class="card card-modern">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Fournisseur</th>
                    <th>Site</th>
                    <th>Statut</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->reference }}</strong></td>
                    <td>{{ $order->fournisseur->name }}</td>
                    <td>{{ $order->site?->name ?? '—' }}</td>
                    <td><span class="badge bg-secondary">{{ $order->statusLabel() }}</span></td>
                    <td>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $order->ordered_at?->format('d/m/Y') ?? $order->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('stock.commandes.show', $order) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune commande. Générez depuis l’analyse stock.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $orders->links() }}</div>
</div>
@endsection
