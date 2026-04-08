<?php

namespace App\Observers;

class AssetObserver extends AuditableObserver
{
    protected function targetType(): string
    {
        return 'asset';
    }
}
