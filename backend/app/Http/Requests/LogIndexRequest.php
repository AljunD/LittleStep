<?php

namespace App\Http\Requests;

use App\Support\AuditLog;
use Illuminate\Validation\Rule;

class LogIndexRequest extends IndexSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'category' => ['nullable', Rule::in(array_keys(AuditLog::CATEGORIES))],
            'action' => ['nullable', 'string', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => array_filter(['nullable', 'date', $this->filled('from_date') ? 'after_or_equal:from_date' : null]),
        ];
    }
}
