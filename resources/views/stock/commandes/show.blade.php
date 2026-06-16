@extends('layouts.app')

@section('page-title', '🛒 '.$order->reference)

@section('content')
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1">{{ $order->reference }}</h5>
        <small class="text-muted">
            {{ $order->fournisseur->name }} — {{ $order->site?->name ?? 'Tous sites' }} —
            <span class="badge bg-secondary">{{ $order->statusLabel() }}</span>
        </small>
    </div>
    <a href="{{ route('stock.commandes.index', ['site_id' => $order->site_id]) }}" class="btn btn-outline-secondary btn-sm">← Liste</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-modern">
            <div class="card-header bg-white"><h6 class="mb-0">Lignes de commande</h6></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Produit</th><th>Commandé</th><th>Reçu</th><th>P.U.</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->lines as $line)
                        <tr>
                            <td>{{ $line->product->name }}</td>
                            <td>{{ $line->quantity_ordered }}</td>
                            <td>{{ $line->quantity_received }}</td>
                            <td>{{ number_format($line->unit_cost, 0, ',', ' ') }}</td>
                            <td>{{ number_format($line->lineTotal(), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4" class="text-end">Total</th>
                            <th>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if(in_array($order->status, ['brouillon', 'envoyee']))
        <div class="card card-modern mb-3">
            <div class="card-header bg-white"><h6 class="mb-0">Statut</h6></div>
            <div class="card-body">
                <form action="{{ route('stock.commandes.status', $order) }}" method="POST">
                    @csrf @method('PUT')
                    <select name="status" class="form-select mb-2">
                        <option value="brouillon" @selected($order->status === 'brouillon')>Brouillon</option>
                        <option value="envoyee" @selected($order->status === 'envoyee')>Envoyée</option>
                        <option value="annulee">Annulée</option>
                    </select>
                    <button class="btn btn-outline-primary w-100">Mettre à jour</button>
                </form>
            </div>
        </div>
        @endif

        @if($order->isEditable())
        <div class="card card-modern">
            <div class="card-header bg-success text-white"><h6 class="mb-0">📥 Réception</h6></div>
            <div class="card-body">
                <form action="{{ route('stock.commandes.receive', $order) }}" method="POST">
                    @csrf
                    @foreach($order->lines as $line)
                    @if($line->remainingQty() > 0)
                    <label class="form-label small">{{ $line->product->name }} (reste {{ $line->remainingQty() }})</label>
                    <input type="number" name="received[{{ $line->id }}]" class="form-control mb-2" min="0" max="{{ $line->remainingQty() }}" placeholder="Qté reçue">
                    @endif
                    @endforeach
                    <button class="btn btn-success w-100 mt-2">Enregistrer la réception</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
