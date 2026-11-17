<?php

namespace Database\Seeders;

use App\Models\Run;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The runs are relative to the current week so that the demo always shows
     * a "this week" with 14.50 km, and last Sunday's 10 km in 59:58.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $thisWeek = now()->startOfWeek();
        $lastWeek = $thisWeek->copy()->subWeek();
        $twoWeeksAgo = $thisWeek->copy()->subWeeks(2);

        Run::factory()->for($user)->createMany([
            ['ran_on' => $twoWeeksAgo->copy()->addDays(1), 'distance_km' => 7.00, 'duration_seconds' => 2415],
            ['ran_on' => $twoWeeksAgo->copy()->addDays(3), 'distance_km' => 12.00, 'duration_seconds' => 4140],
            ['ran_on' => $twoWeeksAgo->copy()->addDays(6), 'distance_km' => 16.00, 'duration_seconds' => 5760],
            ['ran_on' => $lastWeek->copy(), 'distance_km' => 6.00, 'duration_seconds' => 1980],
            ['ran_on' => $lastWeek->copy()->addDays(2), 'distance_km' => 8.00, 'duration_seconds' => 2640],
            ['ran_on' => $lastWeek->copy()->addDays(4), 'distance_km' => 5.00, 'duration_seconds' => 1575],
            ['ran_on' => $lastWeek->copy()->addDays(6), 'distance_km' => 10.00, 'duration_seconds' => 3598],
            ['ran_on' => $thisWeek->copy(), 'distance_km' => 8.00, 'duration_seconds' => 2640],
            ['ran_on' => today(), 'distance_km' => 6.50, 'duration_seconds' => 2243],
        ]);
    }
}
