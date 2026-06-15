@extends('layouts.app')

@section('page-title', '➕ Nouveau Retour')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-modern">
            <div class="card-body">
                <form action="{{ route('retours.store') }}" method="POST" id="retourForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">🧾 Facture</label>
                        <select name="vente_id" class="form-select" id="venteSelect" required onchange="location.href='?vente_id='+this.value">
                            <option value="">Sélectionner une facture...</option>
                            @foreach($ventes as $v)
                                <option value="{{ $v->id }}" {{ ($vente?->id ?? '') == $v->id ? 'selected' : '' }}>
                                    {{ $v->numero_facture }} — {{ number_format($v->total, 0, ',', ' ') }} FCFA ({{ $v->created_at->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($vente)
                    <div class="mb-3">
                        <label class="form-label">Type de retour</label>
                        <select name="type" class="form-select" id="typeRetour" onchange="toggleItems()">
                            <option value="total">↩️ Retour total</option>
                            <option value="partiel">↩️ Retour partiel</option>
                        </select>
                    </div>

                    <div id="itemsSection" style="display:none" class="mb-3">
                        <label class="form-label">Articles à retourner</label>
                        @foreach($vente->details as $i => $detail)
                            @if($detail->quantiteRestante() > 0)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded">
                                <input type="hidden" name="items[{{ $i }}][detail_vente_id]" value="{{ $detail->id }}">
                                <span class="flex-grow-1">📦 {{ $detail->product->name }} (max: {{ $detail->quantiteRestante() }})</span>
                                <input type="number" name="items[{{ $i }}][quantite]" class="form-control" style="width:80px" min="1" max="{{ $detail->quantiteRestante() }}" value="1">
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">📝 Motif du retour <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="3" required minlength="10" placeholder="Justification obligatoire (min. 10 caractères)"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">↩️ Enregistrer le retour</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleItems() {
    document.getElementById('itemsSection').style.display =
        document.getElementById('typeRetour').value === 'partiel' ? 'block' : 'none';
}
</script>
@endsection
