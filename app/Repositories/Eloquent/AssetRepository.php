<?php

namespace App\Repositories\Eloquent;

use App\Models\Asset;
use App\Repositories\AssetRepositoryInterface;

class AssetRepository implements AssetRepositoryInterface
{
    public function getAll(int $perPage = 20, string $folder = null, string $search = null, string $tag = null, string $type = null)
    {
        $query = Asset::query()->orderBy('created_at', 'desc');

        if ($folder) {
            $query->where('folder', $folder);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($tag) {
            // tags is a JSON array column; match any asset containing this tag
            $query->whereJsonContains('tags', $tag);
        }

        if ($type === 'image') {
            $query->where('asset_type', 'image');
        } elseif ($type === 'video') {
            $query->where('mime_type', 'like', 'video/%');
        } elseif ($type === 'document') {
            $query->where('asset_type', 'document');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Asset
    {
        return Asset::find($id);
    }

    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $asset = $this->findById($id);
        if (!$asset) return false;
        
        return $asset->update($data);
    }

    public function delete(int $id): bool
    {
        $asset = $this->findById($id);
        if (!$asset) return false;

        return $asset->delete();
    }
}
