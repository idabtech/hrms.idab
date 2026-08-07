<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStage extends Model
{
    protected $fillable = [
        'title',
        'order',
        'created_by',
    ];

    public function applications($filter)
    {
        $application = JobApplication::where('created_by', \Auth::user()->creatorId())->where('is_archive', 0)->where('stage', $this->id);
        if (!empty($filter['start_date'])) {
            $application->whereDate('created_at', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $application->whereDate('created_at', '<=', $filter['end_date']);
        }

        if (!empty($filter['job'])) {
            $application->where('job', $filter['job']);
        }
        $application = $application->orderBy('order')->get();

        return $application;
    }
}
