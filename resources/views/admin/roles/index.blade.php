@extends('layouts.app')

@section('page-title', '🔐 Gestion des Rôles')

@section('content')
<div class="row g-4">
    @foreach($roles as $role)
    <div class="col-md-4">
        <div class="card card-modern h-100">
            <div class="card-body">
                <h5>{{ $role->name === 'Administrateur' ? '👑' : ($role->slug === 'caissier' ? '💰' : '📦') }} {{ $role->name }}</h5>
                <p class="text-muted small">{{ $role->description }}</p>
                <p><span class="badge bg-secondary">{{ $role->users_count }} utilisateur(s)</span></p>
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">✏️ Modifier permissions</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
