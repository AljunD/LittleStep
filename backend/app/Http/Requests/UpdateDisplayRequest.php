<?php

namespace App\Http\Requests;

use App\Support\DisplayTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisplayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', Rule::in(array_keys(DisplayTheme::OPTIONS))],
        ];
    }
}
