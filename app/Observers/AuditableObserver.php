<?php

namespace App\Observers;

use App\Services\Auditor;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Eloquent observer that writes audit log entries on
 * created/updated/deleted events. Subclasses provide a target type label
 * and an optional list of attribute keys to redact from the diff.
 */
abstract class AuditableObserver
{
    public function __construct(protected Auditor $auditor) {}

    abstract protected function targetType(): string;

    /** Attribute keys to scrub from old/new values before logging. */
    protected array $redact = ['password', 'remember_token'];

    public function created(Model $model): void
    {
        $this->auditor->log(
            "{$this->targetType()}.created",
            $this->targetType(),
            $model->getKey(),
            null,
            $this->scrub($model->getAttributes()),
        );
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        // Skip if only timestamps changed (avoid noise)
        $meaningful = array_diff_key($changes, array_flip(['updated_at']));
        if (empty($meaningful)) return;

        $original = [];
        foreach (array_keys($meaningful) as $key) {
            $original[$key] = $model->getOriginal($key);
        }

        $this->auditor->log(
            "{$this->targetType()}.updated",
            $this->targetType(),
            $model->getKey(),
            $this->scrub($original),
            $this->scrub($meaningful),
        );
    }

    public function deleted(Model $model): void
    {
        $this->auditor->log(
            "{$this->targetType()}.deleted",
            $this->targetType(),
            $model->getKey(),
            $this->scrub($model->getAttributes()),
            null,
        );
    }

    protected function scrub(array $attrs): array
    {
        foreach ($this->redact as $key) {
            if (array_key_exists($key, $attrs)) {
                $attrs[$key] = '[redacted]';
            }
        }
        return $attrs;
    }
}
