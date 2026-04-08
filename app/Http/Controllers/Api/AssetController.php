<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use App\Repositories\AssetRepositoryInterface;
use App\Services\MediaManagementService;
use Illuminate\Http\Request;

class AssetController extends BaseController
{
    public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected MediaManagementService $mediaService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('manage_assets');
        
        $assets = $this->assetRepository->getAll(
            $request->get('per_page', 20),
            $request->get('folder'),
            $request->get('search'),
            $request->get('tag'),
            $request->get('type')
        );

        $assets->getCollection()->transform(function($asset) {
            $asset->url = asset('storage/assets/' . $asset->folder . '/' . $asset->file_name);
            return $asset;
        });

        return $this->paginatedResponse($assets, 'Assets retrieved successfully.');
    }

    public function store(Request $request)
    {
        $this->authorize('manage_assets');
        
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,gif,webp,svg,pdf,doc,docx,mp4,webm,mov', // 10MB max
            'folder' => 'nullable|string|max:50|regex:/^[a-z0-9_\-]+$/i',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $folder = $request->get('folder', 'uncategorized');
        
        $fileInfo = $this->mediaService->uploadAndCompress($request->file('file'), $folder);
        
        $assetData = array_merge($fileInfo, [
            'user_id' => $request->user()->id,
            'title' => $request->get('title'),
            'alt_text' => $request->get('alt_text'),
        ]);

        $asset = $this->assetRepository->create($assetData);
        $asset->url = asset('storage/assets/' . $asset->folder . '/' . $asset->file_name);

        return $this->successResponse($asset, 'Asset uploaded successfully.', 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage_assets');
        
        $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
        ]);

        $success = $this->assetRepository->update($id, $request->only('title', 'alt_text', 'tags'));

        if (!$success) {
            return $this->errorResponse('Asset not found', 404);
        }

        $asset = $this->assetRepository->findById($id);
        return $this->successResponse($asset, 'Asset updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage_assets');

        $asset = $this->assetRepository->findById($id);
        if (!$asset) {
            return $this->errorResponse('Asset not found', 404);
        }

        // Soft-delete the DB row and remove the underlying file from disk.
        $this->mediaService->deleteFile($asset->folder, $asset->file_name);
        $this->assetRepository->delete($id);

        return $this->successResponse(null, 'Asset deleted successfully.');
    }

    /**
     * Return the list of distinct folder names currently in use, so the
     * UI can offer them as a dropdown.
     */
    public function folders()
    {
        $this->authorize('manage_assets');

        $folders = Asset::query()
            ->select('folder')
            ->distinct()
            ->orderBy('folder')
            ->pluck('folder')
            ->filter()
            ->values();

        return $this->successResponse($folders, 'Folders retrieved.');
    }
}
