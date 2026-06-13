<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\TranslationSource;
use App\Models\TranslationValue;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Localization\LocaleReadinessService;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Translations\TranslationCoverageService;
use App\Services\Translations\TranslationFallbackResolver;
use App\Services\Translations\TranslationStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(LocaleSettingsStore::class)->ensureSeeded();
        app(TranslationStore::class)->syncRegistry();
    }

    public function test_active_locale_editor_renders_canonical_context_coverage_and_bulk_controls(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.translation-center.index', ['mode' => 'editor', 'locale' => 'de']))
            ->assertOk()
            ->assertSee('Locale editor')
            ->assertSee('German translation')
            ->assertSee('English canonical source')
            ->assertSee('Locale coverage')
            ->assertSee('Required coverage')
            ->assertSee('Missing queue')
            ->assertSee('Select all visible')
            ->assertSee('Save translations')
            ->assertSee('Mark reviewed')
            ->assertSee('Publish selected')
            ->assertSee('x-on:beforeunload.window', false);
    }

    public function test_editor_can_bulk_save_translation_values_and_audit_without_logging_copy(): void
    {
        $editor = User::factory()->editor()->create();
        $source = TranslationSource::query()->where('translation_key', 'home.hero.title')->firstOrFail();

        $this->actingAs($editor)
            ->post(route('admin.translation-center.translations.save'), [
                'locale' => 'de',
                'translations' => [
                    $source->id => [
                        'value' => 'Private temporäre E-Mail in Sekunden',
                        'status' => 'translated',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.translation-center.index', ['mode' => 'editor', 'locale' => 'de']));

        $this->assertDatabaseHas('translation_values', [
            'translation_source_id' => $source->id,
            'locale_id' => Locale::query()->where('locale', 'de')->value('id'),
            'value' => 'Private temporäre E-Mail in Sekunden',
            'status' => 'translated',
            'updated_by' => $editor->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'translation.values_updated',
            'actor_id' => $editor->id,
        ]);

        $metadata = (array) $this->getAuditMetadata('translation.values_updated');
        $this->assertArrayNotHasKey('value', $metadata);
        $this->assertArrayNotHasKey('translation', $metadata);
    }

    public function test_coverage_and_locale_launch_readiness_use_real_translation_values(): void
    {
        $admin = User::factory()->admin()->create();
        $locale = Locale::query()->where('locale', 'de')->firstOrFail();
        $sources = TranslationSource::query()->where('is_active', true)->take(2)->get();

        foreach ($sources as $source) {
            TranslationValue::query()->create([
                'translation_source_id' => $source->id,
                'locale_id' => $locale->id,
                'value' => 'Übersetzt '.$source->id,
                'status' => 'reviewed',
                'updated_by' => $admin->id,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        }

        $coverage = app(TranslationCoverageService::class)->forLocale($locale);
        $readiness = app(LocaleReadinessService::class)->forLocale($locale);

        $this->assertSame(2, $coverage['completed']);
        $this->assertSame($coverage['coverage'], $readiness['categories']['copy_ui_text']['score']);
        $this->assertSame($coverage['total'] - 2, $coverage['missing']);
    }

    public function test_review_and_publish_transitions_are_permission_aware_and_audited(): void
    {
        $editor = User::factory()->editor()->create();
        $admin = User::factory()->admin()->create();
        $locale = Locale::query()->where('locale', 'de')->firstOrFail();
        $source = TranslationSource::query()->where('translation_key', 'nav.home')->firstOrFail();

        TranslationValue::query()->create([
            'translation_source_id' => $source->id,
            'locale_id' => $locale->id,
            'value' => 'Startseite',
            'status' => 'translated',
            'updated_by' => $editor->id,
        ]);

        $this->actingAs($editor)
            ->post(route('admin.translation-center.translations.review'), [
                'locale' => 'de',
                'source_ids' => [$source->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('translation_values', [
            'translation_source_id' => $source->id,
            'locale_id' => $locale->id,
            'status' => 'reviewed',
            'reviewed_by' => $editor->id,
        ]);

        $this->actingAs($editor)
            ->post(route('admin.translation-center.translations.publish'), [
                'locale' => 'de',
                'source_ids' => [$source->id],
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.translation-center.translations.publish'), [
                'locale' => 'de',
                'source_ids' => [$source->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('translation_values', [
            'translation_source_id' => $source->id,
            'locale_id' => $locale->id,
            'status' => 'published',
            'published_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'translation.values_reviewed', 'actor_id' => $editor->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'translation.values_published', 'actor_id' => $admin->id]);
    }

    public function test_missing_or_unpublished_values_fall_back_to_canonical_english(): void
    {
        $admin = User::factory()->admin()->create();
        $locale = Locale::query()->where('locale', 'de')->firstOrFail();
        $source = TranslationSource::query()->where('translation_key', 'mailbox.empty.title')->firstOrFail();
        $resolver = app(TranslationFallbackResolver::class);

        $this->assertSame($source->source_value, $resolver->resolve($source, $locale));

        $value = TranslationValue::query()->create([
            'translation_source_id' => $source->id,
            'locale_id' => $locale->id,
            'value' => 'Dein Posteingang ist leer',
            'status' => 'draft',
            'updated_by' => $admin->id,
        ]);

        $this->assertSame($source->source_value, $resolver->resolve($source, $locale));

        $value->update(['status' => 'published', 'published_by' => $admin->id, 'published_at' => now()]);

        $this->assertSame('Dein Posteingang ist leer', $resolver->resolve($source, $locale));
    }

    public function test_translation_values_cannot_execute_blade_or_php(): void
    {
        $admin = User::factory()->admin()->create();
        $source = TranslationSource::query()->where('translation_key', 'home.hero.title')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.translation-center.index', ['mode' => 'editor', 'locale' => 'de']))
            ->post(route('admin.translation-center.translations.save'), [
                'locale' => 'de',
                'translations' => [
                    $source->id => [
                        'value' => '@php echo "unsafe"; @endphp',
                        'status' => 'translated',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("translations.{$source->id}.value");

        $this->assertDatabaseMissing('translation_values', [
            'translation_source_id' => $source->id,
            'locale_id' => Locale::query()->where('locale', 'de')->value('id'),
        ]);
    }

    public function test_inactive_and_english_locales_cannot_be_used_as_translation_targets(): void
    {
        $admin = User::factory()->admin()->create();
        $source = TranslationSource::query()->firstOrFail();

        foreach (['en', 'nl'] as $locale) {
            $this->actingAs($admin)
                ->post(route('admin.translation-center.translations.save'), [
                    'locale' => $locale,
                    'translations' => [$source->id => ['value' => 'Blocked', 'status' => 'draft']],
                ])
                ->assertSessionHasErrors('locale');
        }
    }

    private function getAuditMetadata(string $event): array
    {
        return (array) UserAuditEvent::query()->where('event', $event)->latest('id')->value('metadata');
    }
}
