<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'workspace_id', 'color', 'is_archived'];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    /**
     * Get all cards through columns — avoids N+1 with eager loading
     */
    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, Column::class);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }
}
