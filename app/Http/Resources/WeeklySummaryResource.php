<?php

namespace App\Http\Resources;

use App\Support\WeeklySummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin WeeklySummary
 */
class WeeklySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{distance: string, runs: string, goal: string, progress: float}
     */
    public function toArray(Request $request): array
    {
        $distance = number_format($this->distanceKm, 2);

        return [
            'distance' => $distance.' km',
            'runs' => $this->runCount.' '.Str::plural('run', $this->runCount),
            'goal' => $distance.' / '.WeeklySummary::GOAL_KM.' km',
            'progress' => $this->goalProgress(),
        ];
    }
}
