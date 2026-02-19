<?php

namespace App\Http\Requests\KbArticle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKbArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:100'],
            'body'         => ['sometimes', 'required', 'string'],
            'is_public'    => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
