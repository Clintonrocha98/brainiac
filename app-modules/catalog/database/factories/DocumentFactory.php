<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
final class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'body_markdown' => fake()->paragraphs(3, true),
            'git_pointer' => null,
        ];
    }
}
