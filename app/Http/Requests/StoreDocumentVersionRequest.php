<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fichier' => 'nullable|file|max:10240',
            'fichier_url' => 'nullable|url',
            'commentaire' => 'nullable|string',
        ];
    }
}
