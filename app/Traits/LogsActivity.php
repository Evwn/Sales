<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait LogsActivity
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function logActivity(string $type, array $data = [], $causer = null)
    {
        $activity = new Activity([
            'type' => $type,
            'data' => $data,
            'user_id' => auth()->id(),
        ]);

        $activity->subject()->associate($this);

        if ($causer) {
            $activity->causer()->associate($causer);
        } else {
            $activity->causer()->associate(auth()->user());
        }

        $activity->save();

        return $activity;
    }
} 