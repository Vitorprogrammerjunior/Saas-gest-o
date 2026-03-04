<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Comment;
use App\Models\Label;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users ───────────────────────────────
        $admin = User::create([
            'name' => 'Vitor Admin',
            'email' => 'admin@taskflow.com',
            'password' => Hash::make('password'),
        ]);

        $member1 = User::create([
            'name' => 'Ana Silva',
            'email' => 'ana@taskflow.com',
            'password' => Hash::make('password'),
        ]);

        $member2 = User::create([
            'name' => 'Pedro Santos',
            'email' => 'pedro@taskflow.com',
            'password' => Hash::make('password'),
        ]);

        // ─── Workspace ──────────────────────────
        $workspace = Workspace::create([
            'name' => 'TaskFlow Team',
            'description' => 'Workspace principal da equipe de desenvolvimento',
            'owner_id' => $admin->id,
        ]);

        $workspace->members()->attach([
            $admin->id => ['role' => 'owner'],
            $member1->id => ['role' => 'admin'],
            $member2->id => ['role' => 'member'],
        ]);

        // ─── Labels ─────────────────────────────
        $labels = [];
        $labelData = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Urgente', 'color' => '#f97316'],
            ['name' => 'Melhoria', 'color' => '#8b5cf6'],
            ['name' => 'Documentação', 'color' => '#06b6d4'],
        ];

        foreach ($labelData as $data) {
            $labels[] = $workspace->labels()->create($data);
        }

        // ─── Board: Sprint Atual ─────────────────
        $board = Board::create([
            'name' => 'Sprint 01 - MVP',
            'description' => 'Sprint de desenvolvimento do MVP do TaskFlow',
            'workspace_id' => $workspace->id,
            'color' => '#6366f1',
        ]);

        $columns = [
            Column::create(['name' => 'Backlog', 'board_id' => $board->id, 'position' => 0, 'color' => '#94a3b8']),
            Column::create(['name' => 'A Fazer', 'board_id' => $board->id, 'position' => 1, 'color' => '#3b82f6']),
            Column::create(['name' => 'Em Progresso', 'board_id' => $board->id, 'position' => 2, 'color' => '#f59e0b']),
            Column::create(['name' => 'Revisão', 'board_id' => $board->id, 'position' => 3, 'color' => '#a855f7']),
            Column::create(['name' => 'Concluído', 'board_id' => $board->id, 'position' => 4, 'color' => '#22c55e']),
        ];

        // ─── Cards ───────────────────────────────
        $cardsData = [
            // Backlog
            ['title' => 'Implementar notificações push', 'description' => 'Adicionar sistema de notificações em tempo real', 'column' => 0, 'position' => 0, 'priority' => 'medium', 'assigned' => $member1, 'labels' => [1]],
            ['title' => 'Tema dark mode', 'description' => 'Implementar toggle de dark/light mode', 'column' => 0, 'position' => 1, 'priority' => 'low', 'assigned' => null, 'labels' => [3]],

            // A Fazer
            ['title' => 'Filtros avançados de cards', 'description' => 'Filtrar por data, prioridade, membro', 'column' => 1, 'position' => 0, 'priority' => 'medium', 'assigned' => $member2, 'labels' => [1]],
            ['title' => 'Export relatórios PDF', 'description' => 'Gerar relatórios de produtividade em PDF', 'column' => 1, 'position' => 1, 'priority' => 'low', 'assigned' => $member1, 'labels' => [1]],

            // Em Progresso
            ['title' => 'API de autenticação', 'description' => 'Login, registro e logout com Sanctum', 'column' => 2, 'position' => 0, 'priority' => 'high', 'assigned' => $admin, 'labels' => [1], 'due' => now()->addDays(2)],
            ['title' => 'Drag & Drop do Kanban', 'description' => 'Implementar movimentação de cards entre colunas', 'column' => 2, 'position' => 1, 'priority' => 'urgent', 'assigned' => $member1, 'labels' => [1, 2]],
            ['title' => 'Corrigir layout responsivo', 'description' => 'Board não funciona bem em mobile', 'column' => 2, 'position' => 2, 'priority' => 'high', 'assigned' => $member2, 'labels' => [0]],

            // Revisão
            ['title' => 'Dashboard de métricas', 'description' => 'Gráficos de produtividade com Chart.js', 'column' => 3, 'position' => 0, 'priority' => 'medium', 'assigned' => $admin, 'labels' => [1]],

            // Concluído
            ['title' => 'Setup do projeto Laravel', 'description' => 'Configuração inicial do backend', 'column' => 4, 'position' => 0, 'priority' => 'high', 'assigned' => $admin, 'labels' => [], 'completed' => now()->subDays(5)],
            ['title' => 'Modelagem do banco de dados', 'description' => 'ERD e migrations criadas', 'column' => 4, 'position' => 1, 'priority' => 'high', 'assigned' => $admin, 'labels' => [4], 'completed' => now()->subDays(3)],
            ['title' => 'Setup Vue.js + Vite', 'description' => 'Projeto frontend configurado', 'column' => 4, 'position' => 2, 'priority' => 'medium', 'assigned' => $member1, 'labels' => [], 'completed' => now()->subDays(2)],
            ['title' => 'Design do Kanban Board', 'description' => 'Prototipação e componentes base', 'column' => 4, 'position' => 3, 'priority' => 'medium', 'assigned' => $member2, 'labels' => [3], 'completed' => now()->subDay()],
        ];

        foreach ($cardsData as $data) {
            $card = Card::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'column_id' => $columns[$data['column']]->id,
                'position' => $data['position'],
                'priority' => $data['priority'],
                'assigned_to' => $data['assigned']?->id,
                'created_by' => $admin->id,
                'due_date' => $data['due'] ?? null,
                'completed_at' => $data['completed'] ?? null,
            ]);

            if (!empty($data['labels'])) {
                $card->labels()->attach(array_map(fn ($i) => $labels[$i]->id, $data['labels']));
            }
        }

        // ─── Comments ────────────────────────────
        $firstCard = Card::first();
        Comment::create(['body' => 'Vamos priorizar essa task na sprint!', 'card_id' => $firstCard->id, 'user_id' => $admin->id]);
        Comment::create(['body' => 'Já comecei a implementar, PR em breve.', 'card_id' => $firstCard->id, 'user_id' => $member1->id]);

        // ─── Second Board ────────────────────────
        $board2 = Board::create([
            'name' => 'Design System',
            'description' => 'Componentes e padrões de UI/UX',
            'workspace_id' => $workspace->id,
            'color' => '#ec4899',
        ]);

        $cols2 = [
            Column::create(['name' => 'A Fazer', 'board_id' => $board2->id, 'position' => 0, 'color' => '#3b82f6']),
            Column::create(['name' => 'Em Progresso', 'board_id' => $board2->id, 'position' => 1, 'color' => '#f59e0b']),
            Column::create(['name' => 'Concluído', 'board_id' => $board2->id, 'position' => 2, 'color' => '#22c55e']),
        ];

        Card::create(['title' => 'Botões e variantes', 'column_id' => $cols2[0]->id, 'position' => 0, 'created_by' => $admin->id, 'priority' => 'medium']);
        Card::create(['title' => 'Tipografia e cores', 'column_id' => $cols2[1]->id, 'position' => 0, 'created_by' => $member1->id, 'priority' => 'high']);
        Card::create(['title' => 'Ícones SVG', 'column_id' => $cols2[2]->id, 'position' => 0, 'created_by' => $member2->id, 'priority' => 'low', 'completed_at' => now()->subDays(1)]);
    }
}
