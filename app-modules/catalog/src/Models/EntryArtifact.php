<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryArtifactFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $entry_id
 * @property string $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<EntryArtifactFactory>
 */
#[UseFactory(EntryArtifactFactory::class)]
#[Table(name: 'catalog_entry_artifacts')]
final class EntryArtifact extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
