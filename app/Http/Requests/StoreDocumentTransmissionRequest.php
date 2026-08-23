<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTransmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'structures' => 'nullable|array',
            'structures.*' => 'exists:structures,id',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'commentaire' => 'nullable|string',
            'archiver' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->structures) && empty($this->users)) {
                $validator->errors()->add('destinataires', 'Sélectionnez au moins un destinataire.');
            }
        });
    }
}
