<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['code', 'name', 'category', 'region', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dataConnections(): HasMany
    {
        return $this->hasMany(DataConnection::class);
    }

    public function analysisSnapshots(): HasMany
    {
        return $this->hasMany(AnalysisSnapshot::class);
    }
}
