<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'html' => ['required', 'string', 'max:2000000'],
            'format' => ['sometimes', 'in:pdf'],
            'options' => ['sometimes', 'array'],
            'options.paper_size' => ['sometimes', 'in:a4,letter'],
        ];
    }
}
