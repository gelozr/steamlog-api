<?php

namespace Tests\Unit;

use App\Contracts\GameDataProvider;
use App\Enum\EnrichmentStatus;
use App\Events\GameEnrichmentStatusUpdated;
use App\Jobs\EnrichGameJob;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class EnrichGameJobTest extends TestCase
{
    public function test_it_processes_enrichment_successfully(): void
    {
        Event::fake([
            GameEnrichmentStatusUpdated::class,
        ]);

        $game = Game::factory()->create([
            'steam_app_id' => '123123',
        ]);

        $responseData = [
            'name' => 'PUBG',
            'short_description' => 'Battle Royale',
            'genres' => [
                ['description' => 'Action']
            ],
            'release_date' => [
                'coming_soon' => false,
                'date' => '2017-01-01',
            ],
        ];

        $this->mock(GameDataProvider::class, function (MockInterface $mock) use ($game, $responseData) {
            $mock->shouldReceive('fetchDetails')
                ->once()
                ->with($game->steam_app_id)
                ->andReturn($responseData);
        });

        $this->mock(GameService::class, function (MockInterface $mock) use ($game, $responseData) {
            $mock->shouldReceive('markEnrichmentAs')
                ->once()
                ->with(
                    Mockery::on(fn (Game $gameArg) => $game->id == $gameArg->id),
                    EnrichmentStatus::InProgress
                );

            $mock->shouldReceive('update')
                ->once()
                ->with(
                    Mockery::on(fn (Game $gameArg) => $game->id == $gameArg->id),
                    [
                        'name' => $responseData['name'],
                        'short_description' => $responseData['short_description'],
                        'genre' => $responseData['genres'][0]['description'],
                        'release_date' => $responseData['release_date']['date'],
                        'enrichment_status' => EnrichmentStatus::Done,
                    ]
                );
        });

        EnrichGameJob::dispatch($game);

        Event::assertDispatched(GameEnrichmentStatusUpdated::class, function ($e) use ($game) {
            return $e->game->id === $game->id
                && $e->enrichmentStatus === EnrichmentStatus::Done;
        });
    }
}
