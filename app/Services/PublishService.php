<?php

namespace App\Services;

use App\Models\Section;
use App\Models\PublishHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PublishService
{
    public function __construct(protected ?Auditor $auditor = null)
    {
        $this->auditor = $this->auditor ?? app(Auditor::class);
    }

    /**
     * Create a pending PublishHistory row to be executed later by the
     * publish:run-scheduled command.
     */
    public function schedule(string $environment, int $userId, Carbon $runAt, ?string $notes = null): PublishHistory
    {
        return PublishHistory::create([
            'environment' => $environment,
            'published_by' => $userId,
            'release_notes' => $notes,
            'snapshot' => [],
            'status' => 'pending',
            'scheduled_at' => $runAt,
        ]);
    }

    /**
     * Execute a previously-scheduled publish. Safe to call multiple times —
     * it no-ops if the row is not pending.
     */
    public function runScheduled(PublishHistory $history): PublishHistory
    {
        if ($history->status !== 'pending') {
            return $history;
        }

        try {
            $executed = $history->environment === 'dark'
                ? $this->publishToDark($history->published_by, $history->release_notes)
                : $this->publishToLive($history->published_by, $history->release_notes);

            $history->update([
                'status' => 'completed',
                'snapshot' => $executed->snapshot,
                'executed_at' => now(),
            ]);

            // The publishTo* methods already created a separate history row;
            // delete it so we don't duplicate history entries for one schedule.
            $executed->delete();
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'executed_at' => now(),
            ]);
            throw $e;
        }

        return $history->fresh();
    }

    public function publishToDark(int $userId, string $notes = null)
    {
        return DB::transaction(function () use ($userId, $notes) {
            // Clear dark preview for any section that is disabled or soft-deleted
            // so it stops showing up in the preview environment.
            Section::withTrashed()
                ->where(function ($q) {
                    $q->where('enabled', false)->orWhereNotNull('deleted_at');
                })
                ->whereNotNull('dark_preview_content')
                ->update(['dark_preview_content' => null]);

            $sections = Section::where('enabled', true)->get();
            $snapshot = [];

            foreach ($sections as $section) {
                $section->dark_preview_content = $section->draft_content;
                if ($section->status === 'draft') {
                    $section->status = 'dark';
                }
                $section->save();

                $snapshot[] = [
                    'section_id' => $section->id,
                    'content' => $section->draft_content,
                    'order' => $section->order,
                ];
            }

            $history = PublishHistory::create([
                'environment' => 'dark',
                'published_by' => $userId,
                'release_notes' => $notes ?? 'Published to Dark environment',
                'snapshot' => $snapshot,
            ]);

            $this->auditor->log('publish.dark', 'publish_history', $history->id, null, [
                'sections_count' => count($snapshot),
                'notes' => $history->release_notes,
            ]);

            return $history;
        });
    }

    public function publishToLive(int $userId, string $notes = null)
    {
        return DB::transaction(function () use ($userId, $notes) {
            // Clear both dark and live content for any section that is
            // disabled or soft-deleted so neither environment serves stale
            // content for sections that no longer belong on the page.
            Section::withTrashed()
                ->where(function ($q) {
                    $q->where('enabled', false)->orWhereNotNull('deleted_at');
                })
                ->where(function ($q) {
                    $q->whereNotNull('live_published_content')
                      ->orWhereNotNull('dark_preview_content');
                })
                ->update([
                    'live_published_content' => null,
                    'dark_preview_content' => null,
                ]);

            $sections = Section::where('enabled', true)->get();
            $snapshot = [];

            foreach ($sections as $section) {
                // Always use the current draft as the source of truth.
                // The previous "dark ?? draft" logic caused stale dark previews
                // to overwrite fresh drafts on Publish to Live.
                $content = $section->draft_content;

                $section->live_published_content = $content;
                // Keep dark in sync so previewing after a live publish shows
                // the same content as the public site.
                $section->dark_preview_content = $content;
                $section->status = 'live';
                $section->save();

                $snapshot[] = [
                    'section_id' => $section->id,
                    'content' => $content,
                    'order' => $section->order,
                ];
            }

            $history = PublishHistory::create([
                'environment' => 'live',
                'published_by' => $userId,
                'release_notes' => $notes ?? 'Published to Live environment',
                'snapshot' => $snapshot,
            ]);

            $this->auditor->log('publish.live', 'publish_history', $history->id, null, [
                'sections_count' => count($snapshot),
                'notes' => $history->release_notes,
            ]);

            return $history;
        });
    }
}
