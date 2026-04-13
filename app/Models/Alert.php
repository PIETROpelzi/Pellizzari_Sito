<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<\Database\Factories\AlertFactory> */
    use HasFactory;

    public const TYPE_HUMIDITY = 'Humidity';
    public const TYPE_TEMPERATURE = 'Temperature';
    public const TYPE_MISSED_DOSE = 'MissedDose';
    public const TYPE_STOCK_LOW = 'StockLow';
    public const TYPE_DEVICE_OFFLINE = 'DeviceOffline';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'dispenser_id',
        'sensor_log_id',
        'dose_log_id',
        'type',
        'severity',
        'message',
        'triggered_at',
        'resolved_at',
        'notified_caregiver',
        'notified_doctor',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
            'notified_caregiver' => 'boolean',
            'notified_doctor' => 'boolean',
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

    public function sensorLog(): BelongsTo
    {
        return $this->belongsTo(SensorLog::class);
    }

    public function doseLog(): BelongsTo
    {
        return $this->belongsTo(DoseLog::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotNull('resolved_at');
    }
}
