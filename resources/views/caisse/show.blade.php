@extends('layouts.app')

@section('page-title', '🧾 Facture ' . $vente->numero_facture)

@section('content')
<div class="card card-modern">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">{{ $vente->numero_facture }}</h5>
        <div>
            <a href="{{ route('caisse.ticket', $vente) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨️ Ticket</a>
            @if($vente->statut === 'complete' && auth()->user()->hasPermission('retour.manage'))
            <a href="{{ route('retours.create', ['vente_id' => $vente->id]) }}" class="btn btn-sm btn-warning">↩️ Retour</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3"><strong>📅 Date:</strong> {{ $vente->created_at->format('d/m/Y H:i') }}</div>
            <div class="col-md-3"><strong>👤 Client:</strong> {{ $vente->clientLabel() }}</div>
            <div class="col-md-3"><strong>🧑‍💼 Caissier:</strong> {{ $vente->user->name }}</div>
            <div class="col-md-3"><strong>Statut:</strong> {{ $vente->statut }}</div>
        </div>
        <table class="table">
            <thead><tr><th>Produit</th><th>Qté</th><th>Prix unit.</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($vente->details as $d)
                <tr>
                    <td>{{ $d->product->name }}</td>
                    <td>{{ $d->quantite }} @if($d->quantite_retournee > 0)<small class="text-warning">({{ $d->quantite_retournee }} retournés)</small>@endif</td>
                    <td>{{ number_format($d->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($d->total_ligne, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="text-end"><strong>Total</strong></td><td><strong>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</strong></td></tr>
            </tfoot>
        </table>
        @if($vente->annulation)
        <div class="alert alert-danger">🚫 Annulée le {{ $vente->annulation->annulee_le->format('d/m/Y H:i') }} — {{ $vente->annulation->motif }}</div>
        @endif
    </div>
</div>
@endsection
