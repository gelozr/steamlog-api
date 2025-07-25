<?php

namespace App\Models;

use App\Enum\EnrichmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'genre',
        'release_date',
        'steam_app_id',
        'enrichment_status',
    ];

    protected function casts(): array
    {
        return [
            'enrichment_status' => EnrichmentStatus::class,
        ];
    }
}
