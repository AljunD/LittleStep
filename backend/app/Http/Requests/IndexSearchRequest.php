<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexSearchRequest extends FormRequest
{
    public const SEARCH_MAX_LENGTH = 100;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:'.self::SEARCH_MAX_LENGTH],
        ];
    }

    public function search(): ?string
    {
        return $this->validated('search');
    }
}
