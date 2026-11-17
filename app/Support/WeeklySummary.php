<?php

namespace App\Support;

use App\Models\User;

final readonly class WeeklySummary
{
    public const int GOAL_KM = 40;

    public function __construct(
        public float $distanceKm,
        public int $runCount,
    ) {}

    public static function forThisWeek(User $user): self
    {
        $runs = $user->runs()->ranThisWeek();

        return new self(
            distanceKm: (float) $runs->sum('distance_km'),
            runCount: $runs->count(),
        );
    }

    public function goalProgress(): float
    {
        return min(100, $this->distanceKm / self::GOAL_KM * 100);
    }
}
