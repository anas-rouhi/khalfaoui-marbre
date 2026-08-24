<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'location' => ['nullable', 'string', 'max:190'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'application' => ['nullable', 'string', 'max:60'],
            'surface_m2' => ['nullable', 'numeric', 'min:0.5', 'max:100000'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
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
            'phone.max' => 'Ce numéro de téléphone est trop long.',
            'email.email' => 'Cette adresse e-mail ne semble pas valide.',
            'location.max' => 'La localisation est trop longue.',
            'product_id.integer' => 'Merci de choisir une référence dans la liste.',
            'product_id.exists' => 'Cette référence n\'est plus disponible, merci d\'en choisir une autre.',
            'surface_m2.numeric' => 'La surface doit être un nombre.',
            'surface_m2.min' => 'La surface doit être d\'au moins 0,5 m².',
            'surface_m2.max' => 'Merci de nous contacter directement pour une surface de cette ampleur.',
            'message.max' => 'Votre message dépasse 2000 caractères.',
        ];
    }
}
