<?php

namespace Tests\Feature;

use App\Models\Game;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    public function test_index_should_return_paginated_list_of_games(): void
    {
        Game::factory()->count(3)->create();

        $response = $this->getJson('/api/games');

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');
    }

    public function test_index_should_return_correct_paginated_data(): void
    {
        Game::factory()->count(3)->create();
        Game::factory()->create([
            'name' => 'PUBG: BATTLEGROUNDS',
            'genre' => 'Action',
            'release_date' => '2018-01-01',
        ]);

        $response = $this->getJson('/api/games?q=PUBG');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.0.name', 'PUBG: BATTLEGROUNDS')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data');
    }
}
