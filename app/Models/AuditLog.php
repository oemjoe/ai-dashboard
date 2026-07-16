<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'role_names',
        'company_code',
        'module_slug',
        'action',
        'question',
        'query_template',
        'database_name',
        'table_name',
        'rows_read',
        'ai_provider',
        'ai_model',
        'response_time_ms',
        'status',
        'error_code',
        'route_name',
        'method',
        'path',
        'ip_address',
        'user_agent',
        'trace_id',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'role_names' => 'array',
            'rows_read' => 'integer',
            'response_time_ms' => 'integer',
            'context' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
