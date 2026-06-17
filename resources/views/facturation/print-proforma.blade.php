<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Proforma {{ $facture->numero }}</title>
    @php
        $isTicket = $format === 'ticket';
        $isA5 = $format === 'a5';
        $pageSize = $isTicket ? '80mm auto' : ($isA5 ? 'A5 portrait' : 'A4 portrait');
        $baseFont = $isTicket ? '11px' : ($isA5 ? '11px' : '12px');
        $titleSize = $isTicket ? '13px' : ($isA5 ? '16px' : '20px');
        $bodyWidth = $isTicket ? '80mm' : '100%';
        $bodyPad = $isTicket ? '4mm' : ($isA5 ? '8mm' : '12mm');
    @endphp
    <style>
        @page { size: {{ $pageSize }}; margin: {{ $isTicket ? '2mm' : '10mm' }}; }
        * { box-sizing: border-box; }
        body {
            font-family: {{ $isTicket ? "'Courier New', monospace" : "'Segoe UI', Arial, sans-serif" }};
            font-size: {{ $baseFont }};
            width: {{ $bodyWidth }};
            max-width: {{ $isTicket ? '80mm' : 'none' }};
            margin: 0 auto;
            padding: {{ $bodyPad }};
            color: #111;
        }
        .header { text-align: center; margin-bottom: {{ $isTicket ? '6px' : '12px' }}; }
        .header h1 { font-size: {{ $titleSize }}; margin: 0 0 4px; }
        .header .meta { font-size: {{ $isTicket ? '10px' : '11px' }}; color: #444; }
        .badge {
            display: inline-block;
            border: 1px solid #333;
            padding: 2px 8px;
            font-weight: bold;
            font-size: {{ $isTicket ? '10px' : '12px' }};
            margin: 6px 0;
        }
        .grid {
            display: {{ $isTicket ? 'block' : 'flex' }};
            justify-content: space-between;
            gap: 12px;
            margin-bottom: {{ $isTicket ? '8px' : '14px' }};
        }
        .box {
            border: 1px solid #ccc;
            padding: {{ $isTicket ? '4px 6px' : '8px 10px' }};
            flex: 1;
            margin-bottom: {{ $isTicket ? '6px' : '0' }};
        }
        .box strong { display: block; margin-bottom: 4px; font-size: {{ $isTicket ? '10px' : '11px' }}; text-transform: uppercase; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.items th, table.items td {
            border-bottom: 1px solid #ddd;
            padding: {{ $isTicket ? '3px 2px' : '6px 4px' }};
            text-align: left;
            vertical-align: top;
        }
        table.items th { font-size: {{ $isTicket ? '9px' : '10px' }}; text-transform: uppercase; background: #f5f5f5; }
        .right { text-align: right; }
        .totals { width: {{ $isTicket ? '100%' : '50%' }}; margin-left: auto; }
        .totals td { padding: 3px 0; }
        .totals .grand td { font-size: {{ $isTicket ? '12px' : '14px' }}; font-weight: bold; border-top: 2px solid #111; padding-top: 6px; }
        .notes {
            margin-top: 10px;
            padding: 6px 8px;
            border: 1px dashed #999;
            font-size: {{ $isTicket ? '10px' : '11px' }};
        }
        .footer {
            margin-top: {{ $isTicket ? '10px' : '20px' }};
            text-align: center;
            font-size: {{ $isTicket ? '9px' : '10px' }};
            color: #555;
        }
        .format-tag {
            position: fixed;
            top: 4px;
            right: 4px;
            background: #eef2ff;
            border: 1px solid #6366f1;
            color: #3730a3;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none !important; }
            .format-tag { position: static; float: right; margin-bottom: 4px; }
            body { page-break-inside: avoid; }
            .header, .grid, table.items, .totals, .notes, .footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print format-tag">Format : {{ \App\Services\ProformaFormatService::label($format) }}</div>

    <div class="header">
        <h1>{{ $settings['nom_magasin'] }}</h1>
        <div class="meta">
            @if($settings['adresse']){{ $settings['adresse'] }}<br>@endif
            @if($settings['telephone'])Tél : {{ $settings['telephone'] }}@endif
            @if($settings['email']) — {{ $settings['email'] }}@endif
            @if($settings['ifu'])<br>IFU : {{ $settings['ifu'] }}@endif
            @if($settings['rccm']) — RCCM : {{ $settings['rccm'] }}@endif
        </div>
        <div class="badge">FACTURE PROFORMA</div>
        <div><strong>N° {{ $facture->numero }}</strong> — {{ $facture->created_at->format('d/m/Y H:i') }}</div>
        @if($facture->date_echeance)
        <div>Valable jusqu'au {{ $facture->date_echeance->format('d/m/Y') }}</div>
        @endif
    </div>

    <div class="grid">
        <div class="box">
            <strong>Client</strong>
            {{ $facture->clientDisplayName() }}<br>
            @if($facture->client_adresse ?? $facture->client?->address){{ $facture->client_adresse ?? $facture->client?->address }}<br>@endif
            @if($facture->client_telephone ?? $facture->client?->phone)Tél : {{ $facture->client_telephone ?? $facture->client?->phone }}@endif
        </div>
        @unless($isTicket)
        <div class="box">
            <strong>Émis par</strong>
            {{ $facture->user->name }}<br>
            Document sans valeur comptable.<br>
            Ne constitue pas une facture définitive.
        </div>
        @endunless
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="right">Qté</th>
                <th class="right">P.U.</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $ligne)
            <tr>
                <td>{{ $ligne->designation }}</td>
                <td class="right">{{ $ligne->quantite }}</td>
                <td class="right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($ligne->total_ligne, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Nb total d'articles</td><td class="right"><strong>{{ $totalArticles }}</strong></td></tr>
        <tr><td>Sous-total</td><td class="right">{{ number_format($facture->sous_total, 0, ',', ' ') }} {{ $settings['devise'] }}</td></tr>
        @if($facture->remise > 0)
        <tr><td>Remise</td><td class="right">-{{ number_format($facture->remise, 0, ',', ' ') }} {{ $settings['devise'] }}</td></tr>
        @endif
        @if((float)$settings['tva'] > 0)
        <tr><td>TVA ({{ $settings['tva'] }}%)</td><td class="right">{{ number_format($facture->tva, 0, ',', ' ') }} {{ $settings['devise'] }}</td></tr>
        @endif
        <tr class="grand"><td>TOTAL</td><td class="right">{{ number_format($facture->total, 0, ',', ' ') }} {{ $settings['devise'] }}</td></tr>
    </table>

    @if($facture->notes)
    <div class="notes"><strong>Notes :</strong> {{ $facture->notes }}</div>
    @endif

    <div class="footer">
        Document proforma — {{ $settings['nom_magasin'] }}
        @if($isTicket)<br><small>Émis par {{ $facture->user->name }}</small>@endif
    </div>

    <div class="no-print" style="text-align:center;margin-top:20px">
        <button onclick="window.print()" style="padding:8px 16px;cursor:pointer">🖨️ Imprimer</button>
        <a href="{{ route('facturation.index', ['type' => 'proforma']) }}" style="margin-left:8px">← Liste proformas</a>
        @if($facture->format_papier === 'auto')
        <div style="margin-top:8px;font-size:12px">
            Autre format :
            <a href="?format=a4">A4</a> |
            <a href="?format=a5">A5</a> |
            <a href="?format=ticket">Ticket</a>
        </div>
        @endif
    </div>
</body>
</html>
