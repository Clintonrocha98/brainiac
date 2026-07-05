<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryArtifact>
 */
final class EntryArtifactFactory extends Factory
{
    protected $model = EntryArtifact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'url' => 'https://waifuvault.moe/f/'.fake()->slug(2).'.html',
        ];
    }
}
