<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function ownedChildren(): Builder
    {
        return Child::query()->whereHas('progressRecords', function (Builder $query) {
            $query->withTrashed()->where('teacher_id', $this->id);
        });
    }

    public function ownedGuardians(): Builder
    {
        return Guardian::query()->whereHas('children', function (Builder $query) {
            // Archived children still tie their guardian to the teacher, so unlinking a guardian's
            // last child does not hide that (still active) guardian from the Guardians page.
            $query->withTrashed()->whereHas('progressRecords', function (Builder $progressQuery) {
                $progressQuery->withTrashed()->where('teacher_id', $this->id);
            });
        });
    }
}
