<?php

namespace App\Services\PublicSite;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Media\MediaUrlResolver;

class PublicAuthorService
{
    public function __construct(private readonly MediaUrlResolver $media) {}

    /** @return array<string, mixed>|null */
    public function resolve(?User $author): ?array
    {
        if (! $author) {
            return null;
        }

        $avatar = null;
        if ($author->avatar_media_id) {
            $asset = MediaAsset::query()->whereKey($author->avatar_media_id)->where('status', 'active')->first();
            if ($asset) {
                $avatar = $this->media->url($asset);
            }
        }

        return [
            'name' => $author->display_name ?: $author->name,
            'slug' => $author->author_profile_active ? $author->public_author_slug : null,
            'bio' => $author->author_profile_active ? $author->author_bio : null,
            'avatar_url' => $avatar,
            'avatar_color' => $author->avatar_color ?: '#0f766e',
            'initials' => str($author->display_name ?: $author->name)->explode(' ')->take(2)->map(fn (string $part): string => str($part)->substr(0, 1)->upper()->toString())->implode(''),
        ];
    }
}
