<?php

namespace App\Models;

use Database\Factories\RunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $ran_on
 * @property string $distance_km
 * @property int $duration_seconds
 * @property-read string $duration_label
 * @property-read string $pace_label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['ran_on', 'distance_km', 'duration_seconds'])]
class Run extends Model
{
    /** @use HasFactory<RunFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ran_on' => 'date',
            'distance_km' => 'decimal:2',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the runner who logged the run.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Run>  $query
     */
    #[Scope]
    protected function ranThisWeek(Builder $query): void
    {
        $query->whereBetween('ran_on', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * The duration as the runner reads it: "59:58" or "1:36:00".
     *
     * @return Attribute<string, never>
     */
    protected function durationLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->formatDuration());
    }

    /**
     * The average pace per kilometre: "5:30 /km".
     *
     * @return Attribute<string, never>
     */
    protected function paceLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->formatPace());
    }

    private function formatDuration(): string
    {
        $hours = intdiv($this->duration_seconds, 3600);
        $minutes = intdiv($this->duration_seconds % 3600, 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    private function formatPace(): string
    {
        $distanceKm = (float) $this->distance_km;

        if ($distanceKm <= 0 || $this->duration_seconds <= 0) {
            return '—';
        }

        $secondsPerKm = $this->duration_seconds / $distanceKm;
        $minutes = (int) floor($secondsPerKm / 60);
        $seconds = (int) round(fmod($secondsPerKm, 60));

        return sprintf('%d:%02d /km', $minutes, $seconds);
    }
}
