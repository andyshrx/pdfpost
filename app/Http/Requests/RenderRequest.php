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
            'html' => ['required_without:template', 'prohibits:template', 'string', 'max:2000000'],
            'template' => ['required_without:html', 'string', 'exists:templates,slug'],
            'data' => ['sometimes', 'array', 'prohibits:html'],
            'format' => ['sometimes', 'in:pdf,png'],
            'options' => ['sometimes', 'array'],
            'options.paper_size' => ['sometimes', 'in:a4,letter'],
            'options.width' => ['sometimes', 'integer', 'between:16,4000'],
            'options.height' => ['sometimes', 'integer', 'between:16,4000'],
            'webhook_url' => ['sometimes', 'url:http,https', 'max:2048'],
        ];
    }
}
