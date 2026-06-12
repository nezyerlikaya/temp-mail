<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

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
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'excerpt' => fake()->sentence(),
            'content' => null,
            'content_readiness' => 'outline',
            'featured_media_id' => null,
            'blog_category_id' => null,
            'status' => 'draft',
            'author_id' => User::factory()->admin(),
            'published_at' => null,
            'trashed_at' => null,
            'preview_token' => Str::random(48),
        ];
    }
}
