@extends('layouts.app')
@section('page-title', '📜 Historique connexions')
@section('content')
<div class="card card-modern"><div class="card-header bg-white">
<form class="row g-2"><div class="col-md-3"><select name="success" class="form-select form-select-sm"><option value="">Tous</option><option value="1" {{ request('success')==='1'?'selected':'' }}>Réussies</option><option value="0" {{ request('success')==='0'?'selected':'' }}>Échouées</option></select></div>
<div class="col-md-3"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
<div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filtrer</button></div></form></div>
<div class="card-body p-0"><table class="table mb-0"><thead><tr><th>Date</th><th>Email</th><th>Utilisateur</th><th>IP</th><th>Résultat</th></tr></thead>
<tbody>@foreach($histories as $h)<tr><td>{{ $h->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $h->email }}</td>
<td>{{ $h->user?->name ?? '—' }}</td><td>{{ $h->ip_address }}</td>
<td>{!! $h->success?'<span class="badge bg-success">✅</span>':'<span class="badge bg-danger">❌</span>' !!}</td></tr>@endforeach</tbody></table></div>
<div class="card-footer">{{ $histories->links() }}</div></div>
@endsection
