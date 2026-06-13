<?php

namespace App\Services\Comments;

use App\Models\BlogPost;
use App\Services\Security\AkismetSpamService;
use App\Services\Security\SecuritySettingsStore;
use Illuminate\Http\Request;
use Throwable;

class CommentSpamCheckService
{
    public function __construct(
        private readonly AkismetSpamService $akismet,
        private readonly SecuritySettingsStore $security,
        private readonly CommentContentSanitizer $sanitizer,
    ) {}

    /** @return array{status: string, spam_score: int, spam_provider: string|null, provider_decision: string, bot_status: string} */
    public function check(BlogPost $post, array $payload, Request $request): array
    {
        $score = $this->baseScore((string) $payload['content']);
        $provider = null;
        $decision = 'manual_review';

        try {
            $readiness = $this->akismet->readiness();
            $provider = 'akismet';
            $decision = $readiness['status'];

            if ($readiness['status'] === 'configured') {
                $decision = $score >= 75 ? 'spam' : 'hold';
            }
        } catch (Throwable) {
            $provider = 'akismet';
            $decision = 'unavailable';
        }

        $status = $score >= 85 || $decision === 'spam' ? 'spam' : 'pending';

        return [
            'status' => $status,
            'spam_score' => $score,
            'spam_provider' => $provider,
            'provider_decision' => $decision,
            'bot_status' => $this->botStatus(),
        ];
    }

    private function baseScore(string $content): int
    {
        $score = min(45, $this->sanitizer->linkCount($content) * 15);

        if (preg_match('/(viagra|casino|crypto bonus|free money|loan offer)/i', $content) === 1) {
            $score += 45;
        }

        if (strlen($content) < 12) {
            $score += 15;
        }

        return min(100, $score);
    }

    private function botStatus(): string
    {
        $settings = $this->security->bot();

        if (! ($settings['is_active'] ?? false)) {
            return 'passive';
        }

        return in_array('comments', $settings['protected_forms'] ?? [], true) ? 'protected' : 'not_protected';
    }
}
