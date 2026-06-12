<?php

namespace Database\Factories;

use App\Models\BlogTag;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogTag> */
class BlogTagFactory extends Factory
{
    protected $model = BlogTag::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word();

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
            'status' => 'active',
        ];
    }
}
