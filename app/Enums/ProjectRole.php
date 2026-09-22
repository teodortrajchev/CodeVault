<?php

namespace App\Enums;

use App\Models\User;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Member = 'member';
    case Viewer = 'viewer';

    public function canManage(): bool
    {
        return in_array($this, [self::Owner, self::Manager]);
    }

    public function canContribute(): bool
    {
        return $this !== self::Viewer;
    }


}
?>