<?php

namespace App\Services\Typography;

use App\Models\Locale;

class FontPreviewService
{
    public function __construct(
        private readonly FontStackResolver $resolver,
    ) {}

    /** @return array<string, array<string, string>> */
    public function samples(): array
    {
        return [
            'en' => ['label' => 'English', 'direction' => 'ltr', 'text' => 'Your temporary inbox is ready for secure signups.'],
            'tr' => ['label' => 'Turkish', 'direction' => 'ltr', 'text' => 'Geçici posta kutunuz güvenli kayıtlar için hazır.'],
            'de' => ['label' => 'German', 'direction' => 'ltr', 'text' => 'Ihr temporäres Postfach ist für sichere Anmeldungen bereit.'],
            'fr' => ['label' => 'French', 'direction' => 'ltr', 'text' => 'Votre boîte temporaire est prête pour des inscriptions sûres.'],
            'he' => ['label' => 'Hebrew', 'direction' => 'rtl', 'text' => 'תיבת הדואר הזמנית שלך מוכנה להרשמות בטוחות.'],
            'ar' => ['label' => 'Arabic', 'direction' => 'rtl', 'text' => 'صندوق بريدك المؤقت جاهز للتسجيلات الآمنة.'],
            'ru' => ['label' => 'Russian', 'direction' => 'ltr', 'text' => 'Ваш временный почтовый ящик готов для безопасных регистраций.'],
            'el' => ['label' => 'Greek', 'direction' => 'ltr', 'text' => 'Το προσωρινό γραμματοκιβώτιό σας είναι έτοιμο για ασφαλείς εγγραφές.'],
        ];
    }

    /** @return array<string, string> */
    public function modes(): array
    {
        return ['desktop' => 'Desktop', 'mobile' => 'Mobile'];
    }

    /** @return array<string, string> */
    public function directions(): array
    {
        return ['auto' => 'Auto', 'ltr' => 'LTR', 'rtl' => 'RTL'];
    }

    /** @return array<string, string> */
    public function previewScopes(): array
    {
        return [
            'ui' => 'UI text',
            'heading' => 'Heading',
            'body' => 'Body',
            'mailbox' => 'Mailbox interface',
            'cta' => 'CTA',
            'faq' => 'FAQ',
        ];
    }

    /** @return array<string, mixed> */
    public function build(string $theme, Locale|string|null $locale, string $language, string $mode, string $direction): array
    {
        $samples = $this->samples();
        $language = array_key_exists($language, $samples) ? $language : 'en';
        $sample = $samples[$language];
        $localeCode = $locale instanceof Locale ? $locale->locale : ($locale ?: $language);
        $resolved = $this->resolver->resolve($theme, $localeCode);
        $resolvedDirection = $direction === 'auto' ? $sample['direction'] : $direction;

        return [
            'language' => $language,
            'sample' => $sample,
            'mode' => $mode,
            'direction' => $resolvedDirection,
            'resolved' => $resolved,
            'scopes' => $this->previewScopes(),
            'cards' => [
                'ui' => ['label' => 'UI text', 'text' => 'Inbox expires in 10 minutes'],
                'heading' => ['label' => 'Heading', 'text' => $sample['text']],
                'body' => ['label' => 'Body', 'text' => $sample['text'].' Keep spam away from your real inbox while testing new services.'],
                'mailbox' => ['label' => 'Mailbox interface', 'text' => 'copy@temporary.test · 3 messages · refresh'],
                'cta' => ['label' => 'CTA', 'text' => 'Create secure inbox'],
                'faq' => ['label' => 'FAQ', 'text' => 'Can I receive verification codes? Yes, while the mailbox is active.'],
            ],
        ];
    }
}
