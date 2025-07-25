<?php

namespace App\Services;

use App\Enum\EnrichmentStatus;
use App\Events\GameEnrichmentStatusUpdated;
use App\Jobs\EnrichGameJob;
use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class GameService
{
    public function search(
        ?string $name = null,
        array $filter = [],
        ?string $sort = null,
        int $perPage = 50,
        int $page = 1
    ): LengthAwarePaginator {
        return Game::query()
            ->when($name, function ($query, $name) {
                $query->where('name', 'like', '%' . addcslashes($name, '%_') . '%');
            })
            ->when($filter, function ($query, $filter) {
                foreach ($filter as $field => $value) {
                    $query->where($field, $value);
                }
            })
            ->when($sort, function ($query) use ($sort) {
                foreach ($this->buildSort($sort) as $s) {
                    $query->orderBy($s['field'], $s['direction']);
                }
            })
            ->paginate(perPage: $perPage, page: $page);
    }

    public function create(array $data): Game
    {
        $steamAppId = $data['steam_app_id'] ?? null;
        $genre = $data['genre'] ?? null;
        $releaseDate = $data['release_date'] ?? null;

        $game = Game::query()->create([
            'name' => $data['name'],
            'short_description' => $data['short_description'] ?? null,
            'genre' => $genre,
            'release_date' => $releaseDate,
            'steam_app_id' => $steamAppId,
            'enrichment_status' => $steamAppId ? EnrichmentStatus::Pending : EnrichmentStatus::Skipped,
        ]);

        if ($steamAppId) {
            dispatch(new EnrichGameJob($game));
        }

        return $game;
    }

    public function getById(int $id): ?Game
    {
        return Game::query()->find($id);
    }

    public function updateById(int $id, array $data): Game
    {
        if (! $game = $this->getById($id)) {
            throw new ModelNotFoundException('Game not found.');
        }

        return $this->update($game, $data);
    }

    public function update(Game $game, array $data): Game
    {
        if (empty($data)) {
            return $game;
        }

        $game->update($data);

        if ($game->wasChanged('steam_app_id')) {
            if ($game->steam_app_id) {
                $this->markEnrichmentAs($game, EnrichmentStatus::Pending);

                dispatch(new EnrichGameJob($game));
            } else {
                $this->markEnrichmentAs($game, EnrichmentStatus::Skipped);
            }
        }

        return $game;
    }

    public function delete(int $id): void
    {
        if (! $game = $this->getById($id)) {
            throw new ModelNotFoundException('Game not found.');
        }

        $game->delete();
    }

    public function markEnrichmentAs(Game $game, EnrichmentStatus $status): void
    {
        $this->update($game, [
            'enrichment_status' => $status,
        ]);

        event(new GameEnrichmentStatusUpdated($game, $status));
    }

    public function getGenres(): array
    {
        return Game::query()
            ->select('genre')
            ->distinct()
            ->orderBy('genre')
            ->get()
            ->pluck('genre')
            ->toArray();
    }

    private function buildSort(string $sort): array
    {
        if (! $sort) {
            return [];
        }

        return Str::of($sort)
            ->explode(',')
            ->map(function ($value) {
                $s = optional(Str::of($value)->explode(':')->toArray());

                return [
                    'field' => $s[0],
                    'direction' => $s[1] ?: 'asc',
                ];
            })
            ->toArray();
    }
}
