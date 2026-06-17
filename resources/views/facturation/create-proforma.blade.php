@extends('layouts.app')
@section('page-title', '📋 Nouvelle facture proforma')

@section('styles')
<style>
    .proforma-page { max-height: calc(100vh - 130px); display: flex; flex-direction: column; }
    .proforma-top {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: .75rem;
    }
    @media (max-width: 992px) {
        .proforma-top { grid-template-columns: 1fr; }
        .proforma-page { max-height: none; }
    }
    .proforma-page .card-header { padding: .5rem .75rem; font-size: .9rem; }
    .proforma-page .card-body { padding: .75rem; }
    .proforma-page .form-label { font-size: .8rem; margin-bottom: .2rem; }
    .proforma-page .form-control,
    .proforma-page .form-select { font-size: .85rem; padding: .35rem .5rem; }
    .proforma-lines { flex: 1; min-height: 0; display: flex; flex-direction: column; }
    .proforma-lines .table-wrap { flex: 1; overflow: auto; max-height: 42vh; }
    .proforma-lines table { font-size: .85rem; margin-bottom: 0; }
    .proforma-lines th, .proforma-lines td { padding: .35rem .4rem; vertical-align: middle; }
    .proforma-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        margin-top: .75rem;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('facturation.index', ['type' => 'proforma']) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form action="{{ route('facturation.store') }}" method="POST" id="proforma-form" class="proforma-page">
@csrf

<div class="proforma-top">
    <div class="card card-modern h-100">
        <div class="card-header bg-white fw-semibold">👤 Client</div>
        <div class="card-body">
            <label class="form-label">Client enregistré</label>
            <select name="client_id" id="client_id" class="form-select mb-2">
                <option value="">— Aucun / saisie libre —</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" data-nom="{{ $c->name }}" data-adresse="{{ $c->address }}" data-tel="{{ $c->phone }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="text" name="client_nom" id="client_nom" class="form-control mb-2" placeholder="Nom client">
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" name="client_adresse" id="client_adresse" class="form-control" placeholder="Adresse">
                </div>
                <div class="col-6">
                    <input type="text" name="client_telephone" id="client_telephone" class="form-control" placeholder="Téléphone">
                </div>
            </div>
        </div>
    </div>

    <div class="card card-modern h-100">
        <div class="card-header bg-white fw-semibold">⚙️ Options</div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Format papier</label>
                    <select name="format_papier" class="form-select">
                        <option value="auto">🔄 Automatique</option>
                        <option value="ticket">🧾 Ticket 80 mm</option>
                        <option value="a5">📄 A5</option>
                        <option value="a4">📃 A4</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Date validité</label>
                    <input type="date" name="date_echeance" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Remise (FCFA)</label>
                    <input type="number" name="remise" id="remise" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Conditions, délais..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-modern h-100">
        <div class="card-header bg-white fw-semibold">💰 Récapitulatif</div>
        <div class="card-body d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between mb-1"><span>Sous-total</span><strong id="subtotal">0 FCFA</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Remise</span><span id="remise-display">0 FCFA</span></div>
                <div class="d-flex justify-content-between fs-5 border-top pt-2"><span>Total</span><strong id="grand-total" class="text-primary">0 FCFA</strong></div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">📋 Créer et imprimer</button>
        </div>
    </div>
</div>

<div class="card card-modern proforma-lines">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">📦 Lignes</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">+ Ligne</button>
    </div>
    <div class="table-wrap">
        <table class="table mb-0" id="items-table">
            <thead class="table-light sticky-top">
                <tr>
                    <th style="width:38%">Désignation</th>
                    <th style="width:10%">Qté</th>
                    <th style="width:14%">P.U.</th>
                    <th style="width:14%">Total</th>
                    <th style="width:4%"></th>
                </tr>
            </thead>
            <tbody id="items-body">
                @if($vente)
                    @foreach($vente->details as $i => $d)
                    <tr class="item-row">
                        <td>
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $d->product_id }}">
                            <input type="text" name="items[{{ $i }}][designation]" class="form-control form-control-sm" value="{{ $d->product->name }}" required>
                        </td>
                        <td><input type="number" name="items[{{ $i }}][quantite]" class="form-control form-control-sm qty" value="{{ $d->quantite }}" min="1" required></td>
                        <td><input type="number" name="items[{{ $i }}][prix_unitaire]" class="form-control form-control-sm price" value="{{ $d->prix_unitaire }}" min="0" step="0.01" required></td>
                        <td class="line-total align-middle text-end fw-semibold">{{ number_format($d->total_ligne, 0, ',', ' ') }}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">✕</button></td>
                    </tr>
                    @endforeach
                @else
                    <tr class="item-row">
                        <td>
                            <select class="form-select form-select-sm product-select mb-1">
                                <option value="">— Produit —</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->effective_price }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="items[0][product_id]" class="product-id">
                            <input type="text" name="items[0][designation]" class="form-control form-control-sm designation" required placeholder="Désignation">
                        </td>
                        <td><input type="number" name="items[0][quantite]" class="form-control form-control-sm qty" value="1" min="1" required></td>
                        <td><input type="number" name="items[0][prix_unitaire]" class="form-control form-control-sm price" value="0" min="0" step="0.01" required></td>
                        <td class="line-total align-middle text-end">0</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">✕</button></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
</form>

<template id="row-template">
<tr class="item-row">
    <td>
        <select class="form-select form-select-sm product-select mb-1">
            <option value="">— Produit —</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->effective_price }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input type="hidden" name="items[__INDEX__][product_id]" class="product-id">
        <input type="text" name="items[__INDEX__][designation]" class="form-control form-control-sm designation" required placeholder="Désignation">
    </td>
    <td><input type="number" name="items[__INDEX__][quantite]" class="form-control form-control-sm qty" value="1" min="1" required></td>
    <td><input type="number" name="items[__INDEX__][prix_unitaire]" class="form-control form-control-sm price" value="0" min="0" step="0.01" required></td>
    <td class="line-total align-middle text-end">0</td>
    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">✕</button></td>
</tr>
</template>
@endsection

@section('scripts')
<script>
(function() {
    let rowIndex = document.querySelectorAll('.item-row').length;

    function fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)); }

    function recalc() {
        let sub = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const q = parseFloat(row.querySelector('.qty')?.value) || 0;
            const p = parseFloat(row.querySelector('.price')?.value) || 0;
            const t = q * p;
            row.querySelector('.line-total').textContent = fmt(t);
            sub += t;
        });
        const rem = parseFloat(document.getElementById('remise').value) || 0;
        document.getElementById('subtotal').textContent = fmt(sub) + ' FCFA';
        document.getElementById('remise-display').textContent = fmt(rem) + ' FCFA';
        document.getElementById('grand-total').textContent = fmt(Math.max(0, sub - rem)) + ' FCFA';
    }

    function bindRow(row) {
        row.querySelectorAll('.qty, .price').forEach(el => el.addEventListener('input', recalc));
        const sel = row.querySelector('.product-select');
        if (sel) {
            sel.addEventListener('change', function() {
                const opt = this.selectedOptions[0];
                if (!opt.value) return;
                row.querySelector('.product-id').value = opt.value;
                row.querySelector('.designation').value = opt.dataset.name;
                row.querySelector('.price').value = opt.dataset.price;
                recalc();
            });
        }
        row.querySelector('.remove-row')?.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalc();
            }
        });
    }

    document.getElementById('add-row').addEventListener('click', function() {
        const tpl = document.getElementById('row-template').innerHTML.replace(/__INDEX__/g, rowIndex++);
        const tbody = document.getElementById('items-body');
        tbody.insertAdjacentHTML('beforeend', tpl);
        bindRow(tbody.lastElementChild);
        recalc();
    });

    document.querySelectorAll('.item-row').forEach(bindRow);
    document.getElementById('remise').addEventListener('input', recalc);

    document.getElementById('client_id').addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        if (!opt.value) return;
        document.getElementById('client_nom').value = opt.dataset.nom || '';
        document.getElementById('client_adresse').value = opt.dataset.adresse || '';
        document.getElementById('client_telephone').value = opt.dataset.tel || '';
    });

    recalc();
})();
</script>
@endsection
