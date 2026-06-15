@extends('layouts.app')
@section('page-title', '🏭 Fournisseurs')
@section('content')
<div class="alert alert-warning">💰 Solde total fournisseurs: <strong>{{ number_format($totalDettes,0,',',' ') }} FCFA</strong>
<a href="{{ route('fournisseurs.reglements') }}" class="alert-link">Règlements →</a></div>
<div class="row g-4"><div class="col-md-8"><div class="card card-modern"><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Code</th><th>Nom</th><th>Téléphone</th><th>Solde dû</th></tr></thead>
<tbody>@foreach($fournisseurs as $f)<tr><td>{{ $f->code }}</td><td>{{ $f->name }}</td><td>{{ $f->phone }}</td>
<td class="text-danger">{{ number_format($f->balance,0,',',' ') }} FCFA</td></tr>@endforeach</tbody></table></div></div></div>
<div class="col-md-4"><div class="card card-modern"><div class="card-header bg-primary text-white">➕ Fournisseur</div><div class="card-body">
<form method="POST" action="{{ route('fournisseurs.store') }}">@csrf
<input name="name" class="form-control mb-2" placeholder="Nom" required>
<input name="phone" class="form-control mb-2" placeholder="Téléphone">
<button class="btn btn-primary w-100">Ajouter</button></form></div></div></div></div>
@endsection
