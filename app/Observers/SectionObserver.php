<?php

namespace App\Observers;

class SectionObserver extends AuditableObserver
{
    protected function targetType(): string
    {
        return 'section';
    }
}
