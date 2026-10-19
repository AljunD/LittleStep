<?php

namespace App\Http\Requests;

use App\Support\FormRules;
use Illuminate\Foundation\Http\FormRequest;

/** Validates the teacher's own details. Email is deliberately not accepted here. */
class UpdateTeacherProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return FormRules::teacherProfile();
    }

    public function messages(): array
    {
        return FormRules::messages();
    }
}
