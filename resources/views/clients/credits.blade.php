@extends('layouts.app')
@section('page-title', '💳 Crédits clients')
@section('content')
@if($enRetard>0)<div class="alert alert-danger">⚠️ {{ $enRetard }} crédit(s) en retard — relance recommandée</div>@endif
<div class="card card-modern"><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Client</th><th>Montant</th><th>Payé</th><th>Reste</th><th>Échéance</th><th>Statut</th></tr></thead>
<tbody>@forelse($credits as $cr)<tr><td>{{ $cr->client->name }}</td><td>{{ number_format($cr->montant,0,',',' ') }}</td>
<td>{{ number_format($cr->montant_paye,0,',',' ') }}</td><td>{{ number_format($cr->reste(),0,',',' ') }}</td>
<td>{{ $cr->date_echeance->format('d/m/Y') }}</td><td><span class="badge bg-{{ $cr->statut==='en_retard'?'danger':'secondary' }}">{{ $cr->statut }}</span></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-3">Aucun crédit</td></tr>@endforelse</tbody></table></div>
<div class="card-footer">{{ $credits->links() }}</div></div>
@endsection
