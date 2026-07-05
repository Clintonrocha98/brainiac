<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\PrdVersionFactory;
use He4rt\Catalog\Enums\PrdVersionState;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $entry_id
 * @property int|null $major
 * @property int|null $minor
 * @property string $body_markdown
 * @property PrdVersionState $state
 * @property Carbon|null $frozen_at
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<PrdVersionFactory>
 */
#[UseFactory(PrdVersionFactory::class)]
#[Table(name: 'catalog_prd_versions')]
final class PrdVersion extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected static function booted(): void
    {
        self::saving(static function (PrdVersion $version): void {
            if ($version->isDirty('body_markdown')) {
                $version->fill(resolve(DeriveBodyFacts::class)->execute($version->body_markdown)->toColumns());
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => PrdVersionState::class,
            'frozen_at' => 'datetime',
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
