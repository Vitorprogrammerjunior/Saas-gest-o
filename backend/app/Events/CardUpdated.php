<?php

namespace App\Events;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Card $card,
        public Board $board
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("board.{$this->board->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'card_id' => $this->card->id,
            'board_id' => $this->board->id,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'card.updated';
    }
}
