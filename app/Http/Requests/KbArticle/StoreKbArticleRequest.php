<?php

namespace App\Http\Requests\KbArticle;

use Illuminate\Foundation\Http\FormRequest;

class StoreKbArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:100'],
            'body'         => ['required', 'string'],
            'is_public'    => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
