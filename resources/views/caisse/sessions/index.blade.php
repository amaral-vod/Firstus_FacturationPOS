@extends('layouts.app')
@section('page-title', '🏧 Gestion de caisse')
@section('content')
<div class="row g-4">
<div class="col-md-5">
@if($session)
<div class="card card-modern border-success"><div class="card-header bg-success text-white">✅ Caisse ouverte</div><div class="card-body">
<p>Fond initial: <strong>{{ number_format($session->fond_initial,0,',',' ') }} FCFA</strong></p>
<p>Ouverte le: {{ $session->opened_at->format('d/m/Y H:i') }}</p>
<form action="{{ route('caisse.sessions.fermer') }}" method="POST">@csrf
<input type="number" name="fond_reel" class="form-control mb-2" placeholder="Fond réel en caisse" required step="0.01">
<textarea name="notes" class="form-control mb-2" placeholder="Notes"></textarea>
<button class="btn btn-danger w-100">🔒 Fermer la caisse</button></form></div></div>
@else
<div class="card card-modern"><div class="card-header bg-primary text-white">🔓 Ouvrir la caisse</div><div class="card-body">
<form action="{{ route('caisse.sessions.ouvrir') }}" method="POST">@csrf
<input type="number" name="fond_initial" class="form-control mb-2" placeholder="Fonds initial" required step="0.01">
<button class="btn btn-primary w-100">✅ Ouvrir la caisse</button></form></div></div>
@endif
</div>
<div class="col-md-7"><div class="card card-modern"><div class="card-header bg-white"><h6 class="mb-0">📜 Historique sessions</h6></div><div class="card-body p-0">
<table class="table mb-0"><thead><tr><th>Caissier</th><th>Ouverture</th><th>Fermeture</th><th>Écart</th><th>Statut</th></tr></thead>
<tbody>@foreach($sessions as $s)<tr><td>{{ $s->user->name }}</td><td>{{ $s->opened_at?->format('d/m H:i') }}</td><td>{{ $s->closed_at?->format('d/m H:i') ?? '—' }}</td>
<td>@if($s->ecart_type==='surplus')<span class="text-success">+{{ $s->ecart }}</span>@elseif($s->ecart_type==='manquant')<span class="text-danger">-{{ $s->ecart }}</span>@else — @endif</td>
<td>{{ $s->statut }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $sessions->links() }}</div></div></div>
</div>
@endsection
