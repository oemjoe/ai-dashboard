<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataConnection extends Model
{
    protected $fillable = [
        'company_id',
        'slug',
        'name',
        'system_name',
        'db_driver',
        'host',
        'host_masked',
        'port',
        'database_name',
        'username',
        'password_env_key',
        'module_slugs',
        'options',
        'metadata',
        'is_active',
        'is_read_only_validated',
        'last_success_at',
        'last_failure_at',
        'last_failure_message',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'port' => 'integer',
            'module_slugs' => 'array',
            'options' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'is_read_only_validated' => 'boolean',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'response_time_ms' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
