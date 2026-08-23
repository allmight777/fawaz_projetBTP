<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => 'required|exists:document_types,id',
            'structure_emettrice_id' => 'nullable|exists:structures,id',
            'lot_id' => 'nullable|exists:lots,id',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fichier' => 'nullable|file|max:10240',
            'fichier_url' => 'nullable|url',
        ];
    }
}
