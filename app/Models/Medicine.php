<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    /** @use HasFactory<\Database\Factories\MedicineFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'created_by_id',
        'name',
        'description',
        'image_url',
        'remaining_quantity',
        'minimum_temperature',
        'maximum_temperature',
        'minimum_humidity',
        'maximum_humidity',
        'reorder_threshold',
    ];

    protected function casts(): array
    {
        return [
            'minimum_temperature' => 'decimal:2',
            'maximum_temperature' => 'decimal:2',
            'minimum_humidity' => 'decimal:2',
            'maximum_humidity' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function therapyPlans(): HasMany
    {
        return $this->hasMany(TherapyPlan::class);
    }

    public function doseLogs(): HasMany
    {
        return $this->hasMany(DoseLog::class);
    }

    public function isStockLow(): bool
    {
        return $this->remaining_quantity <= $this->reorder_threshold;
    }
}
