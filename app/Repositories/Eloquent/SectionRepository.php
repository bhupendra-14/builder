<?php

namespace App\Repositories\Eloquent;

use App\Models\Section;
use App\Repositories\SectionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SectionRepository implements SectionRepositoryInterface
{
    public function getAllOrdered()
    {
        return Section::orderBy('order', 'asc')->get();
    }

    public function findById(int $id): ?Section
    {
        return Section::find($id);
    }

    public function create(array $data): Section
    {
        return Section::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $section = $this->findById($id);
        if (!$section) return false;
        
        return $section->update($data);
    }

    public function updateOrder(array $newOrder): bool
    {
        return DB::transaction(function () use ($newOrder) {
            foreach ($newOrder as $item) {
                Section::where('id', $item['id'])->update(['order' => $item['order']]);
            }
            return true;
        });
    }

    public function delete(int $id): bool
    {
        $section = $this->findById($id);
        if (!$section) return false;

        return $section->delete();
    }

    public function duplicate(int $id): ?Section
    {
        $source = $this->findById($id);
        if (!$source) return null;

        return DB::transaction(function () use ($source) {
            // Shift all sections after the source down by 1 to make room.
            Section::where('order', '>', $source->order)->increment('order');

            $copy = $source->replicate(['status']);
            $copy->label = $source->label . ' (Copy)';
            $copy->order = $source->order + 1;
            $copy->status = 'draft';
            $copy->dark_preview_content = null;
            $copy->live_published_content = null;
            $copy->save();

            return $copy;
        });
    }
}
