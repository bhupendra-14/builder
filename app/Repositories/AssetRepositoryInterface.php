<?php

namespace App\Repositories;

use App\Models\Asset;

interface AssetRepositoryInterface
{
    public function getAll(int $perPage = 20, string $folder = null, string $search = null, string $tag = null, string $type = null);
    public function findById(int $id): ?Asset;
    public function create(array $data): Asset;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
