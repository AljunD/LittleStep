<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'children';

    protected $fillable = [
        'guardian_id',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'date_of_birth',
        'address',
        'handedness',
        'is_studying',
        'school_name',
        'fathers_name',
        'fathers_age',
        'fathers_occupation',
        'fathers_education',
        'mothers_name',
        'mothers_age',
        'mothers_occupation',
        'mothers_education',
        'number_of_siblings',
        'birth_order',
        'photo_path',
    ];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function progressRecords()
    {
        return $this->hasMany(ProgressRecord::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Address Accessors
    |--------------------------------------------------------------------------
    | Parses comma-separated address string: "Barangay, City, Province, Region"
    */

    public function getBarangayAttribute(): ?string
    {
        $parts = array_map('trim', explode(',', $this->address ?? ''));
        return $parts[0] ?? null;
    }

    public function getCityAttribute(): ?string
    {
        $parts = array_map('trim', explode(',', $this->address ?? ''));
        return $parts[1] ?? null;
    }

    public function getProvinceAttribute(): ?string
    {
        $parts = array_map('trim', explode(',', $this->address ?? ''));
        return $parts[2] ?? null;
    }

    public function getRegionAttribute(): ?string
    {
        $parts = array_map('trim', explode(',', $this->address ?? ''));
        return $parts[3] ?? null;
    }
}