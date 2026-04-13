<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoseLog extends Model
{
    /** @use HasFactory<\Database\Factories\DoseLogFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_DISPENSED = 'Dispensed';
    public const STATUS_TAKEN = 'Taken';
    public const STATUS_MISSED = 'Missed';
    public const STATUS_SNOOZED = 'Snoozed';
    public const STATUS_SKIPPED = 'Skipped';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'dispenser_id',
        'therapy_plan_id',
        'medicine_id',
        'status',
        'source',
        'scheduled_for',
        'event_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'event_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(Dispenser::class);
    }

    public function therapyPlan(): BelongsTo
    {
        return $this->belongsTo(TherapyPlan::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeTaken(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TAKEN);
    }

    public function scopeWindow(Builder $query, int $days): Builder
    {
        return $query->where('event_at', '>=', now()->subDays($days));
    }
}
