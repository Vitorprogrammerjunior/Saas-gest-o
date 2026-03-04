<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TaskFlow
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically.
| Auth routes are public, everything else requires Sanctum token.
|
*/

// ─── Auth (Public) ───────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected Routes ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Workspaces
    Route::apiResource('workspaces', WorkspaceController::class);

    // Invitations
    Route::get('/invitations/pending', [InvitationController::class, 'myInvitations']);
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::post('/invitations/{token}/decline', [InvitationController::class, 'decline']);
    Route::post('/workspaces/{workspace}/invite', [InvitationController::class, 'invite']);
    Route::get('/workspaces/{workspace}/invitations', [InvitationController::class, 'index']);
    Route::delete('/workspaces/{workspace}/invitations/{invitation}', [InvitationController::class, 'cancel']);

    // Labels (no shallow — always scoped to workspace)
    Route::apiResource('workspaces.labels', LabelController::class)
        ->except(['show']);

    // Boards (no shallow — always scoped to workspace)
    Route::apiResource('workspaces.boards', BoardController::class);

    // Columns (reorder BEFORE {column} wildcard to avoid route conflict)
    Route::put('/workspaces/{workspace}/boards/{board}/columns/reorder', [ColumnController::class, 'reorder']);
    Route::post('/workspaces/{workspace}/boards/{board}/columns', [ColumnController::class, 'store']);
    Route::put('/workspaces/{workspace}/boards/{board}/columns/{column}', [ColumnController::class, 'update']);
    Route::delete('/workspaces/{workspace}/boards/{board}/columns/{column}', [ColumnController::class, 'destroy']);

    // Cards
    Route::post('/workspaces/{workspace}/boards/{board}/columns/{column}/cards', [CardController::class, 'store']);
    Route::get('/workspaces/{workspace}/boards/{board}/cards/{card}', [CardController::class, 'show']);
    Route::put('/workspaces/{workspace}/boards/{board}/cards/{card}', [CardController::class, 'update']);
    Route::delete('/workspaces/{workspace}/boards/{board}/cards/{card}', [CardController::class, 'destroy']);
    Route::put('/workspaces/{workspace}/boards/{board}/cards/{card}/move', [CardController::class, 'move']);
    Route::put('/workspaces/{workspace}/boards/{board}/columns/{column}/cards/reorder', [CardController::class, 'reorder']);

    // Comments
    Route::get('/workspaces/{workspace}/boards/{board}/cards/{card}/comments', [CommentController::class, 'index']);
    Route::post('/workspaces/{workspace}/boards/{board}/cards/{card}/comments', [CommentController::class, 'store']);
    Route::delete('/workspaces/{workspace}/boards/{board}/cards/{card}/comments/{comment}', [CommentController::class, 'destroy']);

    // Dashboard
    Route::get('/workspaces/{workspace}/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/workspaces/{workspace}/dashboard/activity', [DashboardController::class, 'activity']);
});
