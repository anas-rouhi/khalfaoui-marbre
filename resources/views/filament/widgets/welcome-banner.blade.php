{{--
    Bannière d'accueil du tableau de bord.

    Styles écrits ici, préfixés « km- », et non en classes utilitaires : la
    feuille précompilée de Filament ne contient pas les classes arbitraires du
    projet (voir brand-logo.blade.php). Propriétés logiques (inline-start/end)
    pour que la mise en page se retourne d'elle-même en arabe.
--}}
<x-filament-widgets::widget class="km-welcome-widget">
    @if (app()->getLocale() !== 'ar')
        {{-- Titre en Cormorant Garamond, comme le site vitrine. L'arabe garde
             Cairo, déjà chargée par arabic-font.blade.php. --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600&display=swap" rel="stylesheet" />
    @endif

    <style>
        .km-welcome {
            --km-gold: #d9b56a;
            --km-gold-deep: #b98a3c;
            --km-gold-soft: #f1dfb5;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            border-radius: 1rem;
            padding: 2rem;
            color: #fff;
            background:
                radial-gradient(ellipse 60% 90% at 100% 0%, rgba(30, 157, 107, .22), transparent 60%),
                radial-gradient(ellipse 50% 70% at 0% 100%, rgba(217, 181, 106, .14), transparent 65%),
                linear-gradient(135deg, #141519 0%, #0a0b0d 55%, #0d1714 100%);
            border: 1px solid rgba(217, 181, 106, .28);
            box-shadow: 0 30px 60px -28px rgba(0, 0, 0, .55), inset 0 1px 0 rgba(255, 255, 255, .06);
        }

        /* Filet doré en tête, comme une tranche de dalle polie. */
        .km-welcome::before {
            content: "";
            position: absolute;
            inset-inline: 0;
            top: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--km-gold), transparent);
            opacity: .8;
        }

        .km-welcome__veins {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .km-welcome__mark {
            position: absolute;
            inset-inline-end: -1.5rem;
            top: 50%;
            height: 15rem;
            width: auto;
            transform: translateY(-50%);
            opacity: .07;
            z-index: -1;
            pointer-events: none;
        }

        .km-welcome__body {
            position: relative;
            max-width: 46rem;
        }

        .km-welcome__eyebrow {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem .75rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--km-gold);
        }

        .km-welcome__rule {
            display: inline-block;
            width: 2rem;
            height: 1px;
            background: currentColor;
        }

        .km-welcome__date {
            font-weight: 500;
            letter-spacing: .04em;
            text-transform: none;
            color: rgba(255, 255, 255, .55);
        }

        .km-welcome__title {
            margin-top: .875rem;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(1.625rem, 1.2rem + 1.5vw, 2.375rem);
            font-weight: 500;
            line-height: 1.15;
            color: rgba(255, 255, 255, .92);
        }

        .km-welcome__brand {
            display: block;
            font-weight: 600;
            letter-spacing: .02em;
            background: linear-gradient(90deg, var(--km-gold-soft), var(--km-gold) 45%, var(--km-gold-deep));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        html[lang="ar"] .km-welcome__title {
            font-family: inherit;
            font-weight: 600;
            line-height: 1.5;
        }

        html[lang="ar"] .km-welcome__eyebrow {
            letter-spacing: 0;
        }

        .km-welcome__lead {
            margin-top: .75rem;
            max-width: 38rem;
            font-size: .9375rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, .62);
        }

        .km-welcome__actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-top: 1.75rem;
        }

        .km-welcome__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 2.75rem;
            padding: .625rem 1.125rem;
            border-radius: .625rem;
            font-size: .875rem;
            font-weight: 600;
            line-height: 1.25;
            text-decoration: none;
            transition: background-color .15s, border-color .15s, color .15s, box-shadow .15s, transform .15s;
        }

        .km-welcome__btn:focus-visible {
            outline: 2px solid var(--km-gold);
            outline-offset: 2px;
        }

        .km-welcome__btn--primary {
            color: #141519;
            background: linear-gradient(135deg, var(--km-gold-soft), var(--km-gold) 40%, var(--km-gold-deep));
            box-shadow: 0 10px 24px -12px rgba(217, 181, 106, .8);
        }

        .km-welcome__btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -12px rgba(217, 181, 106, .9);
        }

        .km-welcome__btn--outline {
            color: var(--km-gold-soft);
            border: 1px solid rgba(217, 181, 106, .45);
            background: rgba(217, 181, 106, .06);
        }

        .km-welcome__btn--outline:hover {
            border-color: var(--km-gold);
            background: rgba(217, 181, 106, .14);
        }

        .km-welcome__btn--ghost {
            color: rgba(255, 255, 255, .78);
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .km-welcome__btn--ghost:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, .3);
            background: rgba(255, 255, 255, .05);
        }

        .km-welcome__icon {
            width: 1.125rem;
            height: 1.125rem;
            flex-shrink: 0;
        }

        @media (max-width: 640px) {
            .km-welcome {
                padding: 1.5rem 1.25rem;
            }

            .km-welcome__mark {
                height: 10rem;
                top: 1.5rem;
                transform: none;
            }

            .km-welcome__btn {
                flex: 1 1 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .km-welcome__btn {
                transition: none;
            }

            .km-welcome__btn--primary:hover {
                transform: none;
            }
        }
    </style>

    <div class="km-welcome">
        {{-- Veinage de marbre, purement décoratif. --}}
        <svg class="km-welcome__veins" viewBox="0 0 800 260" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
            <g fill="none" stroke-linecap="round">
                <path d="M-20 210 C 120 170, 190 230, 320 180 S 520 90, 640 130 S 780 60, 840 40" stroke="rgba(217,181,106,.22)" stroke-width="1.2" />
                <path d="M-20 230 C 140 200, 230 250, 360 205 S 560 130, 700 165" stroke="rgba(217,181,106,.1)" stroke-width="3" />
                <path d="M120 -10 C 170 60, 150 110, 230 150 S 300 240, 280 280" stroke="rgba(255,255,255,.05)" stroke-width="1" />
                <path d="M520 -10 C 540 50, 600 70, 610 130 S 660 220, 720 270" stroke="rgba(255,255,255,.06)" stroke-width="1.5" />
            </g>
        </svg>

        <img class="km-welcome__mark" src="{{ asset(ltrim(config('company.logo_mark'), '/')) }}" alt="" aria-hidden="true" />

        <div class="km-welcome__body">
            <p class="km-welcome__eyebrow">
                <span class="km-welcome__rule" aria-hidden="true"></span>
                <span>{{ filled($userName) ? __('Bonjour, :name', ['name' => $userName]) : __('Bonjour') }}</span>
                <time class="km-welcome__date" datetime="{{ now()->toDateString() }}">{{ $today }}</time>
            </p>

            <h2 class="km-welcome__title">
                {{ __('Bienvenue sur l\'espace d\'administration de') }}
                <span class="km-welcome__brand">{{ __('Khalfaoui Marbre') }}</span>
            </h2>

            <p class="km-welcome__lead">
                {{ __('Pilotez votre catalogue, vos réalisations et vos demandes de devis depuis un seul espace.') }}
            </p>

            <nav class="km-welcome__actions" aria-label="{{ __('Raccourcis') }}">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="km-welcome__btn km-welcome__btn--{{ $action['style'] }}"
                        @if ($action['external'] ?? false) target="_blank" rel="noopener" @endif
                    >
                        <x-filament::icon :icon="$action['icon']" class="km-welcome__icon" />
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</x-filament-widgets::widget>
