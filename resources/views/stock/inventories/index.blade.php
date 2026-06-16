@extends('layouts.app')

@section('page-title', '📋 Inventaires physiques')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Sessions d'inventaire</h5>
    @if(auth()->user()->hasPermission('stock.inventory'))
    <a href="{{ route('stock.inventories.create') }}" class="btn btn-primary">➕ Nouvel inventaire</a>
    @endif
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card card-modern">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Site</th>
                    <th>Créé par</th>
                    <th>Écart qté</th>
                    <th>Écart valeur</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td><strong>{{ $s->reference }}</strong></td>
                    <td>{{ $s->inventory_date->format('d/m/Y') }}</td>
                    <td>{{ $s->site?->name ?? '—' }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>
                        @if($s->total_variance_qty > 0)
                            <span class="text-success">+{{ $s->total_variance_qty }}</span>
                        @elseif($s->total_variance_qty < 0)
                            <span class="text-danger">{{ $s->total_variance_qty }}</span>
                        @else
                            0
                        @endif
                    </td>
                    <td>{{ number_format($s->total_variance_value, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @if($s->status === 'brouillon') <span class="badge bg-warning text-dark">Brouillon</span>
                        @elseif($s->status === 'valide') <span class="badge bg-success">Validé</span>
                        @else <span class="badge bg-secondary">Annulé</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('stock.inventories.show', $s) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                        @if($s->status === 'valide')
                        <a href="{{ route('stock.inventories.report', $s) }}" class="btn btn-sm btn-outline-secondary">Rapport</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun inventaire enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $sessions->links() }}</div>
</div>
@endsection
