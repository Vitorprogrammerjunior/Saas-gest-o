<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use App\Models\Workspace;
use App\Events\BoardUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /**
     * Create column — O(1)
     */
    public function store(Request $request, Workspace $workspace, Board $board): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $maxPosition = $board->columns()->max('position') ?? -1;

        $column = $board->columns()->create([
            ...$validated,
            'position' => $maxPosition + 1,
        ]);

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json($column->load('cards'), 201);
    }

    /**
     * Update column — O(1)
     */
    public function update(Request $request, Workspace $workspace, Board $board, Column $column): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $column->update($validated);

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json($column);
    }

    /**
     * Reorder columns — O(k) batch update where k = number of columns
     */
    public function reorder(Request $request, Workspace $workspace, Board $board): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:columns,id'],
        ]);

        Column::reorder($board->id, $validated['ordered_ids']);

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json(['message' => 'Colunas reordenadas com sucesso.']);
    }

    /**
     * Delete column — O(1) with CASCADE
     */
    public function destroy(Request $request, Workspace $workspace, Board $board, Column $column): JsonResponse
    {
        $this->authorize('update', $workspace);

        $column->delete();

        broadcast(new BoardUpdated($board))->toOthers();

        return response()->json(null, 204);
    }
}
