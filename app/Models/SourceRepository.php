<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceRepository extends Model
{
    protected $fillable = [
        'name',
        'path',
        'branch',
        'provider',
        'is_active',
        'last_indexed_at',
        'metadata',
    ];

    protected $table = 'source_repositories';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_indexed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
