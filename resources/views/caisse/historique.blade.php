@extends('layouts.app')

@section('page-title', '🧾 Historique des Ventes')

@section('content')
<div class="card card-modern">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Historique des ventes</h5>
        <form class="d-flex gap-2">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            <button class="btn btn-sm btn-primary">🔍 Filtrer</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° Facture</th>
                    <th>Date</th>
                    <th>Caissier</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $vente)
                <tr>
                    <td><strong>{{ $vente->numero_facture }}</strong></td>
                    <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $vente->user->name }}</td>
                    <td>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @switch($vente->statut)
                            @case('complete') <span class="badge bg-success">✅ Complète</span> @break
                            @case('annulee') <span class="badge bg-danger">🚫 Annulée</span> @break
                            @case('retournee') <span class="badge bg-warning">↩️ Retournée</span> @break
                            @default <span class="badge bg-info">↩️ Partiel</span>
                        @endswitch
                    </td>
                    <td>
                        <a href="{{ route('caisse.show', $vente) }}" class="btn btn-sm btn-outline-primary">👁️</a>
                        @if(auth()->user()->hasPermission('vente.print'))
                        <a href="{{ route('caisse.ticket', $vente) }}" target="_blank" class="btn btn-sm btn-outline-secondary">🖨️</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune vente trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ventes->hasPages())
    <div class="card-footer">{{ $ventes->links() }}</div>
    @endif
</div>
@endsection
