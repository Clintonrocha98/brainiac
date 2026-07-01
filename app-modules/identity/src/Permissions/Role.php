<?php

declare(strict_types=1);

namespace He4rt\Identity\Permissions;

use App\Models\Concerns\InteractsWithRequest;
use He4rt\Identity\Database\Factories\Permissions\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Role as BaseRole;

/**
 * @property-read string $id
 * @property string $name
 * @property string $guard_name
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Collection<int, Permission> $permissions
 */
#[UsePolicy(RolePolicy::class)]
#[UseFactory(RoleFactory::class)]
#[Table(name: 'identity_roles')]
class Role extends BaseRole
{
    use HasActivity;

    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithRequest;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(self::class)
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept($this->hidden);
    }
}
