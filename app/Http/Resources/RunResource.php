<?php

namespace App\Http\Resources;

use App\Models\Run;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Run
 */
class RunResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, ran_on: string, ran_on_label: string, distance: string, duration: string, pace: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ran_on' => $this->ran_on->toDateString(),
            'ran_on_label' => $this->ran_on->format('D, j M'),
            'distance' => $this->distance_km.' km',
            'duration' => $this->duration_label,
            'pace' => $this->pace_label,
        ];
    }
}
