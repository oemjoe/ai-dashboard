<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisSnapshot extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $fillable = [
        'company_id',
        'module_slug',
        'period_label',
        'filters',
        'metrics',
        'findings',
        'mapping_status',
        'source_reference',
        'data_as_of',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'filters' => 'array',
            'metrics' => 'array',
            'findings' => 'array',
            'data_as_of' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
