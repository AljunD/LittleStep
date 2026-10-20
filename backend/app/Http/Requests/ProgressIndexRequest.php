<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ProgressIndexRequest extends IndexSearchRequest
{
    /** Status filter values => the progress_records.status values they match (the dashboard's record cards link here). */
    public const STATUSES = [
        'completed' => ['completed'],
        'unfinished' => ['pending', 'in_progress'],
    ];

    public const STATUS_LABELS = [
        'completed' => 'Completed',
        'unfinished' => 'Unfinished',
    ];

    public function rules(): array
    {
        return parent::rules() + [
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    /** @return list<string>|null */
    public function recordStatuses(): ?array
    {
        return self::STATUSES[$this->validated('status')] ?? null;
    }
}
