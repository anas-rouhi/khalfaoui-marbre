{{--
    Devis estimatif — rendu par dompdf, en français ou en arabe.

    Contraintes dompdf : pas de flexbox ni de grid, pas de webfont distante,
    pas de `direction: rtl`. La mise en page repose donc sur des tableaux, et
    la version arabe inverse elle-même l'ordre des colonnes (<x-pdf.row>).

    Police : Helvetica est une police de base du format PDF, non embarquée
    (≈ 30 Ko). Elle ignore l'arabe : dès qu'un texte arabe est présent —
    libellés ou saisie du client — le contrôleur bascule sur DejaVu Sans,
    livrée avec dompdf et embarquée en sous-ensemble.

    Texte arabe : chaque fragment passe par $tx (App\Support\PdfText), qui lie
    les lettres et les range dans l'ordre visuel. Les fragments latins (noms
    de marbre, références, montants) ne sont pas modifiés ; c'est $seq qui
    les ordonne dans le sens de lecture.
--}}
@php
    $tx = fn ($text, $max = 200) => \App\Support\PdfText::shape($text, $max);
    // Texte long découpé en lignes : dompdf ne doit pas couper l'arabe lui-même.
    $lines = fn ($text, $max) => nl2br(e($tx($text, $max)), false);
    // Fragments affichés côte à côte, dans le sens de lecture.
    $seq = fn (array $parts, $sep = ' ') => implode($sep, $rtl
        ? array_reverse(array_map(fn ($part) => $tx($part), array_filter($parts, 'filled')))
        : array_filter($parts, 'filled'));
    $pair = fn ($label, $value) => $seq([__("pdf.$label").' :', $value]);

    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $qty = rtrim(rtrim(number_format($surface, 2, ',', ' '), '0'), ',');
    $dh = fn ($v) => $seq([$money($v), __('pdf.currency')]);
    $dhPerM2 = fn ($v) => $seq([$money($v), __('pdf.currency').'/'.__('pdf.unit')]);
    $area = $seq([$qty, __('pdf.unit')]);

    $start = $rtl ? 'right' : 'left';
    $end = $rtl ? 'left' : 'right';

    $green = '#1e9d6b';
    $ink = '#141519';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ $devis->reference }}</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: {!! $font !!};
            font-size: 10.5px;
            color: {{ $ink }};
        }
        .band { background: #0d1714; padding: 22px 38px; }
        .wrap { padding: 0 38px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; text-align: {{ $start }}; }
        .muted { color: #6b7076; }
        .start { text-align: {{ $start }}; }
        .end { text-align: {{ $end }}; }
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
            padding: 0 0 7px;
            border-bottom: 1px solid #e3e5e7;
        }
        .items td { padding: 11px 0; border-bottom: 1px solid #f0f1f2; }
        .total-band {
            background: {{ $ink }};
            color: #fff;
            padding: 14px 18px;
            border-radius: 6px;
        }
        .total-label { color: #9fa2a9; font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; }
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
        @if ($rtl)
            /* L'espacement des lettres casserait la liaison des caractères arabes. */
            .label, h1, .card h2, .items th, .total-label { letter-spacing: 0; }
            .label, .card h2, .items th { font-size: 9px; }
        @endif
    </style>
</head>
<body>

{{-- ══ En-tête : bandeau sombre, le logo étant dessiné en blanc ══ --}}
<div class="band">
    <x-pdf.row :rtl="$rtl" start-style="width: 47%;" end-style="text-align: {{ $end }}; color:#9fa2a9; font-size:9px; line-height:1.75; padding-top:6px;">
        <x-slot:start>
            @if (is_file($logo))
                <img src="{{ $logo }}" style="width: 218px;" alt="{{ $company['legal_name'] }}">
            @else
                <span style="color:#fff; font-size:16px; letter-spacing:2px;">{{ $company['legal_name'] }}</span>
            @endif
        </x-slot:start>
        <x-slot:end>
            {{ $company['address'] }}<br>
            {{ $pair('phone', $company['phone_display']) }}<br>
            {{ $company['email'] }}
        </x-slot:end>
    </x-pdf.row>
</div>

<div class="wrap">

    {{-- ══ Titre et référence ══ --}}
    <x-pdf.row :rtl="$rtl" style="margin-top: 26px;" end-style="text-align: {{ $end }}; color:#6b7076; font-size:9.5px; line-height:1.8;">
        <x-slot:start>
            <div class="label">{{ $tx(__('pdf.label')) }}</div>
            <h1>{{ $tx(__('pdf.title')) }}</h1>
        </x-slot:start>
        <x-slot:end>
            {{ $pair('reference', $devis->reference) }}<br>
            {{ $pair('date', ($devis->created_at ?? now())->format('d/m/Y')) }}<br>
            {{ $pair('validity', __('pdf.validity_value')) }}
        </x-slot:end>
    </x-pdf.row>

    {{-- ══ Client et chantier ══ --}}
    <x-pdf.row :rtl="$rtl" style="margin-top: 22px;" start-style="width: 49%; padding-{{ $end }}: 9px;" end-style="width: 51%; padding-{{ $start }}: 9px;">
        <x-slot:start>
            <div class="card">
                <h2>{{ $tx(__('pdf.client')) }}</h2>
                <strong style="font-size:12px;">{{ $tx($devis->client_name, 40) }}</strong><br>
                <span class="muted" style="line-height:1.8;">
                    {{ $devis->phone }}<br>
                    @if ($devis->email){{ $devis->email }}<br>@endif
                    @if ($devis->location){{ $tx($devis->location, 45) }}@endif
                </span>
            </div>
        </x-slot:start>
        <x-slot:end>
            <div class="card">
                <h2>{{ $tx(__('pdf.site')) }}</h2>
                <x-pdf.row :rtl="$rtl" :start-style="$thumbnail ? 'width: 74px;' : 'width: 0;'">
                    <x-slot:start>
                        @if ($thumbnail)
                            <img src="{{ $thumbnail }}" style="width: 66px; height: 50px; border-radius: 4px;" alt="">
                        @endif
                    </x-slot:start>
                    <x-slot:end>
                        <strong style="font-size:12px;">{{ $tx($productName ?? __('pdf.material_tbd'), 40) }}</strong><br>
                        <span class="muted" style="line-height:1.8;">
                            {{ $tx($applicationLabel) }}<br>
                            {{-- $area est déjà mis en forme : il ne repasse pas par $seq. --}}
                            {{ implode(' · ', array_filter($rtl ? [$tx($product?->finish), $area] : [$area, $product?->finish], 'filled')) }}
                        </span>
                    </x-slot:end>
                </x-pdf.row>
            </div>
        </x-slot:end>
    </x-pdf.row>

    {{-- ══ Détail chiffré ══ --}}
    @php
        $columns = [
            ['key' => 'designation', 'width' => '46%', 'align' => $start],
            ['key' => 'unit_price', 'width' => '18%', 'align' => $end],
            ['key' => 'quantity', 'width' => '15%', 'align' => $end],
            ['key' => 'amount', 'width' => '21%', 'align' => $end],
        ];
        $rows = [
            [
                'title' => $seq([__('pdf.supply'), $productName ?? __('pdf.material')], ' — '),
                'detail' => $product?->origin ? $seq([__('pdf.origin'), $product->origin]) : null,
                'unit_price' => $dhPerM2($materialRate),
                'amount' => $dh($materialCost),
            ],
            [
                'title' => $tx(__('pdf.installation')),
                'detail' => $seq([$applicationLabel, __('pdf.installation_detail')], ' — '),
                'unit_price' => $dhPerM2($installationRate),
                'amount' => $dh($installationCost),
            ],
        ];
        if ($rtl) {
            $columns = array_reverse($columns);
        }
    @endphp
    <table class="items" style="margin-top: 24px;">
        <tr>
            @foreach ($columns as $column)
                <th style="width: {{ $column['width'] }}; text-align: {{ $column['align'] }};">{{ $tx(__('pdf.'.$column['key'])) }}</th>
            @endforeach
        </tr>
        @foreach ($rows as $row)
            <tr>
                @foreach ($columns as $column)
                    <td style="text-align: {{ $column['align'] }};">
                        @switch($column['key'])
                            @case('designation')
                                <strong>{{ $row['title'] }}</strong>
                                @if ($row['detail'])<br><span class="muted" style="font-size:9px;">{{ $row['detail'] }}</span>@endif
                                @break
                            @case('unit_price')
                                {{ $row['unit_price'] }}
                                @break
                            @case('quantity')
                                {{ $area }}
                                @break
                            @case('amount')
                                {{ $row['amount'] }}
                                @break
                        @endswitch
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    {{-- ══ Total ══ --}}
    <x-pdf.row :rtl="$rtl" style="margin-top: 16px;" start-style="width: 52%;">
        <x-slot:start></x-slot:start>
        <x-slot:end>
            <div class="total-band">
                <x-pdf.row :rtl="$rtl" end-style="text-align: {{ $end }}; color:#43bd88; font-size:17px;">
                    <x-slot:start>
                        <span class="total-label">{{ $tx(__('pdf.total')) }}</span>
                    </x-slot:start>
                    <x-slot:end>{{ $dh($total) }}</x-slot:end>
                </x-pdf.row>
            </div>
        </x-slot:end>
    </x-pdf.row>

    @if ($devis->message)
        <div class="card start" style="margin-top: 18px;">
            <h2>{{ $tx(__('pdf.client_notes')) }}</h2>
            <span class="muted">{!! $lines($devis->message, 95) !!}</span>
        </div>
    @endif

    {{-- ══ Conditions ══ --}}
    <div class="start" style="margin-top: 22px;">
        <div class="label" style="color:#8b9096;">{{ $tx(__('pdf.conditions')) }}</div>
        @if ($rtl)
            {{-- Pas de puces HTML : dompdf les placerait à gauche. --}}
            <div class="terms" style="margin-top: 7px;">
                @foreach (__('pdf.terms') as $term)
                    @php $termLines = explode("\n", $tx($term, 110)); $termLines[0] .= ' •'; @endphp
                    <div style="margin-bottom: 3px;">{!! implode('<br>', array_map('e', $termLines)) !!}</div>
                @endforeach
            </div>
        @else
            <ul class="terms" style="margin: 7px 0 0; padding-left: 15px;">
                @foreach (__('pdf.terms') as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- ══ Appel à l'action ══ --}}
    <div style="margin-top: 20px; border: 1px solid {{ $green }}; border-radius: 6px; padding: 14px 18px;">
        <x-pdf.row :rtl="$rtl" end-style="text-align: {{ $end }}; line-height:1.7;">
            <x-slot:start>
                <strong style="font-size:12px;">{{ $tx(__('pdf.next_step')) }}</strong><br>
                <span class="muted">{{ $tx(__('pdf.next_step_detail')) }}</span>
            </x-slot:start>
            <x-slot:end>
                <strong style="color:{{ $green }}; font-size:13px;">{{ $company['phone_display'] }}</strong><br>
                <span class="muted" style="font-size:9px;">{{ $company['email'] }}</span>
            </x-slot:end>
        </x-pdf.row>
    </div>
</div>

<div class="footer">
    <x-pdf.row :rtl="$rtl" end-style="text-align: {{ $end }};">
        <x-slot:start>{{ $company['legal_name'] }} — {{ $company['address'] }}</x-slot:start>
        <x-slot:end>{{ $devis->reference }}</x-slot:end>
    </x-pdf.row>
</div>

</body>
</html>
