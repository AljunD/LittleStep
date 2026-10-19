<?php

namespace App\Http\Requests;

use App\Support\FormRules;
use Illuminate\Validation\Rule;

class ChildIndexRequest extends IndexSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'sex' => ['nullable', Rule::in(FormRules::SEXES)],
        ];
    }
}
