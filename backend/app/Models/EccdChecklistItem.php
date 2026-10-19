<?php

namespace App\Models;

use App\Services\EccdExpertSystem;
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

    /** Official items only, in Child's Record 2 order: domains as in EccdExpertSystem::DOMAIN_LABELS, then item number. */
    public function scopeInOfficialOrder($query)
    {
        $domainKeys = array_keys(EccdExpertSystem::DOMAIN_LABELS);
        $bindings = collect($domainKeys)->flatMap(fn (string $key, int $position) => [$key, $position])->all();

        return $query->whereIn('domain', $domainKeys)
            ->orderByRaw('CASE domain '.str_repeat('WHEN ? THEN ? ', count($domainKeys)).'END', $bindings)
            ->orderBy('item_number')
            ->orderBy('id');
    }
}
