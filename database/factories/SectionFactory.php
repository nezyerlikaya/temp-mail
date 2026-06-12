<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
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
            'section_type' => 'cta',
            'placement' => 'home.primary',
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'settings' => ['readiness' => 'foundation'],
            'status' => 'draft',
            'sort_order' => 0,
            'visibility' => 'public',
            'created_by' => User::factory()->admin(),
            'updated_by' => null,
            'trashed_at' => null,
        ];
    }
}
