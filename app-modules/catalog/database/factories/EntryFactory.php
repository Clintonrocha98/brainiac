<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Entry>
 */
final class EntryFactory extends Factory
{
    protected $model = Entry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $native = 'doc/'.fake()->unique()->slug(2);

        return [
            'qualified_id' => 'DESIGN:'.$native,
            'native_id' => $native,
            'project_id' => null,
            'slug' => Str::slug($native),
            'title' => Str::title(fake()->words(3, true)),
            'summary' => fake()->sentence(),
            'purpose' => fake()->randomElement(Purpose::cases()),
            'format' => Format::Explanation,
            'origin' => Origin::Native,
            'department' => Area::Design,
            'audience' => [Audience::Ti],
            'keywords' => ['exemplo'],
            'status' => Status::Published,
            'owner_id' => User::factory(),
        ];
    }

    public function prd(): self
    {
        return $this->state(fn (): array => [
            'format' => Format::Prd,
            'purpose' => Purpose::Reference,
        ]);
    }
}
