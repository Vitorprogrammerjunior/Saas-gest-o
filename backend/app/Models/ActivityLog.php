<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'workspace_id', 'action',
        'subject_type', 'subject_id', 'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an activity — O(1) insert operation
     */
    public static function log(
        int $userId,
        int $workspaceId,
        string $action,
        Model $subject,
        ?array $properties = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'workspace_id' => $workspaceId,
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'properties' => $properties,
        ]);
    }
}
