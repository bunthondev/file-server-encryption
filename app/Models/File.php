<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    protected $fillable = [
        'bucket_id',
        'original_name',
        'encrypted_name',
        'mime_type',
        'size',
        'path',
    ];

    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class);
    }

    public function user()
    {
        return $this->bucket->user();
    }
} 