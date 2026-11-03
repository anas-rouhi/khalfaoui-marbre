<?php

namespace App\Http\Requests;

use App\Models\InstallationRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevisRequest extends FormRequest
{
    /** Champ piège invisible : un humain le laisse vide, un robot le remplit. */
    public const HONEYPOT = 'website';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Le numéro est ramené à sa forme compacte avant validation : les
     * visiteurs écrivent « 06 12 34 56 78 », « 06.12.34.56.78 » ou
     * « +212 (0)6 12… », que la règle stricte doit tous accepter.
     */
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('phone'))) {
            return;
        }

        $phone = preg_replace('/[\s.\-()]/', '', $this->input('phone'));
        $phone = preg_replace('/^00212/', '+212', $phone);
        $phone = preg_replace('/^\+2120/', '+212', $phone);

        $this->merge(['phone' => $phone]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^(\+212|0)[5-7]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'location' => ['nullable', 'string', 'max:190'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'application' => ['nullable', 'string', Rule::in($this->allowedApplications())],
            'surface_m2' => ['nullable', 'numeric', 'min:0.5', 'max:100000'],
            'message' => ['nullable', 'string', 'max:2000'],
            self::HONEYPOT => ['prohibited'],
        ];
    }

    /**
     * Données à enregistrer : le champ piège n'a pas de colonne en base.
     *
     * @return array<string, mixed>
     */
    public function devisData(): array
    {
        return $this->safe()->except(self::HONEYPOT);
    }

    /**
     * Usages administrés depuis le back-office, complétés par la liste de
     * référence : quand la table des tarifs est vide, le formulaire propose
     * ces usages par défaut et doit rester utilisable.
     *
     * @return array<int, string>
     */
    private function allowedApplications(): array
    {
        return InstallationRate::pluck('application')
            ->merge(array_column(InstallationRate::DEFAULTS, 'application'))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_name' => 'nom complet',
            'phone' => 'téléphone',
            'email' => 'adresse e-mail',
            'location' => 'ville / localisation',
            'product_id' => 'type de marbre',
            'application' => 'application',
            'surface_m2' => 'surface',
            'message' => 'message',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_name.required' => 'Merci d\'indiquer votre nom complet.',
            'client_name.max' => 'Le nom ne doit pas dépasser 120 caractères.',
            'phone.required' => 'Un numéro de téléphone est nécessaire pour vous rappeler.',
            'phone.regex' => 'Merci d\'indiquer un numéro marocain valide, par exemple 06 12 34 56 78 ou +212 6 12 34 56 78.',
            'email.email' => 'Cette adresse e-mail ne semble pas valide.',
            'location.max' => 'La localisation est trop longue.',
            'product_id.integer' => 'Merci de choisir une référence dans la liste.',
            'product_id.exists' => 'Cette référence n\'est plus disponible, merci d\'en choisir une autre.',
            'application.in' => 'Merci de choisir un type de pose dans la liste.',
            'surface_m2.numeric' => 'La surface doit être un nombre.',
            'surface_m2.min' => 'La surface doit être d\'au moins 0,5 m².',
            'surface_m2.max' => 'Merci de nous contacter directement pour une surface de cette ampleur.',
            'message.max' => 'Votre message dépasse 2000 caractères.',
            self::HONEYPOT.'.prohibited' => 'Votre demande n\'a pas pu être envoyée.',
        ];
    }
}
