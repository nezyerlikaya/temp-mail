<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Editor = 'editor';
    case Moderator = 'moderator';
    case Author = 'author';
    case Member = 'member';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function description(): string
    {
        return match ($this) {
            self::Owner => 'Full platform control, including owner assignment and critical access protection.',
            self::Admin => 'Full operational access except owner-only account changes.',
            self::Editor => 'Content, markets, media, SEO, and publishing workspaces.',
            self::Moderator => 'Moderation, abuse response, and identity review workspaces.',
            self::Author => 'Authoring, media, and profile workspaces.',
            self::Member => 'Product membership only. No admin panel access.',
        };
    }

    public function hasAdminAccess(): bool
    {
        return $this !== self::Member;
    }

    public function isCritical(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function isElevated(): bool
    {
        return $this !== self::Member;
    }
}
