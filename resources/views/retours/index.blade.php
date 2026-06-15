@extends('layouts.app')

@section('page-title', '↩️ Retours de Marchandises')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5>Historique des retours</h5>
    <a href="{{ route('retours.create') }}" class="btn btn-primary">➕ Nouveau retour</a>
</div>
<div class="card card-modern">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>N° Retour</th><th>Facture</th><th>Type</th><th>Montant</th><th>Motif</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($retours as $retour)
                <tr>
                    <td><strong>{{ $retour->numero_retour }}</strong></td>
                    <td>{{ $retour->vente->numero_facture }}</td>
                    <td><span class="badge bg-{{ $retour->type === 'total' ? 'danger' : 'warning' }}">{{ $retour->type === 'total' ? '↩️ Total' : '↩️ Partiel' }}</span></td>
                    <td>{{ number_format($retour->montant_rembourse, 0, ',', ' ') }} FCFA</td>
                    <td><small>{{ Str::limit($retour->motif, 40) }}</small></td>
                    <td>{{ $retour->created_at->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('retours.show', $retour) }}" class="btn btn-sm btn-outline-primary">👁️</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $retours->links() }}</div>
</div>
@endsection
