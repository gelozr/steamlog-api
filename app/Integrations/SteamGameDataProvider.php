<?php

namespace App\Integrations;

use App\Contracts\GameDataProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteamGameDataProvider implements GameDataProvider
{
    private PendingRequest $http;

    private int $timeout = 5;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.steam.api_url'))
            ->timeout($this->timeout)
            ->asJson();
    }

    public function fetchDetails(int $steamAppId): array
    {
        return Cache::remember("steam:appdetails:{$steamAppId}", now()->addDay(), function () use ($steamAppId) {
            $response = $this->http
                ->get('/appdetails', [
                    'appids' => $steamAppId,
                    'cc' => 'us',
                    'l' => 'en',
                ])
                ->throw();

            $res = $response->json();

            if (! isset($res[$steamAppId])) {
                Log::error('steam app not found', ['steam_app_id' => $steamAppId]);
                return [];
            }

            $appRes = $res[$steamAppId];

            if (! $appRes['success']) {
                Log::error('steam app data failed', ['steam_app_id' => $steamAppId]);
                return [];
            }

            return $appRes['data'];
        });
    }
}
