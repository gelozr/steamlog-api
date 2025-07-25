<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchGameRequest;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameCollection;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

readonly class GameController
{
    public function __construct(private GameService $gameService)
    {
    }

    public function index(SearchGameRequest $request): GameCollection
    {
        $q = trim($request->query('q'));
        $filter = $request->query('filter', []);
        $sort = trim($request->query('sort'));
        $perPage = $request->query('per_page', 50);
        $page = $request->query('page', 1);

        return GameCollection::make(
            $this->gameService->search($q, $filter, $sort, $perPage, $page)
        );
    }

    public function show(int $id): GameResource
    {
        if (! $game = $this->gameService->getById($id)) {
            abort(Response::HTTP_NOT_FOUND, 'Game not found.');
        }

        return GameResource::make($game);
    }

    public function store(StoreGameRequest $request): GameResource
    {
        $game = $this->gameService->create($request->validated());

        return GameResource::make($game);
    }

    public function update(UpdateGameRequest $request, int $id): GameResource
    {
        $game = $this->gameService->updateById($id, $request->validated());

        return GameResource::make($game);
    }

    public function destroy(int $id): Response
    {
        $this->gameService->delete($id);
        return response()->noContent();
    }

    public function genres(): JsonResponse
    {
        return response()->json([
            'data' => $this->gameService->getGenres()
        ]);
    }
}
