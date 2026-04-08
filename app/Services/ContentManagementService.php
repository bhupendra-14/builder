<?php

namespace App\Services;

use App\Repositories\SectionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\SectionVersion;

class ContentManagementService
{
    public function __construct(
        protected SectionRepositoryInterface $sectionRepository
    ) {}

    public function updateSectionContent(int $id, array $content, int $userId)
    {
        $section = $this->sectionRepository->findById($id);
        if (!$section) {
            throw new \Exception('Section not found');
        }

        DB::beginTransaction();
        try {
            // Save version history before modifying
            SectionVersion::create([
                'section_id' => $section->id,
                'saved_by' => $userId,
                'content' => $section->draft_content ?? [],
            ]);

            // Auto-prune: keep only the 20 most recent versions per section
            $excessIds = SectionVersion::where('section_id', $section->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip(20)
                ->take(PHP_INT_MAX)
                ->pluck('id');

            if ($excessIds->isNotEmpty()) {
                SectionVersion::whereIn('id', $excessIds)->delete();
            }

            // Update section with new content and set status as draft
            $this->sectionRepository->update($id, [
                'draft_content' => $content,
                'status' => 'draft'
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rollbackToVersion(int $sectionId, int $versionId)
    {
        $version = SectionVersion::where('id', $versionId)
            ->where('section_id', $sectionId)
            ->first();

        if (!$version) {
            throw new \Exception('Version not found');
        }

        return $this->sectionRepository->update($sectionId, [
            'draft_content' => $version->content,
            'status' => 'draft'
        ]);
    }
}
