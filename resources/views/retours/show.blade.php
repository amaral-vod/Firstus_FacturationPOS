@extends('layouts.app')

@section('page-title', '↩️ Retour ' . $retour->numero_retour)

@section('content')
<div class="card card-modern">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3"><strong>N° Retour:</strong> {{ $retour->numero_retour }}</div>
            <div class="col-md-3"><strong>Facture:</strong> {{ $retour->vente->numero_facture }}</div>
            <div class="col-md-3"><strong>Type:</strong> {{ $retour->type === 'total' ? '↩️ Total' : '↩️ Partiel' }}</div>
            <div class="col-md-3"><strong>Date:</strong> {{ $retour->created_at->format('d/m/Y H:i') }}</div>
        </div>
        <div class="alert alert-info">📝 <strong>Motif:</strong> {{ $retour->motif }}</div>
        <table class="table">
            <thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($retour->details as $d)
                <tr>
                    <td>{{ $d->product->name }}</td>
                    <td>{{ $d->quantite }}</td>
                    <td>{{ number_format($d->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($d->total_ligne, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="text-end"><strong>Remboursement</strong></td>
                    <td><strong>{{ number_format($retour->montant_rembourse, 0, ',', ' ') }} FCFA</strong></td></tr>
            </tfoot>
        </table>
        <p class="text-muted">Traité par: {{ $retour->user->name }}</p>
    </div>
</div>
@endsection
