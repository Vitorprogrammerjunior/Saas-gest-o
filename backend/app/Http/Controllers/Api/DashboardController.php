<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get workspace dashboard stats — O(1) with caching
     * Without cache: O(n) where n = total cards
     */
    public function stats(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $cacheKey = "dashboard_stats_{$workspace->id}";
        $ttl = 60; // Cache for 60 seconds

        $stats = Cache::remember($cacheKey, $ttl, function () use ($workspace) {
            $boardIds = $workspace->boards()->pluck('id');
            $columnIds = DB::table('columns')
                ->whereIn('board_id', $boardIds)
                ->pluck('id');

            // Total cards by column
            $cardsByColumn = DB::table('cards')
                ->join('columns', 'cards.column_id', '=', 'columns.id')
                ->whereIn('cards.column_id', $columnIds)
                ->select('columns.name', DB::raw('COUNT(*) as count'))
                ->groupBy('columns.name')
                ->get();

            // Cards by priority
            $cardsByPriority = DB::table('cards')
                ->whereIn('column_id', $columnIds)
                ->select('priority', DB::raw('COUNT(*) as count'))
                ->groupBy('priority')
                ->get();

            // Cards completed per day (last 30 days)
            $completedPerDay = DB::table('cards')
                ->whereIn('column_id', $columnIds)
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(completed_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(completed_at)'))
                ->orderBy('date')
                ->get();

            // Cards created per day (last 30 days)
            $createdPerDay = DB::table('cards')
                ->whereIn('column_id', $columnIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Overdue cards
            $overdueCount = DB::table('cards')
                ->whereIn('column_id', $columnIds)
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereNull('completed_at')
                ->count();

            // Member productivity (cards completed per member)
            $memberProductivity = DB::table('cards')
                ->join('users', 'cards.assigned_to', '=', 'users.id')
                ->whereIn('cards.column_id', $columnIds)
                ->whereNotNull('cards.completed_at')
                ->where('cards.completed_at', '>=', now()->subDays(30))
                ->select('users.name', DB::raw('COUNT(*) as completed'))
                ->groupBy('users.name')
                ->orderByDesc('completed')
                ->limit(10)
                ->get();

            // Total counts
            $totalCards = DB::table('cards')->whereIn('column_id', $columnIds)->count();
            $completedCards = DB::table('cards')
                ->whereIn('column_id', $columnIds)
                ->whereNotNull('completed_at')
                ->count();

            return [
                'total_cards' => $totalCards,
                'completed_cards' => $completedCards,
                'overdue_cards' => $overdueCount,
                'completion_rate' => $totalCards > 0
                    ? round(($completedCards / $totalCards) * 100, 1)
                    : 0,
                'cards_by_column' => $cardsByColumn,
                'cards_by_priority' => $cardsByPriority,
                'completed_per_day' => $completedPerDay,
                'created_per_day' => $createdPerDay,
                'member_productivity' => $memberProductivity,
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get recent activity — O(k) where k = limit
     */
    public function activity(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $activities = $workspace->activityLogs()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($activity) {
                $actionLabels = [
                    'board_created' => 'criou um board',
                    'board_deleted' => 'deletou um board',
                    'card_created' => 'criou um card',
                    'card_updated' => 'atualizou um card',
                    'card_moved' => 'moveu um card',
                    'card_deleted' => 'deletou um card',
                    'comment_added' => 'adicionou um comentário',
                ];
                $activity->description = $actionLabels[$activity->action] ?? $activity->action;
                return $activity;
            });

        return response()->json($activities);
    }
}
