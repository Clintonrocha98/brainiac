<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryLink>
 */
final class EntryLinkFactory extends Factory
{
    protected $model = EntryLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_entry_id' => Entry::factory(),
            'to_entry_id' => Entry::factory(),
            'type' => fake()->randomElement(EntryLinkType::cases()),
        ];
    }
}
