<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\BoardUpdated;
use App\Events\CardUpdated;
use App\Models\ActivityLog;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Create a card — O(1)
     */
    public function store(Request $request, Workspace $workspace, Board $board, Column $column): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:labels,id'],
        ]);

        $maxPosition = $column->cards()->max('position') ?? -1;

        $card = $column->cards()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'position' => $maxPosition + 1,
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        if (!empty($validated['label_ids'])) {
            $card->labels()->sync($validated['label_ids']);
        }

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'card_created',
            $card,
            ['column' => $column->name]
        );

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json(
            $card->load(['assignee:id,name,email', 'labels', 'creator:id,name']),
            201
        );
    }

    /**
     * Show card with details — O(1) indexed lookup + eager loading
     */
    public function show(Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('view', $workspace);

        return response()->json(
            $card->load([
                'assignee:id,name,email',
                'creator:id,name',
                'labels',
                'comments.user:id,name',
                'column:id,name',
            ])
        );
    }

    /**
     * Update card — O(1)
     */
    public function update(Request $request, Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'completed' => ['nullable', 'boolean'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:labels,id'],
        ]);

        // Handle completed boolean → completed_at timestamp
        if (isset($validated['completed'])) {
            $validated['completed_at'] = $validated['completed'] ? now() : null;
        }
        unset($validated['completed']);

        $labelIds = $validated['label_ids'] ?? null;
        unset($validated['label_ids']);

        $card->update($validated);

        if ($labelIds !== null) {
            $card->labels()->sync($labelIds);
        }

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'card_updated',
            $card
        );

        broadcast(new CardUpdated($card, $board))->toOthers();

        return response()->json(
            $card->load(['assignee:id,name,email', 'labels', 'creator:id,name'])
        );
    }

    /**
     * Move card to different column — O(1)
     * This is called when drag & drop moves a card between columns
     */
    public function move(Request $request, Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'column_id' => ['required', 'integer'],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        // Verify column belongs to this board
        $targetColumn = Column::where('id', $validated['column_id'])
            ->where('board_id', $board->id)
            ->firstOrFail();

        $oldColumn = $card->column;
        $card->moveTo($validated['column_id'], $validated['position']);

        $newColumn = Column::find($validated['column_id']);

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'card_moved',
            $card,
            [
                'from_column' => $oldColumn->name,
                'to_column' => $newColumn->name,
            ]
        );

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json($card->fresh()->load(['assignee:id,name,email', 'labels']));
    }

    /**
     * Reorder cards within a column — O(k) batch update
     */
    public function reorder(Request $request, Workspace $workspace, Board $board, Column $column): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:cards,id'],
        ]);

        Card::reorder($column->id, $validated['ordered_ids']);

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json(['message' => 'Cards reordenados com sucesso.']);
    }

    /**
     * Delete card — O(1) with CASCADE
     */
    public function destroy(Request $request, Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('update', $workspace);

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'card_deleted',
            $card,
            ['card_title' => $card->title]
        );

        $card->delete();

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json(null, 204);
    }
}
