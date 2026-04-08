<?php

namespace Tests\Feature;

use App\Models\PublishHistory;
use App\Models\Section;
use App\Services\PublishService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected PublishService $publish;

    protected function setUp(): void
    {
        parent::setUp();
        $this->publish = app(PublishService::class);
    }

    /** Helper: create an enabled draft section. */
    protected function makeSection(array $content = ['headline' => 'Hello'], array $overrides = []): Section
    {
        return Section::create(array_merge([
            'type' => 'hero',
            'label' => 'Test section',
            'order' => 1,
            'enabled' => true,
            'status' => 'draft',
            'draft_content' => $content,
        ], $overrides));
    }

    public function test_publish_to_dark_copies_draft_to_dark_only(): void
    {
        $section = $this->makeSection(['headline' => 'Draft headline']);

        $this->publish->publishToDark(0);

        $section->refresh();
        $this->assertEquals(['headline' => 'Draft headline'], $section->dark_preview_content);
        $this->assertNull($section->live_published_content);
        $this->assertEquals('dark', $section->status);
    }

    public function test_publish_to_live_uses_current_draft_not_stale_dark(): void
    {
        // This is the regression test for the bug where publishToLive was
        // copying dark_preview_content (stale) instead of draft_content (fresh).
        $section = $this->makeSection([
            'items' => [['title' => 'Q1'], ['title' => 'Q2'], ['title' => 'Q3'], ['title' => 'Q4']],
        ], ['type' => 'accordion']);

        // First publish: 4 items go to dark and live.
        $this->publish->publishToLive(0);
        $section->refresh();
        $this->assertCount(4, $section->live_published_content['items']);
        $this->assertCount(4, $section->dark_preview_content['items']);

        // Editor adds a 5th item to the draft.
        $section->update([
            'draft_content' => [
                'items' => [
                    ['title' => 'Q1'], ['title' => 'Q2'], ['title' => 'Q3'], ['title' => 'Q4'], ['title' => 'Q5'],
                ],
            ],
            'status' => 'draft',
        ]);

        // Publish to Live again — should pick up the new 5-item draft, not the
        // stale 4-item dark preview.
        $this->publish->publishToLive(0);
        $section->refresh();
        $this->assertCount(5, $section->live_published_content['items']);
        $this->assertCount(5, $section->dark_preview_content['items'], 'Dark should be kept in sync with live.');
    }

    public function test_publish_to_live_clears_disabled_sections_content(): void
    {
        $live = $this->makeSection(['headline' => 'Stays live'], ['order' => 1]);
        $disabled = $this->makeSection(['headline' => 'Going away'], ['order' => 2, 'enabled' => false]);

        // Seed disabled section with previously-published content.
        $disabled->update([
            'live_published_content' => ['headline' => 'Going away'],
            'dark_preview_content' => ['headline' => 'Going away'],
        ]);

        $this->publish->publishToLive(0);

        $disabled->refresh();
        $this->assertNull($disabled->live_published_content);
        $this->assertNull($disabled->dark_preview_content);

        $live->refresh();
        $this->assertEquals(['headline' => 'Stays live'], $live->live_published_content);
    }

    public function test_publish_to_live_clears_soft_deleted_sections_content(): void
    {
        $kept = $this->makeSection(['headline' => 'Kept'], ['order' => 1]);
        $deleted = $this->makeSection(['headline' => 'Deleted'], ['order' => 2]);
        $deleted->update([
            'live_published_content' => ['headline' => 'Deleted'],
            'dark_preview_content' => ['headline' => 'Deleted'],
        ]);
        $deleted->delete(); // Soft delete

        $this->publish->publishToLive(0);

        // Need to use withTrashed() because the section is soft-deleted.
        $deletedFresh = Section::withTrashed()->find($deleted->id);
        $this->assertNull($deletedFresh->live_published_content);
        $this->assertNull($deletedFresh->dark_preview_content);

        $kept->refresh();
        $this->assertEquals(['headline' => 'Kept'], $kept->live_published_content);
    }

    public function test_publish_to_dark_status_promotes_only_drafts(): void
    {
        $draft = $this->makeSection(['headline' => 'A'], ['status' => 'draft']);
        $live = $this->makeSection(['headline' => 'B'], ['order' => 2, 'status' => 'live']);

        $this->publish->publishToDark(0);

        // Draft → dark; live stays live (it's already further along the pipeline)
        $this->assertEquals('dark', $draft->fresh()->status);
        $this->assertEquals('live', $live->fresh()->status);
    }

    public function test_publish_to_live_creates_history_row(): void
    {
        $this->makeSection(['headline' => 'Test'], ['order' => 1]);
        $this->makeSection(['headline' => 'Test 2'], ['order' => 2]);

        $history = $this->publish->publishToLive(42, 'Release notes here');

        $this->assertInstanceOf(PublishHistory::class, $history);
        $this->assertEquals('live', $history->environment);
        $this->assertEquals(42, $history->published_by);
        $this->assertEquals('Release notes here', $history->release_notes);
        $this->assertCount(2, $history->snapshot);
        $this->assertEquals(['headline' => 'Test'], $history->snapshot[0]['content']);
    }

    public function test_publish_to_dark_creates_history_row(): void
    {
        $this->makeSection(['headline' => 'A']);

        $history = $this->publish->publishToDark(7, 'Preview build');

        $this->assertEquals('dark', $history->environment);
        $this->assertEquals(7, $history->published_by);
        $this->assertEquals('Preview build', $history->release_notes);
        $this->assertCount(1, $history->snapshot);
    }

    public function test_publish_to_live_status_becomes_live(): void
    {
        $section = $this->makeSection(['headline' => 'Hi'], ['status' => 'draft']);

        $this->publish->publishToLive(0);

        $this->assertEquals('live', $section->fresh()->status);
    }

    public function test_publish_to_live_skips_disabled_sections_from_snapshot(): void
    {
        $this->makeSection(['headline' => 'Active'], ['order' => 1]);
        $this->makeSection(['headline' => 'Disabled'], ['order' => 2, 'enabled' => false]);

        $history = $this->publish->publishToLive(0);

        $this->assertCount(1, $history->snapshot);
        $this->assertEquals(['headline' => 'Active'], $history->snapshot[0]['content']);
    }

    public function test_schedule_creates_pending_history(): void
    {
        $runAt = now()->addHour();
        $history = $this->publish->schedule('live', 1, $runAt, 'Scheduled run');

        $this->assertEquals('pending', $history->status);
        $this->assertEquals('live', $history->environment);
        $this->assertEquals(1, $history->published_by);
        $this->assertEquals($runAt->toDateTimeString(), $history->scheduled_at->toDateTimeString());
    }

    public function test_run_scheduled_executes_pending_publish(): void
    {
        $section = $this->makeSection(['headline' => 'Scheduled content']);

        $pending = $this->publish->schedule('live', 1, now()->subMinute(), 'Auto');
        $this->publish->runScheduled($pending);

        $pending->refresh();
        $this->assertEquals('completed', $pending->status);
        $this->assertNotNull($pending->executed_at);

        $section->refresh();
        $this->assertEquals(['headline' => 'Scheduled content'], $section->live_published_content);
    }

    public function test_run_scheduled_is_noop_for_already_completed_history(): void
    {
        $history = PublishHistory::create([
            'environment' => 'live',
            'published_by' => 1,
            'snapshot' => [],
            'status' => 'completed',
        ]);

        // Should not throw, should not change state
        $this->publish->runScheduled($history);

        $this->assertEquals('completed', $history->fresh()->status);
    }
}
