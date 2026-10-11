<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domains';

    protected $fillable = [
        'progress_record_id',
        'checklist_item_id',
        'status',
    ];

    // Relationships
    public function progressRecord()
    {
        return $this->belongsTo(ProgressRecord::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(EccdChecklistItem::class, 'checklist_item_id');
    }

    public function results()
    {
        return $this->hasMany(DomainResult::class);
    }

    public function scores()
    {
        return $this->hasMany(DomainScore::class);
    }
}