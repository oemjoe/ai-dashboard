<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardModule extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'navigation_group',
        'description',
        'permission_name',
        'mapping_status',
        'guide_slug',
        'display_order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(AnalysisSnapshot::class, 'module_slug', 'slug');
    }
}
