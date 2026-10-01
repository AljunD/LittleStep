<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'contact_number',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressRecords()
    {
        return $this->hasMany(ProgressRecord::class);
    }
}