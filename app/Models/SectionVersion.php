<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Soft-delete behaviour:
 * The section_versions.section_id FK uses ON DELETE CASCADE, which only
 * fires on a hard delete. Soft-deleting a Section therefore leaves its
 * version history intact — restoring the section restores its drafts.
 * Only forceDelete() on a Section will cascade-remove its versions.
 */
class SectionVersion extends Model
{
    protected $guarded = ['id'];
    
    // Explicitly define updated_at as null since we didn't add it in migration
    const UPDATED_AT = null;

    protected $casts = [
        'content' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function saver()
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
