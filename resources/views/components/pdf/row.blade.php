{{--
    Ligne à deux colonnes du devis PDF.

    dompdf ignore `direction: rtl` : en arabe, l'ordre des cellules est donc
    inversé ici, la colonne « start » passant à droite. L'alignement et les
    marges intérieures restent fournis par le gabarit, qui les exprime déjà
    en fonction du sens de lecture.
--}}
@props(['rtl' => false, 'startStyle' => '', 'endStyle' => ''])
<table {{ $attributes }}>
    <tr>
        @if ($rtl)
            <td style="{{ $endStyle }}">{{ $end }}</td>
            <td style="{{ $startStyle }}">{{ $start }}</td>
        @else
            <td style="{{ $startStyle }}">{{ $start }}</td>
            <td style="{{ $endStyle }}">{{ $end }}</td>
        @endif
    </tr>
</table>
