<?php

namespace App\Repositories;

use App\Models\Section;

interface SectionRepositoryInterface
{
    public function getAllOrdered();
    public function findById(int $id): ?Section;
    public function create(array $data): Section;
    public function update(int $id, array $data): bool;
    public function updateOrder(array $newOrder): bool;
    public function delete(int $id): bool;
    public function duplicate(int $id): ?Section;
}
