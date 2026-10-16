<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EccdChecklistItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eccd_checklist_items';

    protected $fillable = [
        'domain',
        'activity',
        'materials_and_procedure',
        'item_number',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class, 'checklist_item_id');
    }

    public function scopeOfType($query, string $domain)
    {
        return $query->where('domain', $domain);
    }
}