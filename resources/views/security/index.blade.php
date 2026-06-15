@extends('layouts.app')
@section('page-title', '🛡️ Sécurité')
@section('content')
<div class="card card-modern mb-4"><div class="card-header bg-white"><h6 class="mb-0">🔒 Journal de sécurité</h6></div><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>IP</th><th>Description</th></tr></thead>
<tbody>@foreach($logs as $l)<tr><td><small>{{ $l->created_at->format('d/m/Y H:i') }}</small></td><td>{{ $l->user?->name ?? '—' }}</td>
<td><span class="badge bg-{{ str_contains($l->action,'echec')?'danger':'success' }}">{{ $l->action }}</span></td>
<td>{{ $l->ip_address }}</td><td>{{ $l->description }}</td></tr>@endforeach</tbody></table></div></div>
<div class="card card-modern"><div class="card-header bg-white"><h6 class="mb-0">🖥️ Sessions actives</h6></div><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>ID Session</th><th>User ID</th><th>IP</th><th>Dernière activité</th></tr></thead>
<tbody>@foreach($sessions as $s)<tr><td><small>{{ Str::limit($s->id,20) }}</small></td><td>{{ $s->user_id ?? '—' }}</td>
<td>{{ $s->ip_address }}</td><td>{{ date('d/m/Y H:i', $s->last_activity) }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
