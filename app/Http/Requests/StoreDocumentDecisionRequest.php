<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $regles = [
            'decision' => 'required|in:valide,a_corriger',
            'observations_retenues' => 'nullable|array',
            'observations_retenues.*' => 'exists:document_observations,id',
            'commentaires' => 'required_if:decision,a_corriger|nullable|string',
        ];

        // Valider = viser : cachet + signature du validateur obligatoires.
        if ($this->input('decision') === 'valide') {
            $regles += \App\Services\VisaService::regles($this->user());
        }

        return $regles;
    }

    public function messages(): array
    {
        return \App\Services\VisaService::messages();
    }
}
