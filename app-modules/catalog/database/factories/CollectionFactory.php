<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Collection;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
final class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(2, asText: true));

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9_999),
            'title' => $title,
            'summary' => fake()->sentence(),
            'body_markdown' => null,
            'audience' => [Audience::Ti],
            'owner_id' => User::factory(),
            'status' => Status::Published,
        ];
    }
}
