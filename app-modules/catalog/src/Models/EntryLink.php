<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryLinkFactory;
use He4rt\Catalog\Enums\EntryLinkType;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $from_entry_id
 * @property string $to_entry_id
 * @property EntryLinkType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $fromEntry
 * @property-read Entry $toEntry
 *
 * @extends BaseModel<EntryLinkFactory>
 */
#[UseFactory(EntryLinkFactory::class)]
#[Table(name: 'catalog_entry_links')]
final class EntryLink extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function fromEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'from_entry_id');
    }

    /** @return BelongsTo<Entry, $this> */
    public function toEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'to_entry_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EntryLinkType::class,
        ];
    }
}
