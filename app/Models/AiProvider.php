<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'driver',
        'model',
        'base_url',
        'api_key_env_key',
        'priority',
        'timeout_seconds',
        'max_retries',
        'min_confidence',
        'is_enabled',
        'last_success_at',
        'last_error_at',
        'last_error_message',
        'usage_meta',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'timeout_seconds' => 'integer',
            'max_retries' => 'integer',
            'min_confidence' => 'float',
            'is_enabled' => 'boolean',
            'last_success_at' => 'datetime',
            'last_error_at' => 'datetime',
            'usage_meta' => 'array',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true)->orderBy('priority');
    }
}
