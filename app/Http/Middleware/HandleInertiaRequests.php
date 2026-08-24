<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'company' => config('company'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'i18n' => [
                'locale' => app()->getLocale(),
                'direction' => $this->localeSetting('dir', 'ltr'),
                'font' => $this->localeSetting('font', 'sans'),
                'available' => collect(config('localization.supported'))
                    ->map(fn (array $meta, string $code) => [
                        'code' => $code,
                        'short' => $meta['short'],
                        'native' => $meta['native'],
                        'label' => $meta['label'],
                    ])
                    ->values(),
                // Le dictionnaire complet est envoyé au navigateur : les
                // composants Vue traduisent sans aller-retour serveur.
                'messages' => $this->messages(),
            ],
        ];
    }

    private function localeSetting(string $key, string $default): string
    {
        return config('localization.supported.'.app()->getLocale().'.'.$key, $default);
    }

    /**
     * Messages de la langue courante, complétés par ceux de la langue par
     * défaut : une clé oubliée en arabe s'affiche en français plutôt que
     * de laisser apparaître la clé brute.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        $fallback = $this->readTranslations(config('localization.default', 'fr'));
        $current = $this->readTranslations(app()->getLocale());

        return array_merge($fallback, $current);
    }

    /**
     * @return array<string, string>
     */
    private function readTranslations(string $locale): array
    {
        $path = lang_path($locale.'.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }
}
