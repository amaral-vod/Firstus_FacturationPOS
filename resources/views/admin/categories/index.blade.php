@extends('layouts.app')

@section('page-title', '🏷️ Catégories')

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Catégorie</th><th>Produits</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->products_count }}</td>
                            <td>{!! $cat->is_active ? '✅' : '🚫' !!}</td>
                            <td>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern">
            <div class="card-header bg-primary text-white"><h6 class="mb-0">➕ Nouvelle catégorie</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <input type="text" name="name" class="form-control mb-2" placeholder="Nom" required>
                    <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
                    <button class="btn btn-primary w-100">➕ Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
