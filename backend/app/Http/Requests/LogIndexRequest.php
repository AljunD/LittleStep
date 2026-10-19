<?php

namespace App\Http\Requests;

class LogIndexRequest extends IndexSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'action' => ['nullable', 'string', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => array_filter(['nullable', 'date', $this->filled('from_date') ? 'after_or_equal:from_date' : null]),
        ];
    }
}
