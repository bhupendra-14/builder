<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'draft_content' => 'array',
        'dark_preview_content' => 'array',
        'live_published_content' => 'array',
        'enabled' => 'boolean',
        'show_in_nav' => 'boolean',
    ];

    public function versions()
    {
        return $this->hasMany(SectionVersion::class);
    }
}
