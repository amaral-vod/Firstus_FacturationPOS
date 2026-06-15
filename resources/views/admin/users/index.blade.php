@extends('layouts.app')

@section('page-title', '👥 Gestion des Utilisateurs')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5>Liste des utilisateurs</h5>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">➕ Nouvel utilisateur</a>
</div>
<div class="card card-modern">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-primary">{{ $user->role?->name }}</span></td>
                    <td>{!! $user->is_active ? '<span class="badge bg-success">✅ Actif</span>' : '<span class="badge bg-danger">🚫 Inactif</span>' !!}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
