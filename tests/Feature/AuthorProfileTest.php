<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Users\AvatarResolver;
use App\Services\Users\MembershipSummaryResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorProfileTest extends TestCase
{
    use RefreshDatabase;

    private string $recoveryPath;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalRecovery = file_exists($this->recoveryPath)
            ? file_get_contents($this->recoveryPath)
            : null;

        if (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalRecovery !== null) {
            file_put_contents($this->recoveryPath, $this->originalRecovery);
        } elseif (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        parent::tearDown();
    }

    public function test_user_detail_renders_author_avatar_and_membership_cards(): void
    {
        $owner = User::factory()->owner()->create();
        $author = User::factory()->author()->create();

        $this->actingAs($owner)
            ->get(route('admin.people-identity.show', $author))
            ->assertOk()
            ->assertSee('Author Profile')
            ->assertSee('Avatar')
            ->assertSee('Membership Summary')
            ->assertSee('Initials fallback')
            ->assertSee('Plans never grant admin roles or permissions.');
    }

    public function test_author_profiles_page_replaces_placeholder_and_author_only_sees_self(): void
    {
        $author = User::factory()->author()->create(['display_name' => 'Visible Author']);
        User::factory()->author()->create(['display_name' => 'Other Author']);

        $this->actingAs($author)
            ->get(route('admin.author-profiles.index'))
            ->assertOk()
            ->assertSee('Author readiness')
            ->assertSee('Visible Author')
            ->assertDontSee('Other Author')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_author_can_update_own_profile_without_changing_role_or_membership(): void
    {
        $author = User::factory()->author()->create([
            'current_plan_reference' => 'premium-monthly',
            'membership_status' => 'active',
        ]);

        $this->actingAs($author)
            ->put(route('admin.author-profiles.update', $author), [
                'display_name' => 'Public Author',
                'public_author_slug' => 'public-author',
                'author_bio' => 'A durable author biography for future attributed content.',
                'website' => 'https://example.com',
                'social_links' => ['github' => 'https://github.com/example'],
                'author_profile_active' => '1',
                'featured_author' => '1',
            ])
            ->assertRedirect(route('admin.author-profiles.edit', $author));

        $author->refresh();
        $this->assertSame('Public Author', $author->display_name);
        $this->assertSame('public-author', $author->public_author_slug);
        $this->assertTrue($author->author_profile_active);
        $this->assertSame('author', $author->role);
        $this->assertSame('premium-monthly', $author->current_plan_reference);
        $this->assertDatabaseHas('user_audit_events', [
            'subject_id' => $author->id,
            'event' => 'user.author_profile_updated',
        ]);
    }

    public function test_public_author_profile_can_be_disabled_without_erasing_attribution(): void
    {
        $owner = User::factory()->owner()->create();
        $author = User::factory()->author()->create([
            'display_name' => 'Archived Name',
            'author_profile_active' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('admin.author-profiles.update', $author), [
                'display_name' => 'Archived Name',
                'public_author_slug' => $author->public_author_slug,
                'author_bio' => $author->author_bio,
                'website' => null,
            ])
            ->assertRedirect(route('admin.people-identity.show', $author));

        $author->refresh();
        $this->assertFalse($author->author_profile_active);
        $this->assertSame('Archived Name', $author->display_name);
        $this->assertNotNull($author->author_bio);
    }

    public function test_archived_author_identity_remains_available_for_future_content_attribution(): void
    {
        $author = User::factory()->author()->create([
            'display_name' => 'Archived Author',
            'public_author_slug' => 'archived-author',
            'author_bio' => 'This identity must remain attributable.',
        ]);

        $author->delete();

        $archived = User::withTrashed()->findOrFail($author->id);

        $this->assertNotNull($archived->deleted_at);
        $this->assertSame('Archived Author', $archived->display_name);
        $this->assertSame('archived-author', $archived->public_author_slug);
        $this->assertSame('This identity must remain attributable.', $archived->author_bio);
    }

    public function test_suspended_author_cannot_enable_public_profile(): void
    {
        $owner = User::factory()->owner()->create();
        $author = User::factory()->author()->suspended()->create();

        $this->actingAs($owner)
            ->from(route('admin.author-profiles.edit', $author))
            ->put(route('admin.author-profiles.update', $author), [
                'display_name' => 'Suspended Author',
                'public_author_slug' => 'suspended-author',
                'author_bio' => 'Preserved attribution biography.',
                'author_profile_active' => '1',
            ])
            ->assertRedirect(route('admin.author-profiles.edit', $author))
            ->assertSessionHasErrors('author_profile_active');
    }

    public function test_author_validation_errors_render_accessible_summary(): void
    {
        $owner = User::factory()->owner()->create();
        $author = User::factory()->author()->create();

        $this->actingAs($owner)
            ->followingRedirects()
            ->from(route('admin.author-profiles.edit', $author))
            ->put(route('admin.author-profiles.update', $author), [
                'display_name' => '',
                'public_author_slug' => 'x',
                'website' => 'invalid',
                'social_links' => ['github' => 'invalid'],
                'author_profile_active' => '1',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_avatar_fallback_updates_and_media_reference_can_be_removed(): void
    {
        $owner = User::factory()->owner()->create();
        $author = User::factory()->author()->create(['avatar_media_id' => 42]);

        $this->actingAs($owner)
            ->patch(route('admin.people-identity.avatar.update', $author), [
                'avatar_media_id' => 42,
                'avatar_color' => '#123ABC',
                'remove_avatar' => '1',
            ])
            ->assertRedirect(route('admin.people-identity.show', $author));

        $author->refresh();
        $this->assertNull($author->avatar_media_id);
        $this->assertSame('#123ABC', $author->avatar_color);
        $this->assertFalse(app(AvatarResolver::class)->resolve($author)['media_library_ready']);
        $this->assertDatabaseHas('user_audit_events', [
            'subject_id' => $author->id,
            'event' => 'user.avatar_updated',
        ]);
    }

    public function test_membership_summary_is_date_aware_and_does_not_change_roles(): void
    {
        $member = User::factory()->create([
            'current_plan_reference' => 'premium-yearly',
            'membership_status' => 'active',
            'premium_ends_at' => now()->subDay(),
        ]);

        $summary = app(MembershipSummaryResolver::class)->resolve($member);

        $this->assertSame('expired', $summary['status']);
        $this->assertSame('member', $member->role);
        $this->assertFalse($member->hasAdminAccess());
    }
}
