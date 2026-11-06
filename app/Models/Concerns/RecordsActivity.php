<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Records create / update / delete / restore events in the activity log
 * (spatie/laravel-activitylog), shown on the Audit Logs page.
 *
 * By default every fillable attribute is logged, and only the fields that
 * actually changed. Override activityLogAttributes() to log a fixed list
 * instead (e.g. to keep sensitive fields out of the log).
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->useLogName(Str::snake(class_basename($this)))
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at']);

        $attributes = $this->activityLogAttributes();

        return $attributes === null ? $options->logFillable() : $options->logOnly($attributes);
    }

    /**
     * Attributes to log, or null to log all fillable attributes.
     *
     * @return array<int, string>|null
     */
    protected function activityLogAttributes(): ?array
    {
        return null;
    }

    /**
     * Human-readable name for this record in the audit log. Saved with each
     * entry so it still reads well after the record is deleted.
     */
    public function activityLabel(): string
    {
        return (string) ($this->name ?? '#'.$this->getKey());
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties->put('label', $this->activityLabel());
    }
}
