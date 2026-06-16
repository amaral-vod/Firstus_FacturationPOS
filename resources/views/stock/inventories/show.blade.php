@extends('layouts.app')

@section('page-title', '📝 Inventaire '.$session->reference)

@section('styles')
<style>
    .variance-pos { color: #16a34a; font-weight: 600; }
    .variance-neg { color: #dc2626; font-weight: 600; }
    .variance-zero { color: #64748b; }
    .reason-cell select { min-width: 160px; }
    @media print { .no-print { display: none !important; } }
</style>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success no-print">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger no-print">{{ session('error') }}</div>@endif

<div class="row g-3 mb-3 no-print">
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Stock théorique</small><div class="fs-5 fw-bold">{{ $session->total_theoretical_qty }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Stock compté</small><div class="fs-5 fw-bold">{{ $session->total_counted_qty }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Écart quantité</small><div class="fs-5 fw-bold @if($session->total_variance_qty>0) variance-pos @elseif($session->total_variance_qty<0) variance-neg @endif">{{ $session->total_variance_qty >= 0 ? '+' : '' }}{{ $session->total_variance_qty }}</div></div></div>
    <div class="col-md-3"><div class="card card-modern p-3"><small class="text-muted">Écart valeur</small><div class="fs-5 fw-bold">{{ number_format($session->total_variance_value, 0, ',', ' ') }} FCFA</div></div></div>
</div>

<div class="card card-modern mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong>{{ $session->reference }}</strong> —
            {{ $session->inventory_date->format('d/m/Y') }} —
            {{ $session->site?->name ?? 'Site' }} —
            @if($session->status === 'brouillon') <span class="badge bg-warning text-dark">Brouillon</span>
            @elseif($session->status === 'valide') <span class="badge bg-success">Validé</span>
            @else <span class="badge bg-secondary">Annulé</span>
            @endif
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('stock.inventories.index') }}" class="btn btn-sm btn-outline-secondary">← Liste</a>
            @if($session->status === 'valide')
            <a href="{{ route('stock.inventories.report', $session) }}" class="btn btn-sm btn-outline-primary">📄 Rapport</a>
            @endif
        </div>
    </div>
</div>

@if($session->isEditable())
<form action="{{ route('stock.inventories.update', $session) }}" method="POST" id="inventoryForm">
    @csrf
    @method('PUT')
@endif

<div class="card card-modern">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Lignes d'inventaire ({{ $session->lines->count() }} produits)</h6>
        @if($session->isEditable())
        <input type="text" id="searchProduct" class="form-control form-control-sm no-print" style="max-width:220px" placeholder="Rechercher un produit…">
        @endif
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0" id="linesTable">
            <thead class="table-light">
                <tr>
                    <th>Produit</th>
                    <th>Théorique</th>
                    <th>Compté</th>
                    <th>Écart qté</th>
                    <th>Coût unit.</th>
                    <th>Écart valeur</th>
                    <th>Motif écart</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($session->lines as $line)
                <tr class="line-row" data-name="{{ strtolower($line->product->name) }}">
                    <td>
                        <div>{{ $line->product->name }}</div>
                        <small class="text-muted">{{ $line->product->sku }}</small>
                    </td>
                    <td class="theoretical">{{ $line->theoretical_qty }}</td>
                    <td>
                        @if($session->isEditable())
                        <input type="number" name="lines[{{ $line->product_id }}][counted_qty]"
                               class="form-control form-control-sm counted-input" min="0"
                               value="{{ $line->counted_qty }}" style="width:90px"
                               data-theoretical="{{ $line->theoretical_qty }}"
                               data-cost="{{ $line->unit_cost }}">
                        @else
                        {{ $line->counted_qty }}
                        @endif
                    </td>
                    <td class="variance-qty @if($line->variance_qty>0) variance-pos @elseif($line->variance_qty<0) variance-neg @else variance-zero @endif">
                        {{ $line->variance_qty >= 0 ? '+' : '' }}{{ $line->variance_qty }}
                    </td>
                    <td>{{ number_format($line->unit_cost, 0, ',', ' ') }}</td>
                    <td class="variance-value">{{ number_format($line->variance_value, 0, ',', ' ') }}</td>
                    <td class="reason-cell">
                        @if($session->isEditable())
                        <select name="lines[{{ $line->product_id }}][variance_reason]" class="form-select form-select-sm reason-input">
                            <option value="">—</option>
                            @foreach($reasons as $key => $label)
                            <option value="{{ $key }}" @selected($line->variance_reason === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @else
                        {{ $line->reasonLabel() ?? '—' }}
                        @endif
                    </td>
                    <td>
                        @if($session->isEditable())
                        <input type="text" name="lines[{{ $line->product_id }}][notes]" class="form-control form-control-sm"
                               value="{{ $line->notes }}" placeholder="…">
                        @else
                        {{ $line->notes ?? '—' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($session->isEditable())
<div class="d-flex flex-wrap gap-2 mt-3 no-print">
    <button type="submit" class="btn btn-primary">💾 Enregistrer le brouillon</button>
    @if(auth()->user()->hasPermission('stock.inventory.validate'))
    <button type="submit" name="action" value="validate" class="btn btn-success"
            onclick="return confirm('Valider cet inventaire et mettre à jour le stock ?')">
        ✅ Valider et appliquer au stock
    </button>
    @endif
    <button type="submit" formaction="{{ route('stock.inventories.cancel', $session) }}" formmethod="POST"
            class="btn btn-outline-danger" onclick="return confirm('Annuler cet inventaire ?')">
        🚫 Annuler
    </button>
</div>
</form>
@endif

@if($session->status === 'valide')
<div class="alert alert-info mt-3 no-print">
    Validé le {{ $session->validated_at?->format('d/m/Y H:i') }}
    @if($session->validator) par <strong>{{ $session->validator->name }}</strong>@endif
</div>
@endif
@endsection

@section('scripts')
@if($session->isEditable())
<script>
document.querySelectorAll('.counted-input').forEach(input => {
    input.addEventListener('input', () => {
        const row = input.closest('tr');
        const theoretical = parseInt(input.dataset.theoretical, 10);
        const cost = parseFloat(input.dataset.cost) || 0;
        const counted = parseInt(input.value, 10) || 0;
        const variance = counted - theoretical;
        const value = variance * cost;

        const qtyCell = row.querySelector('.variance-qty');
        qtyCell.textContent = (variance >= 0 ? '+' : '') + variance;
        qtyCell.className = 'variance-qty ' + (variance > 0 ? 'variance-pos' : variance < 0 ? 'variance-neg' : 'variance-zero');

        row.querySelector('.variance-value').textContent = Math.round(value).toLocaleString('fr-FR');

        const reason = row.querySelector('.reason-input');
        if (reason) reason.required = variance !== 0;
    });
});

document.getElementById('searchProduct')?.addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.line-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>
@endif
@endsection
