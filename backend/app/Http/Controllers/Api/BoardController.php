<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Board;
use App\Models\Column;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    /**
     * List boards for a workspace — O(k) where k = boards
     */
    public function index(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $boards = $workspace->boards()
            ->when($request->boolean('archived'), fn ($q) => $q->where('is_archived', true), fn ($q) => $q->active())
            ->withCount('columns', 'cards')
            ->orderBy('name')
            ->get();

        return response()->json($boards);
    }

    /**
     * Create board with default columns — O(1)
     */
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $board = $workspace->boards()->create($validated);

        // Create default columns
        $defaultColumns = ['A Fazer', 'Em Progresso', 'Revisão', 'Concluído'];
        $colors = ['#94a3b8', '#3b82f6', '#f59e0b', '#22c55e'];

        foreach ($defaultColumns as $i => $name) {
            $board->columns()->create([
                'name' => $name,
                'position' => $i,
                'color' => $colors[$i],
            ]);
        }

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'board_created',
            $board
        );

        return response()->json(
            $board->load('columns'),
            201
        );
    }

    /**
     * Show board with columns and cards — O(c + k) where c = columns, k = cards
     */
    public function show(Workspace $workspace, Board $board): JsonResponse
    {
        $this->authorize('view', $workspace);

        $board->load([
            'columns.cards' => fn ($q) => $q->with(['assignee:id,name,email', 'labels', 'creator:id,name'])
                ->withCount('comments')
                ->orderBy('position'),
        ]);

        return response()->json($board);
    }

    /**
     * Update board — O(1)
     */
    public function update(Request $request, Workspace $workspace, Board $board): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_archived' => ['sometimes', 'boolean'],
        ]);

        $board->update($validated);

        return response()->json($board);
    }

    /**
     * Delete board — O(1) with CASCADE
     */
    public function destroy(Request $request, Workspace $workspace, Board $board): JsonResponse
    {
        $this->authorize('update', $workspace);

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'board_deleted',
            $board,
            ['board_name' => $board->name]
        );

        $board->delete();

        return response()->json(null, 204);
    }
}
