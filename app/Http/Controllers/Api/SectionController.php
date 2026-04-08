<?php

namespace App\Http\Controllers\Api;

use App\Repositories\SectionRepositoryInterface;
use App\Services\ContentManagementService;
use App\Services\SectionContentValidator;
use Illuminate\Http\Request;
use App\Models\SectionVersion;

class SectionController extends BaseController
{
    public function __construct(
        protected SectionRepositoryInterface $sectionRepository,
        protected ContentManagementService $contentService,
        protected SectionContentValidator $contentValidator
    ) {}

    public function index()
    {
        $this->authorize('manage_pages');
        $sections = $this->sectionRepository->getAllOrdered();
        return $this->successResponse($sections, 'Sections retrieved successfully.');
    }

    public function show(int $id)
    {
        $this->authorize('manage_pages');
        $section = $this->sectionRepository->findById($id);
        
        if (!$section) {
            return $this->errorResponse('Section not found', 404);
        }
        return $this->successResponse($section, 'Section retrieved successfully.');
    }

    public function store(Request $request)
    {
        $this->authorize('manage_pages');
        
        $request->validate([
            'type' => 'required|string',
            'label' => 'required|string',
            'order' => 'required|integer',
            'draft_content' => 'nullable|array'
        ]);

        $section = $this->sectionRepository->create($request->all());

        return $this->successResponse($section, 'Section created successfully.', 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage_pages');

        $request->validate([
            'label' => 'sometimes|string',
            'enabled' => 'sometimes|boolean',
            'show_in_nav' => 'sometimes|boolean',
            'nav_label' => 'sometimes|nullable|string|max:60',
            'draft_content' => 'sometimes|array'
        ]);

        if ($request->has('draft_content')) {
            // Validate the content shape against the section's type before
            // writing. The content schema is type-specific so we cannot use
            // Laravel's request validator for this.
            $section = $this->sectionRepository->findById($id);
            if (!$section) {
                return $this->errorResponse('Section not found', 404);
            }

            $contentErrors = $this->contentValidator->validate(
                $section->type,
                $request->input('draft_content')
            );

            if (!empty($contentErrors)) {
                return $this->errorResponse($contentErrors, 422);
            }

            $this->contentService->updateSectionContent($id, $request->input('draft_content'), $request->user()->id);
        }

        // Update other fields
        $data = $request->except('draft_content');
        if (!empty($data)) {
            $this->sectionRepository->update($id, $data);
        }

        $section = $this->sectionRepository->findById($id);
        return $this->successResponse($section, 'Section updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage_pages');
        
        $success = $this->sectionRepository->delete($id);
        if (!$success) {
            return $this->errorResponse('Section not found', 404);
        }

        return $this->successResponse(null, 'Section deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $this->authorize('manage_pages');
        
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|integer|exists:sections,id',
            'sections.*.order' => 'required|integer'
        ]);

        $this->sectionRepository->updateOrder($request->input('sections'));

        return $this->successResponse(null, 'Sections reordered successfully.');
    }

    public function history(int $id)
    {
        $this->authorize('manage_pages');
        
        $versions = SectionVersion::where('section_id', $id)->orderBy('created_at', 'desc')->get();
        return $this->successResponse($versions, 'Version history retrieved.');
    }

    public function duplicate(int $id)
    {
        $this->authorize('manage_pages');

        $copy = $this->sectionRepository->duplicate($id);
        if (!$copy) {
            return $this->errorResponse('Section not found', 404);
        }

        return $this->successResponse($copy, 'Section duplicated successfully.', 201);
    }

    public function rollback(Request $request, int $id, int $versionId)
    {
        $this->authorize('manage_pages');
        
        try {
            $this->contentService->rollbackToVersion($id, $versionId);
            return $this->successResponse(null, 'Section rolled back successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
