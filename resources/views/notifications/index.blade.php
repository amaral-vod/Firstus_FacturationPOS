@extends('layouts.app')
@section('page-title', '🔔 Notifications')
@section('content')
<form action="{{ route('notifications.read-all') }}" method="POST" class="mb-3">@csrf<button class="btn btn-sm btn-outline-primary">✅ Tout marquer comme lu</button></form>
<div class="card card-modern">@forelse($notifications as $n)
<div class="p-3 border-bottom {{ $n->isRead()?'opacity-50':'' }}">
<div class="d-flex justify-content-between"><strong>{{ $n->titre }}</strong><small>{{ $n->created_at->diffForHumans() }}</small></div>
<p class="mb-1">{{ $n->message }}</p>
@if(!$n->isRead())<form action="{{ route('notifications.read',$n) }}" method="POST">@csrf<button class="btn btn-sm btn-link p-0">Marquer lu</button></form>@endif
</div>@empty<p class="text-muted p-4 mb-0">Aucune notification</p>@endforelse
<div class="card-footer">{{ $notifications->links() }}</div></div>
@endsection
