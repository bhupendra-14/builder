<?php

namespace App\Observers;

class UserObserver extends AuditableObserver
{
    protected function targetType(): string
    {
        return 'user';
    }
}
