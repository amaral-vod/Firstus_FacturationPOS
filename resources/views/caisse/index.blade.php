@extends('layouts.app')

@section('page-title', '💰 Point de Vente')

@section('styles')
<style>
    .product-card { cursor: pointer; transition: all .2s; border: 2px solid transparent; }
    .product-card:hover { border-color: var(--primary); transform: scale(1.02); }
    .product-card.low-stock { opacity: .6; }
    .cart-item { border-bottom: 1px solid #e2e8f0; }
    .pos-container { height: calc(100vh - 120px); }
</style>
@endsection

@section('content')
<div class="row pos-container g-3">
    <div class="col-md-7">
        <div class="card card-modern h-100">
            <div class="card-header bg-white d-flex gap-2">
                <input type="text" id="searchProduct" class="form-control" placeholder="🔍 Rechercher un produit...">
                <select id="filterCategory" class="form-select" style="max-width:200px">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body overflow-auto">
                <div class="row g-2" id="productGrid">
                    @foreach($products as $product)
                    <div class="col-md-4 col-6 product-item" data-name="{{ strtolower($product->name) }}" data-category="{{ $product->category_id }}">
                        <div class="card product-card h-100 {{ ($product->stock?->quantity ?? 0) <= 0 ? 'low-stock' : '' }}"
                             onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->effective_price }}, {{ $product->stock?->quantity ?? 0 }})">
                            <div class="card-body p-2 text-center">
                                <div class="fs-4">📦</div>
                                <h6 class="mb-1 small">{{ $product->name }}</h6>
                                <div class="fw-bold text-primary">{{ number_format($product->effective_price, 0, ',', ' ') }} FCFA</div>
                                @if($product->isPromoActive())
                                    <small class="text-danger">🔥 Promo !</small>
                                @endif
                                <div class="small text-muted">Stock: {{ $product->stock?->quantity ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-modern h-100 d-flex flex-column">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🛒 Panier</h5>
            </div>
            <div class="card-body flex-grow-1 overflow-auto" id="cartItems">
                <p class="text-muted text-center" id="emptyCart">Panier vide — cliquez sur un produit</p>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between mb-2">
                    <span>Sous-total</span>
                    <span id="subtotal">0 FCFA</span>
                </div>
                <div class="mb-2">
                    <label class="form-label small">💸 Remise (FCFA)</label>
                    <input type="number" id="remise" class="form-control" value="0" min="0" onchange="updateTotals()">
                </div>
                <div class="d-flex justify-content-between mb-2 fw-bold fs-5">
                    <span>Total</span>
                    <span id="total" class="text-primary">0 FCFA</span>
                </div>
                <div class="mb-2">
                    <label class="form-label small">💳 Mode paiement</label>
                    <select id="modePaiement" class="form-select">
                        <option value="especes">💵 Espèces</option>
                        <option value="mobile">📱 Mobile Money</option>
                        <option value="carte">💳 Carte</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small">💰 Montant payé</label>
                    <input type="number" id="montantPaye" class="form-control" min="0" onchange="updateTotals()">
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Monnaie</span>
                    <span id="monnaie" class="text-success fw-bold">0 FCFA</span>
                </div>
                <button class="btn btn-success btn-lg w-100" onclick="validerVente()" id="btnValider" disabled>
                    ✅ Valider la vente
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cart = [];

function addToCart(id, name, price, stock) {
    if (stock <= 0) { alert('❌ Stock insuffisant !'); return; }
    const existing = cart.find(i => i.product_id === id);
    if (existing) {
        if (existing.quantite >= stock) { alert('❌ Stock insuffisant !'); return; }
        existing.quantite++;
    } else {
        cart.push({ product_id: id, name, price, quantite: 1, stock });
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(i => i.product_id !== id);
    renderCart();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.product_id === id);
    if (!item) return;
    item.quantite += delta;
    if (item.quantite <= 0) removeFromCart(id);
    else if (item.quantite > item.stock) { item.quantite = item.stock; alert('❌ Stock max atteint'); }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-muted text-center" id="emptyCart">Panier vide — cliquez sur un produit</p>';
        document.getElementById('btnValider').disabled = true;
        updateTotals();
        return;
    }
    container.innerHTML = cart.map(item => `
        <div class="cart-item py-2 d-flex justify-content-between align-items-center">
            <div>
                <strong>${item.name}</strong><br>
                <small class="text-muted">${item.price.toLocaleString()} × ${item.quantite}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${item.product_id}, -1)">−</button>
                <span>${item.quantite}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${item.product_id}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.product_id})">🗑️</button>
            </div>
        </div>
    `).join('');
    document.getElementById('btnValider').disabled = false;
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + i.price * i.quantite, 0);
    const remise = parseFloat(document.getElementById('remise').value) || 0;
    const total = Math.max(0, subtotal - remise);
    const paye = parseFloat(document.getElementById('montantPaye').value) || 0;
    document.getElementById('subtotal').textContent = subtotal.toLocaleString() + ' FCFA';
    document.getElementById('total').textContent = total.toLocaleString() + ' FCFA';
    document.getElementById('monnaie').textContent = Math.max(0, paye - total).toLocaleString() + ' FCFA';
}

async function validerVente() {
    const remise = parseFloat(document.getElementById('remise').value) || 0;
    const montantPaye = parseFloat(document.getElementById('montantPaye').value) || 0;
    const total = cart.reduce((s, i) => s + i.price * i.quantite, 0) - remise;
    if (montantPaye < total) { alert('❌ Montant insuffisant !'); return; }

    const res = await fetch('{{ route("caisse.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
            items: cart.map(i => ({ product_id: i.product_id, quantite: i.quantite })),
            remise, montant_paye: montantPaye,
            mode_paiement: document.getElementById('modePaiement').value
        })
    });
    const data = await res.json();
    if (data.success) {
        alert('✅ Vente enregistrée !');
        window.open(data.ticket_url, '_blank');
        cart = [];
        renderCart();
        document.getElementById('remise').value = 0;
        document.getElementById('montantPaye').value = '';
        location.reload();
    } else {
        alert('❌ ' + data.message);
    }
}

document.getElementById('searchProduct').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

document.getElementById('filterCategory').addEventListener('change', function() {
    const cat = this.value;
    document.querySelectorAll('.product-item').forEach(el => {
        el.style.display = !cat || el.dataset.category === cat ? '' : 'none';
    });
});
</script>
@endsection
