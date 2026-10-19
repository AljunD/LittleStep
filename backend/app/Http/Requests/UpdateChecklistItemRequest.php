<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Only the wording of an official checklist item can change. Domain and item number are not accepted,
 * because scoring and the Child's Record 2 report depend on the official item set.
 */
class UpdateChecklistItemRequest extends FormRequest
{
    public const MATERIALS_MAX_LENGTH = 5000;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity' => ['required', 'string', 'max:255'],
            'materials_and_procedure' => ['nullable', 'string', 'max:'.self::MATERIALS_MAX_LENGTH],
        ];
    }
}
