<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogCategory> */
class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'locale_id' => Locale::query()->first()?->id ?? Locale::query()->create([
                'language_name' => 'English',
                'native_name' => 'English',
                'locale' => 'en',
                'direction' => 'ltr',
                'region' => 'Global',
                'market_readiness' => 'ready',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'launch_status' => 'launched',
            ])->id,
            'name' => str($name)->headline()->toString(),
            'slug' => str($name)->slug()->toString(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
