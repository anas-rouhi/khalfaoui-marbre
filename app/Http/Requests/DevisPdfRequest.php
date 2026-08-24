<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Mêmes règles que la demande de devis classique, mais les erreurs sont
 * toujours renvoyées en JSON.
 *
 * La requête du bouton PDF annonce `Accept: application/pdf` : Laravel ne la
 * considère donc pas comme attendant du JSON et répondrait par une
 * redirection. Le `fetch` la suivrait et enregistrerait la page d'accueil
 * sous le nom « devis.pdf ». On force donc une réponse 422 exploitable.
 */
class DevisPdfRequest extends StoreDevisRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
