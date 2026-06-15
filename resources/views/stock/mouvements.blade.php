@extends('layouts.app')

@section('page-title', '📜 Mouvements de Stock')

@section('content')
<div class="card card-modern">
    <div class="card-header bg-white">
        <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-secondary">← Retour</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Produit</th><th>Type</th><th>Qté</th><th>Avant</th><th>Après</th><th>Utilisateur</th><th>Notes</th></tr>
            </thead>
            <tbody>
                @foreach($mouvements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->product->name }}</td>
                    <td>
                        @switch($m->type)
                            @case('entree') <span class="badge bg-success">📥 Entrée</span> @break
                            @case('sortie') <span class="badge bg-warning">📤 Sortie</span> @break
                            @case('inventaire') <span class="badge bg-info">📝 Inventaire</span> @break
                            @case('retour') <span class="badge bg-primary">↩️ Retour</span> @break
                            @case('annulation') <span class="badge bg-danger">🚫 Annulation</span> @break
                        @endswitch
                    </td>
                    <td>{{ $m->quantity }}</td>
                    <td>{{ $m->quantity_before }}</td>
                    <td>{{ $m->quantity_after }}</td>
                    <td>{{ $m->user->name }}</td>
                    <td><small>{{ $m->notes }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $mouvements->links() }}</div>
</div>
@endsection
