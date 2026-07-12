<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash', 'unique:templates,slug'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'liquid_source' => ['required', 'string', 'max:1000000'],
            'sample_data' => ['sometimes', 'array'],
        ];
    }
}
