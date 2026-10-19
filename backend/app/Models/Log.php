<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * An audit trail entry. Entries are append-only: once written they cannot be edited or
 * deleted through the app. Old entries are only removed by the optional retention prune.
 */
class Log extends Model
{
    use HasFactory, MassPrunable, SoftDeletes;

    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'entity_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log entries cannot be changed.'));
        static::deleting(fn () => throw new LogicException('Audit log entries cannot be deleted.'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Entries older than AUDIT_LOG_RETENTION_DAYS, removed by `php artisan model:prune`. Keeps everything when it is not set. */
    public function prunable(): Builder
    {
        $days = config('littlestep.audit_log_retention_days');

        return $days
            ? static::withTrashed()->where('created_at', '<', now()->subDays((int) $days))
            : static::query()->whereRaw('1 = 0');
    }
}
