<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapyPlanSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\TherapyPlanScheduleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'therapy_plan_id',
        'scheduled_time',
        'week_days',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'week_days' => 'array',
        ];
    }

    public function therapyPlan(): BelongsTo
    {
        return $this->belongsTo(TherapyPlan::class);
    }
}
