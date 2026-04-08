<?php

namespace App\Observers;

class SettingObserver extends AuditableObserver
{
    protected function targetType(): string
    {
        return 'setting';
    }
}
