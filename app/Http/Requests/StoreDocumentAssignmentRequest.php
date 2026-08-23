<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'controleurs' => 'required|array|min:1',
            'controleurs.*' => 'exists:users,id',
            'specialite' => 'nullable|string|max:255',
            'date_limite' => 'nullable|date|after:now',
        ];
    }
}
