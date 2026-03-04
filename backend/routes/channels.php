<?php

use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('board.{boardId}', function (User $user, int $boardId) {
    $board = Board::with('workspace')->find($boardId);

    if (!$board) {
        return false;
    }

    // O(log n) — indexed lookup to check membership
    return $board->workspace->hasMember($user);
});
