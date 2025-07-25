<?php

namespace App\Jobs;

use App\Contracts\GameDataProvider;
use App\Enum\EnrichmentStatus;
use App\Events\GameEnrichmentStatusUpdated;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnrichGameJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 5;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public Game $game)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        GameService      $gameService,
        GameDataProvider $steamGameDataFetcher,
    ): void {
        if (! $this->game->steam_app_id) {
            return;
        }

        $gameService->markEnrichmentAs($this->game, EnrichmentStatus::InProgress);

        $data = $steamGameDataFetcher->fetchDetails($this->game->steam_app_id);

        if (empty($data)) {
            $gameService->markEnrichmentAs($this->game, EnrichmentStatus::Invalid);
            return;
        }

        $gameService->update($this->game, [
            'name' => $data['name'],
            'short_description' => $data['short_description'],
            'genre' => $this->getGenre($data['genres']),
            'release_date' => $this->getReleaseDate($data['release_date']),
            'enrichment_status' => EnrichmentStatus::Done,
        ]);

        event(new GameEnrichmentStatusUpdated($this->game, EnrichmentStatus::Done));
    }

    private function getGenre(array $genres): ?string
    {
        $genres = optional($genres);

        if (! $genre = $genres[0]) {
            return null;
        }

        return $genre['description'] ?? null;
    }

    private function getReleaseDate(array $releaseDate): ?string
    {
        $releaseDate = optional($releaseDate);

        if ($releaseDate['coming_soon']) {
            return null;
        }

        if (! $date = $releaseDate['date']) {
            return null;
        }

        try {
            return Date::parse($date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Enrichment failed', [
            'message' => $exception?->getMessage(),
            'game_id' => $this->game->id
        ]);

        $gameService = app(GameService::class);
        $gameService->markEnrichmentAs($this->game, EnrichmentStatus::Failed);
    }
}
