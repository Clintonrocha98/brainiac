<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $entry_id
 * @property string $body_markdown
 * @property string|null $git_pointer
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<DocumentFactory>
 */
#[UseFactory(DocumentFactory::class)]
#[Table(name: 'catalog_documents')]
final class Document extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Não loga o corpo inteiro no activity log (evita bloat).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected static function booted(): void
    {
        self::saving(static function (Document $document): void {
            if ($document->isDirty('body_markdown')) {
                $document->fill(app(DeriveBodyFacts::class)->execute($document->body_markdown)->toColumns());
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
