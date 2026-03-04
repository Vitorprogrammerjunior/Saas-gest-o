<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'column_id', 'assigned_to',
        'created_by', 'position', 'priority', 'due_date', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'card_label');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    // ─── Reorder — O(k) batch update ─────────────────────

    public static function reorder(int $columnId, array $orderedIds): void
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
            "UPDATE cards SET position = CASE {$caseSql} END WHERE id IN ({$ids}) AND column_id = ?",
            [...$bindings, $columnId]
        );
    }

    /**
     * Move card to a different column — O(1) operation
     */
    public function moveTo(int $columnId, int $position): void
    {
        $this->update([
            'column_id' => $columnId,
            'position' => $position,
        ]);
    }
}
