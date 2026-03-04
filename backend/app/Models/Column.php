<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'board_id', 'position', 'color'];

    // ─── Relationships ───────────────────────────────────

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }

    // ─── Position helpers ────────────────────────────────

    /**
     * Reorder columns within a board.
     * Uses batch UPDATE — O(k) where k = number of columns moved.
     */
    public static function reorder(int $boardId, array $orderedIds): void
    {
        $cases = [];
        $bindings = [];

        foreach ($orderedIds as $position => $id) {
            $cases[] = "WHEN id = ? THEN ?";
            $bindings[] = $id;
            $bindings[] = $position;
        }

        $ids = implode(',', array_map('intval', $orderedIds));
        $caseSql = implode(' ', $cases);

        \DB::update(
            "UPDATE columns SET position = CASE {$caseSql} END WHERE id IN ({$ids}) AND board_id = ?",
            [...$bindings, $boardId]
        );
    }
}
