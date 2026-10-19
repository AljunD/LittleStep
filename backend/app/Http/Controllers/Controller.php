<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    /** Readable list of a model's unsaved changed fields for audit log details, e.g. "first name, contact number". */
    protected function changedFieldsList(Model $model): string
    {
        return collect(array_keys($model->getDirty()))
            ->map(fn (string $field) => str_replace('_', ' ', $field))
            ->implode(', ');
    }
}
