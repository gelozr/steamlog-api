<?php

namespace Tests\Unit;

use App\Jobs\EnrichGameJob;
use App\Services\GameService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    public function test_it_creates_game_and_dispatches_enrichment_job_when_steam_app_id_is_present(): void
    {
        Queue::fake([
            EnrichGameJob::class
        ]);

        $gameService = app(GameService::class);

        $game = $gameService->create([
            'name' => 'PUBG',
            'short_description' => 'Battle Royale',
            'genre' => 'Action',
            'release_date' => '2018-01-01',
            'steam_app_id' => '123123',
        ]);

        Queue::assertPushed(EnrichGameJob::class, function ($job) use ($game) {
            return $job->game->id === $game->id;
        });
    }
}
