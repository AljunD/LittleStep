<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'progress_records';

    protected $fillable = [
        'child_id',
        'teacher_id',
        'evaluation_date',
        'evaluation_number',
        'status',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function domainScores()
    {
        return $this->hasMany(DomainScore::class);
    }

    public function domainObservation()
    {
        return $this->hasOne(DomainObservation::class);
    }
}