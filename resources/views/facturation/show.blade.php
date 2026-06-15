@extends('layouts.app')
@section('page-title', '📄 '.$facture->numero)
@section('content')
@php
    $format = $format ?? \App\Services\ProformaFormatService::resolve($facture);
    $lignes = \App\Services\ProformaFormatService::lignes($facture);
@endphp
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('facturation.index', ['type' => $facture->type]) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    @if(in_array($facture->type, ['proforma', 'devis', 'facture', 'facture_a4']))
    <a href="{{ route('facturation.imprimer', $facture) }}" class="btn btn-primary btn-sm">🖨️ Imprimer ({{ \App\Services\ProformaFormatService::label($format) }})</a>
    @endif
</div>
<div class="card card-modern"><div class="card-body">
<p>
    <strong>Type:</strong> {{ $facture->typeLabel() }} |
    <strong>Statut:</strong> {{ $facture->statut }} |
    <strong>Client:</strong> {{ $facture->clientDisplayName() }} |
    <strong>Total:</strong> {{ number_format($facture->total,0,',',' ') }} FCFA
    @if($facture->type === 'proforma')
    | <strong>Format:</strong> {{ $facture->format_papier === 'auto' ? 'Automatique → '. \App\Services\ProformaFormatService::label($format) : strtoupper($facture->format_papier) }}
    @endif
</p>
@if($facture->notes)<p class="text-muted"><strong>Notes:</strong> {{ $facture->notes }}</p>@endif
<hr>
<h6>Lignes</h6>
<table class="table table-sm"><thead><tr><th>Désignation</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
<tbody>
@foreach($lignes as $l)
<tr>
    <td>{{ $l->designation }}</td>
    <td>{{ $l->quantite }}</td>
    <td>{{ number_format($l->prix_unitaire,0,',',' ') }}</td>
    <td>{{ number_format($l->total_ligne,0,',',' ') }}</td>
</tr>
@endforeach
</tbody></table>
</div></div>
@endsection
