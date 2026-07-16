<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'section',
        'summary',
        'markdown_path',
        'route_path',
        'roles',
        'sort_order',
        'is_public',
        'metadata',
    ];

    protected $table = 'help_articles';

    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'sort_order' => 'integer',
            'is_public' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
