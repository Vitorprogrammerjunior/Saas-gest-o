<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\CardUpdated;
use App\Models\Board;
use App\Models\Card;
use App\Models\Comment;
use App\Models\ActivityLog;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * List comments for a card — O(k) where k = comments
     */
    public function index(Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('view', $workspace);

        $comments = $card->comments()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    /**
     * Add comment — O(1)
     */
    public function store(Request $request, Workspace $workspace, Board $board, Card $card): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $card->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        ActivityLog::log(
            $request->user()->id,
            $workspace->id,
            'comment_added',
            $card,
            ['comment_preview' => substr($validated['body'], 0, 100)]
        );

        broadcast(new CardUpdated($card, $board))->toOthers();

        return response()->json(
            $comment->load('user:id,name,email'),
            201
        );
    }

    /**
     * Delete comment — O(1)
     */
    public function destroy(Request $request, Workspace $workspace, Board $board, Card $card, Comment $comment): JsonResponse
    {
        // Only comment author or workspace admin can delete
        if ($comment->user_id !== $request->user()->id) {
            $this->authorize('update', $workspace);
        }

        $comment->delete();

        return response()->json(null, 204);
    }
}
