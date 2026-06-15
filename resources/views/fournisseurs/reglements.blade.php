@extends('layouts.app')
@section('page-title', '💸 Règlements fournisseurs')
@section('content')
<div class="row g-4"><div class="col-md-5"><div class="card card-modern"><div class="card-header bg-success text-white">➕ Nouveau règlement</div><div class="card-body">
<form method="POST" action="{{ route('fournisseurs.reglements.store') }}">@csrf
<select name="fournisseur_id" class="form-select mb-2" required>@foreach(\App\Models\Fournisseur::all() as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach</select>
<input name="montant" type="number" class="form-control mb-2" placeholder="Montant" required step="0.01">
<input name="date_echeance" type="date" class="form-control mb-2">
<select name="mode_paiement" class="form-select mb-2"><option value="especes">Espèces</option><option value="virement">Virement</option><option value="mobile">Mobile Money</option></select>
<button class="btn btn-success w-100">Enregistrer</button></form></div></div></div>
<div class="col-md-7"><div class="card card-modern"><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Fournisseur</th><th>Montant</th><th>Date</th><th>Par</th></tr></thead>
<tbody>@foreach($reglements as $r)<tr><td>{{ $r->fournisseur->name }}</td><td>{{ number_format($r->montant,0,',',' ') }}</td>
<td>{{ $r->created_at->format('d/m/Y') }}</td><td>{{ $r->user->name }}</td></tr>@endforeach</tbody></table></div></div></div>
@endsection
