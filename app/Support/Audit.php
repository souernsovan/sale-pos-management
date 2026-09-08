<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;

/**
 * Thin wrapper around spatie/laravel-activitylog for the sensitive admin
 * actions this app records (users, roles, settings, catalog deletes) —
 * always attributes the entry to the current user and keeps call sites
 * one-liners.
 */
class Audit
{
    public static function log(string $logName, string $description, ?Model $subject = null, array $properties = [], ?string $event = null): ?Activity
    {
        return activity($logName)
            ->causedBy(auth()->user())
            ->when($subject, fn ($activity) => $activity->performedOn($subject))
            ->when($event, fn ($activity) => $activity->event($event))
            ->withProperties($properties)
            ->log($description);
    }
}
