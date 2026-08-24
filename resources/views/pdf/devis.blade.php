{{--
    Devis estimatif — rendu par dompdf.

    Contraintes dompdf : pas de flexbox ni de grid, pas de webfont distante.
    La mise en page repose donc sur des tableaux.

    Helvetica est une police de base du format PDF : elle n'est pas embarquée
    dans le fichier (≈ 30 Ko au lieu de 900 Ko avec DejaVu Sans) et restitue
    correctement tous les caractères français utilisés ici — accents, ç, œ,
    m² et tiret cadratin.
--}}
@php
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $green = '#1e9d6b';
    $ink = '#141519';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $devis->reference }}</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: {{ $ink }};
        }
        .band { background: #0d1714; padding: 22px 38px; }
        .wrap { padding: 0 38px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .muted { color: #6b7076; }
        .label {
            font-size: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: {{ $green }};
        }
        h1 {
            font-size: 21px;
            font-weight: normal;
            letter-spacing: 1px;
            margin: 4px 0 0;
        }
        .card {
            border: 1px solid #e3e5e7;
            border-radius: 6px;
            padding: 14px 16px;
        }
        .card h2 {
            font-size: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #8b9096;
            margin: 0 0 8px;
            font-weight: normal;
        }
        .items th {
            font-size: 8px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #8b9096;
            font-weight: normal;
            text-align: left;
            padding: 0 0 7px;
            border-bottom: 1px solid #e3e5e7;
        }
        .items td { padding: 11px 0; border-bottom: 1px solid #f0f1f2; }
        .right { text-align: right; }
        .total-band {
            background: {{ $ink }};
            color: #fff;
            padding: 14px 18px;
            border-radius: 6px;
        }
        .terms { font-size: 8.5px; color: #6b7076; line-height: 1.65; }
        .terms li { margin-bottom: 3px; }
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #0d1714;
            color: #c5c7cb;
            padding: 12px 38px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

{{-- ══ En-tête : bandeau sombre, le logo étant dessiné en blanc ══ --}}
<div class="band">
    <table>
        <tr>
            <td style="width: 47%;">
                @if (is_file($logo))
                    <img src="{{ $logo }}" style="width: 218px;" alt="{{ $company['legal_name'] }}">
                @else
                    <span style="color:#fff; font-size:16px; letter-spacing:2px;">{{ $company['legal_name'] }}</span>
                @endif
            </td>
            <td class="right" style="color:#9fa2a9; font-size:9px; line-height:1.75; padding-top:6px;">
                {{ $company['address'] }}<br>
                Tél / WhatsApp : {{ $company['phone_display'] }}<br>
                {{ $company['email'] }}
            </td>
        </tr>
    </table>
</div>

<div class="wrap">

    {{-- ══ Titre et référence ══ --}}
    <table style="margin-top: 26px;">
        <tr>
            <td>
                <div class="label">Devis estimatif</div>
                <h1>Offre de prix</h1>
            </td>
            <td class="right muted" style="font-size:9.5px; line-height:1.8;">
                Référence : <strong style="color:{{ $ink }};">{{ $devis->reference }}</strong><br>
                Date : {{ ($devis->created_at ?? now())->format('d/m/Y') }}<br>
                Validité : 30 jours
            </td>
        </tr>
    </table>

    {{-- ══ Client et chantier ══ --}}
    <table style="margin-top: 22px;">
        <tr>
            <td style="width: 49%; padding-right: 9px;">
                <div class="card">
                    <h2>Client</h2>
                    <strong style="font-size:12px;">{{ $devis->client_name }}</strong><br>
                    <span class="muted" style="line-height:1.8;">
                        {{ $devis->phone }}<br>
                        @if ($devis->email){{ $devis->email }}<br>@endif
                        @if ($devis->location){{ $devis->location }}@endif
                    </span>
                </div>
            </td>
            <td style="width: 51%; padding-left: 9px;">
                <div class="card">
                    <h2>Chantier</h2>
                    <table>
                        <tr>
                            @if ($thumbnail)
                                <td style="width: 74px;">
                                    <img src="{{ $thumbnail }}" style="width: 66px; height: 50px; border-radius: 4px;" alt="">
                                </td>
                            @endif
                            <td>
                                <strong style="font-size:12px;">{{ $product?->name ?? 'Matériau à définir' }}</strong><br>
                                <span class="muted" style="line-height:1.8;">
                                    {{ $applicationLabel }}<br>
                                    {{ rtrim(rtrim(number_format($surface, 2, ',', ' '), '0'), ',') }} m²
                                    @if ($product?->finish) · {{ $product->finish }} @endif
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ══ Détail chiffré ══ --}}
    <table class="items" style="margin-top: 24px;">
        <tr>
            <th style="width: 46%;">Désignation</th>
            <th class="right" style="width: 18%;">Prix unitaire</th>
            <th class="right" style="width: 15%;">Quantité</th>
            <th class="right" style="width: 21%;">Montant</th>
        </tr>
        <tr>
            <td>
                <strong>Fourniture — {{ $product?->name ?? 'Matériau' }}</strong>
                @if ($product?->origin)<br><span class="muted" style="font-size:9px;">Origine {{ $product->origin }}</span>@endif
            </td>
            <td class="right">{{ $money($materialRate) }} DH/m²</td>
            <td class="right">{{ rtrim(rtrim(number_format($surface, 2, ',', ' '), '0'), ',') }} m²</td>
            <td class="right">{{ $money($materialCost) }} DH</td>
        </tr>
        <tr>
            <td>
                <strong>Pose et finitions</strong><br>
                <span class="muted" style="font-size:9px;">{{ $applicationLabel }} — main d'œuvre, découpes et chants</span>
            </td>
            <td class="right">{{ $money($installationRate) }} DH/m²</td>
            <td class="right">{{ rtrim(rtrim(number_format($surface, 2, ',', ' '), '0'), ',') }} m²</td>
            <td class="right">{{ $money($installationCost) }} DH</td>
        </tr>
    </table>

    {{-- ══ Total ══ --}}
    <table style="margin-top: 16px;">
        <tr>
            <td style="width: 52%;"></td>
            <td>
                <div class="total-band">
                    <table>
                        <tr>
                            <td style="color:#9fa2a9; font-size:9px; letter-spacing:1.5px; text-transform:uppercase;">
                                Total estimé
                            </td>
                            <td class="right" style="color:#43bd88; font-size:17px;">
                                {{ $money($total) }} DH
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if ($devis->message)
        <div class="card" style="margin-top: 18px;">
            <h2>Précisions du client</h2>
            <span class="muted">{{ $devis->message }}</span>
        </div>
    @endif

    {{-- ══ Conditions ══ --}}
    <div style="margin-top: 22px;">
        <div class="label" style="color:#8b9096;">Conditions</div>
        <ul class="terms" style="margin: 7px 0 0; padding-left: 15px;">
            <li>Devis <strong>estimatif</strong> établi à partir des informations communiquées en ligne. Le prix définitif est arrêté après métré contradictoire sur site.</li>
            <li>Montants exprimés en dirhams, toutes taxes comprises.</li>
            <li>Prix hors transport, dépose de l'existant, travaux de maçonnerie et raccordements de plomberie ou d'électricité.</li>
            <li>La pierre naturelle est un matériau vivant : le veinage et la teinte varient d'un bloc à l'autre. Les tranches sont validées avec le client avant découpe.</li>
            <li>Offre valable 30 jours à compter de la date d'émission, sous réserve de disponibilité en stock.</li>
            <li>Délai indicatif de réalisation : 2 à 5 semaines selon la nature du chantier.</li>
        </ul>
    </div>

    {{-- ══ Appel à l'action ══ --}}
    <div style="margin-top: 20px; border: 1px solid {{ $green }}; border-radius: 6px; padding: 14px 18px;">
        <table>
            <tr>
                <td>
                    <strong style="font-size:12px;">Passons à l'étape suivante</strong><br>
                    <span class="muted">Nous convenons d'un métré gratuit, chez vous, sous 48 heures.</span>
                </td>
                <td class="right" style="line-height:1.7;">
                    <strong style="color:{{ $green }}; font-size:13px;">{{ $company['phone_display'] }}</strong><br>
                    <span class="muted" style="font-size:9px;">{{ $company['email'] }}</span>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="footer">
    <table>
        <tr>
            <td>{{ $company['legal_name'] }} — {{ $company['address'] }}</td>
            <td class="right">{{ $devis->reference }}</td>
        </tr>
    </table>
</div>

</body>
</html>
