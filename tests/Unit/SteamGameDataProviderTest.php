<?php

namespace Tests\Unit;

use App\Integrations\SteamGameDataProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SteamGameDataProviderTest extends TestCase
{
    public function test_it_fetches_data_from_steam_and_caches_the_result(): void
    {
        $steamAppId = 123456;
        $data = [
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

        Http::fake([
            config('services.steam.api_url') . "/appdetails*" => Http::response([
                "{$steamAppId}" => [
                    'success' => true,
                    'data' => $data
                ]
            ], 200)
        ]);

        $dataProvider = app(SteamGameDataProvider::class);

        $responseData = $dataProvider->fetchDetails($steamAppId);

        $cacheData = Cache::get("steam:appdetails:{$steamAppId}");

        Http::assertSent(function (Request $request) use ($steamAppId) {
            return $request->url() == config('services.steam.api_url') . "/appdetails?appids={$steamAppId}&cc=us&l=en";
        });

        $this->assertEquals($data, $cacheData);
        $this->assertEquals($data, $responseData);
    }
}
