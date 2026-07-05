<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, asText: true);

        return [
            'business_name' => Str::title($name),
            'technical_name' => Str::slug($name),
            'slug' => Str::slug($name),
            'acronym' => Str::upper(fake()->unique()->lexify('???')),
            'webhook_token' => null,
            'hmac_secret' => null,
            'last_synced_at' => null,
        ];
    }
}
