<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * List labels — O(k)
     */
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        return response()->json(
            $workspace->labels()->orderBy('name')->get()
        );
    }

    /**
     * Create label — O(1)
     */
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $label = $workspace->labels()->create($validated);

        return response()->json($label, 201);
    }

    /**
     * Update label — O(1)
     */
    public function update(Request $request, Workspace $workspace, Label $label): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $label->update($validated);

        return response()->json($label);
    }

    /**
     * Delete label — O(1)
     */
    public function destroy(Workspace $workspace, Label $label): JsonResponse
    {
        $this->authorize('update', $workspace);

        $label->delete();

        return response()->json(null, 204);
    }
}
