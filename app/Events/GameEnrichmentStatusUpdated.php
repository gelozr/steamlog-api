<?php

namespace App\Events;

use App\Enum\EnrichmentStatus;
use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEnrichmentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Game $game,
        public EnrichmentStatus $enrichmentStatus,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("game.enriched"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.enrichment_status';
    }

    public function broadcastWith(): array
    {
        return [
            'game' => $this->game->toArray(),
            'status' => $this->enrichmentStatus,
        ];
    }
}
