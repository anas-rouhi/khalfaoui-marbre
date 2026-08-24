{{--
    Logo du back-office.

    La signature est dessinée en blanc : posée telle quelle sur le fond clair
    de Filament, elle serait illisible. On la pose donc sur un socle sombre.

    Les couleurs et les dimensions sont écrites en style « inline » et non en
    classes utilitaires : cette vue est rendue dans le panneau Filament, qui
    charge sa propre feuille de style précompilée. Les classes arbitraires du
    projet (bg-[#0d1714], border-white/10…) n'y existent pas et seraient
    silencieusement ignorées — le socle resterait blanc.
--}}
<span style="display:inline-flex;align-items:center;background:#0d1714;border:1px solid rgba(255,255,255,.12);border-radius:.75rem;padding:.375rem .75rem;box-shadow:0 1px 2px rgba(0,0,0,.15);">
    <img
        src="{{ asset(ltrim(config('company.logo'), '/')) }}"
        alt="{{ config('company.legal_name') }}"
        {{-- La signature mesure ~5,6 fois sa hauteur : en dessous de 36 px le
             lettrage n'est plus lisible. --}}
        style="height:2.25rem;width:auto;object-fit:contain;display:block;"
    />
</span>
