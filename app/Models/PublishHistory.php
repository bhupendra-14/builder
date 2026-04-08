<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishHistory extends Model
{
    protected $guarded = ['id'];
    
    // Explicitly define updated_at as null since migration only has created_at
    const UPDATED_AT = null;

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
