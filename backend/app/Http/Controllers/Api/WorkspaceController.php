<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * List workspaces for authenticated user — O(k) where k = user's workspaces
     */
    public function index(Request $request): JsonResponse
    {
        $workspaces = $request->user()
            ->workspaces()
            ->withCount('boards', 'members')
            ->orderBy('name')
            ->get();

        return response()->json($workspaces);
    }

    /**
     * Create workspace — O(1)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $workspace = Workspace::create([
            ...$validated,
            'owner_id' => $request->user()->id,
        ]);

        // Attach creator as owner member
        $workspace->members()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json(
            $workspace->load('members'),
            201
        );
    }

    /**
     * Show workspace with members — O(1) indexed lookup
     */
    public function show(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        return response()->json(
            $workspace->load(['members', 'boards' => fn ($q) => $q->active()])
                ->loadCount('boards', 'members')
        );
    }

    /**
     * Update workspace — O(1)
     */
    public function update(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $workspace->update($validated);

        return response()->json($workspace);
    }

    /**
     * Delete workspace — O(1) with CASCADE
     */
    public function destroy(Workspace $workspace): JsonResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return response()->json(null, 204);
    }
}
