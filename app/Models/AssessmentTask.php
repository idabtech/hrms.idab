<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'self_assessment_id',
        'position',
        'title',
        'responsibilities',
        'status',
        'priority',
    ];

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class);
    }

    public function statusLabel(): string
    {
        $statuses = config('self_assessment.statuses', []);
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    public function priorityLabel(): string
    {
        $priorities = config('self_assessment.priorities', []);
        return $priorities[$this->priority] ?? ucfirst($this->priority);
    }
}
