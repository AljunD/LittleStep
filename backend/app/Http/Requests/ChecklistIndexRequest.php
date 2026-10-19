<?php

namespace App\Http\Requests;

use App\Services\EccdExpertSystem;
use Illuminate\Validation\Rule;

class ChecklistIndexRequest extends IndexSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'domain' => ['nullable', Rule::in(array_keys(EccdExpertSystem::DOMAIN_LABELS))],
        ];
    }
}
