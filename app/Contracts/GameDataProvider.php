<?php

namespace App\Contracts;

interface GameDataProvider
{
    public function fetchDetails(int $steamAppId): array;
}
