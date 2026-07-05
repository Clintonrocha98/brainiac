<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\CollectionFactory;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property string $summary
 * @property string|null $body_markdown
 * @property \Illuminate\Support\Collection<int, Audience> $audience
 * @property string $owner_id
 * @property Status $status
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Entry> $entries
 *
 * @extends BaseModel<CollectionFactory>
 */
#[UseFactory(CollectionFactory::class)]
#[Table(name: 'catalog_collections')]
final class Collection extends BaseModel
{
    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Trilha ordenada de Entradas existentes.
     *
     * @return BelongsToMany<Entry, $this>
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'catalog_collection_entry')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected static function booted(): void
    {
        self::saving(static function (Collection $collection): void {
            if ($collection->isDirty('body_markdown')) {
                $markdown = $collection->body_markdown ?? '';
                $collection->fill(app(DeriveBodyFacts::class)->execute($markdown)->toColumns());
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'audience' => AsEnumCollection::of(Audience::class),
            'status' => Status::class,
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
