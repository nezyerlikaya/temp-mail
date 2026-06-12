<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SectionItem>
 */
class SectionItemFactory extends Factory
{
    protected $model = SectionItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => fake()->sentence(5),
            'content' => fake()->paragraph(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
