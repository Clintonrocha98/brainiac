<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrdVersion>
 */
final class PrdVersionFactory extends Factory
{
    protected $model = PrdVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory()->prd(),
            'major' => null,
            'minor' => null,
            'body_markdown' => fake()->paragraphs(3, asText: true),
            'state' => PrdVersionState::Draft,
            'frozen_at' => null,
        ];
    }

    public function frozen(): self
    {
        return $this->state(fn (): array => [
            'state' => PrdVersionState::Frozen,
            'frozen_at' => now(),
        ]);
    }
}
