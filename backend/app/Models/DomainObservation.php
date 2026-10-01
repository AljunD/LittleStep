<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DomainObservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domain_observations';

    protected $fillable = [
        'progress_record_id',
        'place_administered',
        'background_observations',
        'family_environment_notes',
        'stimulating_activities_notes',
        'home_environment_notes',
        'other_notes',
        'status',
    ];

    public function progressRecord()
    {
        return $this->belongsTo(ProgressRecord::class);
    }
}