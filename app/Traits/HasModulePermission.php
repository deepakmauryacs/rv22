<?php

namespace App\Traits;

trait HasModulePermission
{
    protected function ensurePermission(string $moduleSlug, string $permissionType = 'view', string $moduleFor = '3'): void
    {
        if (!checkPermission($moduleSlug, $permissionType, $moduleFor)) {
            abort(403, 'Unauthorized');
        }
    }
}

