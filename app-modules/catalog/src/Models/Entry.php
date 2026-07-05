<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryFactory;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $qualified_id
 * @property string $native_id
 * @property string|null $project_id
 * @property string|null $slug
 * @property string $title
 * @property string $summary
 * @property Purpose $purpose
 * @property Format $format
 * @property Origin $origin
 * @property Area $department
 * @property Collection<int, Audience> $audience
 * @property array<int, string>|null $keywords
 * @property Status $status
 * @property string|null $owner_id
 * @property array<int, string>|null $authors
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project|null $originProject
 * @property-read User|null $owner
 * @property-read Document|null $document
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PrdVersion> $prdVersions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EntryArtifact> $artifacts
 *
 * @extends BaseModel<EntryFactory>
 */
#[UseFactory(EntryFactory::class)]
#[Table(name: 'catalog_entries')]
final class Entry extends BaseModel
{
    /** @return BelongsTo<Project, $this> */
    public function originProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasOne<Document, $this> */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }

    /** @return HasMany<PrdVersion, $this> */
    public function prdVersions(): HasMany
    {
        return $this->hasMany(PrdVersion::class);
    }

    /**
     * Faceta projeto (assunto).
     *
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'catalog_entry_project');
    }

    /** @return HasMany<EntryArtifact, $this> */
    public function artifacts(): HasMany
    {
        return $this->hasMany(EntryArtifact::class);
    }

    protected static function booted(): void
    {
        // Invariante: a origem sempre está na faceta projeto (spec, entry_project).
        self::saved(static function (Entry $entry): void {
            if ($entry->project_id !== null) {
                $entry->projects()->syncWithoutDetaching([$entry->project_id]);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'purpose' => Purpose::class,
            'format' => Format::class,
            'origin' => Origin::class,
            'department' => Area::class,
            'audience' => AsEnumCollection::of(Audience::class),
            'keywords' => 'array',
            'authors' => 'array',
            'status' => Status::class,
        ];
    }
}
