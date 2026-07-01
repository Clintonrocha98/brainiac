<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\InteractsWithRequest;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @template TFactory of Factory
 */
abstract class BaseModel extends Model
{
    use HasActivity;

    /** @use HasFactory<TFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithRequest;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(static::class)
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept($this->hidden);
    }
}
