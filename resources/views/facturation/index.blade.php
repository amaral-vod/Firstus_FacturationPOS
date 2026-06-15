@extends('layouts.app')
@section('page-title', '📄 Facturation')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="?type=all" class="btn btn-sm btn-{{ $type==='all'?'primary':'outline-primary' }}">Tous</a>
        <a href="?type=proforma" class="btn btn-sm btn-{{ $type==='proforma'?'primary':'outline-primary' }}">📋 Proformas ({{ $counts['proforma']??0 }})</a>
        @foreach(['facture'=>'📄 Factures','devis'=>'📝 Devis','bon_commande'=>'📋 Commandes','bon_livraison'=>'🚚 Livraisons','ticket'=>'🧾 Tickets','facture_a4'=>'📃 A4'] as $k=>$l)
        <a href="?type={{ $k }}" class="btn btn-sm btn-{{ $type===$k?'primary':'outline-primary' }}">{{ $l }} ({{ $counts[$k]??0 }})</a>
        @endforeach
    </div>
    <a href="{{ route('facturation.create') }}" class="btn btn-primary btn-sm">+ Nouvelle proforma</a>
</div>
<div class="card card-modern"><div class="card-body p-0">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>N°</th><th>Type</th><th>Client</th><th>Format</th><th>Total</th><th>Statut</th><th>Date</th><th></th></tr></thead>
<tbody>@forelse($factures as $f)<tr>
<td>{{ $f->numero }}</td><td>{{ $f->typeLabel() }}</td><td>{{ $f->clientDisplayName() }}</td>
<td>@if($f->type==='proforma'){{ $f->format_papier==='auto'?'Auto':strtoupper($f->format_papier) }}@else—@endif</td>
<td>{{ number_format($f->total,0,',',' ') }} FCFA</td><td>{{ $f->statut }}</td><td>{{ $f->created_at->format('d/m/Y') }}</td>
<td>
    <a href="{{ route('facturation.show',$f) }}" class="btn btn-sm btn-outline-primary" title="Voir">👁️</a>
    @if(in_array($f->type, ['proforma','devis','facture','facture_a4']))
    <a href="{{ route('facturation.imprimer',$f) }}" class="btn btn-sm btn-outline-secondary" title="Imprimer">🖨️</a>
    @else
    <form action="{{ route('facturation.reimprimer',$f) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-secondary" title="Réimprimer">🖨️</button></form>
    @endif
</td>
</tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">Aucun document</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $factures->links() }}</div></div>
@endsection
