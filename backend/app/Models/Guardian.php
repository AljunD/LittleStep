<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'guardians';

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'contact_number',
        'address',
        'relationship_to_child',
        'barangay',
        'municipality',
        'province',
        'region',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }
}