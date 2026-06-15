@extends('layouts.app')
@section('page-title', '🤝 Clients')
@section('content')
<div class="alert alert-info">💳 Total dettes clients: <strong>{{ number_format($totalDettes,0,',',' ') }} FCFA</strong>
<a href="{{ route('clients.credits') }}" class="alert-link">Voir les crédits →</a></div>
<div class="row g-4"><div class="col-md-8"><div class="card card-modern"><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Code</th><th>Nom</th><th>Téléphone</th><th>Dette</th><th>Plafond crédit</th></tr></thead>
<tbody>@foreach($clients as $c)<tr><td>{{ $c->code }}</td><td>{{ $c->name }}</td><td>{{ $c->phone }}</td>
<td class="{{ $c->balance_due>0?'text-danger':'' }}">{{ number_format($c->balance_due,0,',',' ') }}</td>
<td>{{ number_format($c->credit_limit,0,',',' ') }}</td></tr>@endforeach</tbody></table></div></div></div>
<div class="col-md-4"><div class="card card-modern"><div class="card-header bg-primary text-white">➕ Nouveau client</div><div class="card-body">
<form method="POST" action="{{ route('clients.store') }}">@csrf
<input name="name" class="form-control mb-2" placeholder="Nom" required>
<input name="phone" class="form-control mb-2" placeholder="Téléphone">
<input name="email" class="form-control mb-2" placeholder="Email">
<input name="credit_limit" type="number" class="form-control mb-2" placeholder="Plafond crédit">
<button class="btn btn-primary w-100">Ajouter</button></form></div></div></div></div>
@endsection
