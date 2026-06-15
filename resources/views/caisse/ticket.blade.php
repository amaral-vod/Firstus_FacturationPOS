<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $vente->numero_facture }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: {{ $settings['ticket_width'] }}mm;
            margin: 0 auto;
            padding: 5mm;
        }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <strong>{{ $settings['nom_magasin'] }}</strong><br>
        {{ $settings['adresse'] }}<br>
        {{ $settings['telephone'] }}
    </div>
    <div class="line"></div>
    <div class="center">
        <strong>🧾 TICKET DE CAISSE</strong><br>
        N° {{ $vente->numero_facture }}<br>
        {{ $vente->created_at->format('d/m/Y H:i') }}
    </div>
    <div class="line"></div>
    <table>
        @foreach($vente->details as $detail)
        <tr>
            <td>{{ $detail->product->name }}<br><small>{{ $detail->quantite }} x {{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</small></td>
            <td class="right">{{ number_format($detail->total_ligne, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>
    <div class="line"></div>
    @php $totalArticles = $vente->details->sum('quantite'); @endphp
    <table>
        <tr><td>Nb total d'articles</td><td class="right"><strong>{{ $totalArticles }}</strong></td></tr>
        <tr><td>Sous-total</td><td class="right">{{ number_format($vente->sous_total, 0, ',', ' ') }} FCFA</td></tr>
        @if($vente->remise > 0)
        <tr><td>Remise</td><td class="right">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        <tr><td><strong>TOTAL</strong></td><td class="right"><strong>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</strong></td></tr>
        <tr><td>Payé ({{ $vente->mode_paiement }})</td><td class="right">{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Monnaie</td><td class="right">{{ number_format($vente->monnaie, 0, ',', ' ') }} FCFA</td></tr>
    </table>
    <div class="line"></div>
    <div class="center">
        <small>Caissier: {{ $vente->user->name }}</small><br>
        <strong>✨ Merci de votre visite ! ✨</strong>
    </div>
    <div class="no-print center" style="margin-top:20px">
        <button onclick="window.print()">🖨️ Imprimer</button>
    </div>
</body>
</html>
