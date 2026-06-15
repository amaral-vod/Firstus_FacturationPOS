@extends('layouts.app')

@section('page-title', '🚫 Annulations de Factures')

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card card-modern">
            <div class="card-header bg-danger text-white"><h6 class="mb-0">🚫 Annuler une facture</h6></div>
            <div class="card-body">
                <form action="{{ route('annulations.store') }}" method="POST" onsubmit="return confirm('Confirmer l\'annulation ?')">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">🧾 Facture</label>
                        <select name="vente_id" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            @foreach($ventes as $v)
                                <option value="{{ $v->id }}">{{ $v->numero_facture }} — {{ number_format($v->total, 0, ',', ' ') }} FCFA</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">📝 Motif d'annulation <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="3" required minlength="10" placeholder="Motif obligatoire (min. 10 caractères)"></textarea>
                    </div>
                    <div class="alert alert-warning small">⚠️ L'annulation remettra automatiquement les articles en stock.</div>
                    <button type="submit" class="btn btn-danger w-100">🚫 Annuler la facture</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">📜 Historique des annulations</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Facture</th><th>Motif</th><th>Par</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($annulations as $a)
                        <tr>
                            <td>{{ $a->vente->numero_facture }}</td>
                            <td><small>{{ Str::limit($a->motif, 50) }}</small></td>
                            <td>{{ $a->user->name }}</td>
                            <td>{{ $a->annulee_le->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucune annulation</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $annulations->links() }}</div>
        </div>
    </div>
</div>
@endsection
