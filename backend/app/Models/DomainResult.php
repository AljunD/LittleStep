<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DomainResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domain_results';

    protected $fillable = [
        'domain_id',
        'present',
        'comments',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}