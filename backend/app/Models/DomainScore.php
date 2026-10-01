<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DomainScore extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domain_scores';

    protected $fillable = [
        'progress_record_id',
        'domain_id',
        'raw_score',
        'scaled_score',
        'sum_scaled_score',
        'standard_score',
        'child_age',
        'interpretation_code',
    ];

    public function progressRecord()
    {
        return $this->belongsTo(ProgressRecord::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}