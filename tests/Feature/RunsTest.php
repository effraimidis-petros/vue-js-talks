<?php

use App\Models\Run;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('runs.index'))->assertRedirect(route('login'));
});

test('the runs page lists only the runner\'s own runs, latest first', function () {
    $user = User::factory()->create();
    Run::factory()->for($user)->create(['ran_on' => '2026-11-01', 'distance_km' => 16, 'duration_seconds' => 5760]);
    Run::factory()->for($user)->create(['ran_on' => '2026-11-09', 'distance_km' => 8, 'duration_seconds' => 2640]);
    Run::factory()->create(['ran_on' => '2026-11-10']);

    $this->actingAs($user)
        ->get(route('runs.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('runs/Index')
            ->has('runs', 2)
            ->where('runs.0.ran_on_label', 'Mon, 9 Nov')
            ->where('runs.0.distance', '8.00 km')
            ->where('runs.0.duration', '44:00')
            ->where('runs.0.pace', '5:30 /km')
            ->where('runs.1.ran_on_label', 'Sun, 1 Nov')
            ->where('runs.1.duration', '1:36:00')
            ->where('runs.1.pace', '6:00 /km'));
});

test('a run without distance shows no pace', function () {
    $user = User::factory()->create();
    Run::factory()->for($user)->create(['distance_km' => 0, 'duration_seconds' => 600]);

    $this->actingAs($user)
        ->get(route('runs.index'))
        ->assertInertia(fn (Assert $page) => $page->where('runs.0.pace', '—'));
});

test('a run can be logged', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('runs.store'), [
            'ran_on' => '2026-11-17',
            'distance_km' => '10.5',
            'duration' => '59:58',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('runs.index'));

    $run = $user->runs()->sole();

    expect($run->ran_on->toDateString())->toBe('2026-11-17')
        ->and($run->distance_km)->toBe('10.50')
        ->and($run->duration_seconds)->toBe(3598);
});

test('the duration is stored in seconds', function (string $duration, int $seconds) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('runs.store'), [
            'ran_on' => '2026-11-17',
            'distance_km' => '10',
            'duration' => $duration,
        ])
        ->assertSessionHasNoErrors();

    expect($user->runs()->sole()->duration_seconds)->toBe($seconds);
})->with([
    'minutes and seconds' => ['44:00', 2640],
    'more than an hour in minutes' => ['75:30', 4530],
    'hours, minutes and seconds' => ['1:05:30', 3930],
]);

test('the duration must be mm:ss or h:mm:ss', function (string $duration) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('runs.create'))
        ->post(route('runs.store'), [
            'ran_on' => '2026-11-17',
            'distance_km' => '10',
            'duration' => $duration,
        ])
        ->assertSessionHasErrors(['duration' => 'Use the format mm:ss or h:mm:ss.'])
        ->assertRedirect(route('runs.create'));

    expect($user->runs()->exists())->toBeFalse();
})->with(['45', '5:5', '1:30:75', 'fast']);

test('date, distance and duration are required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('runs.store'), [])
        ->assertSessionHasErrors(['ran_on', 'distance_km', 'duration']);

    expect($user->runs()->exists())->toBeFalse();
});

describe('the weekly summary', function () {
    beforeEach(function () {
        $this->travelTo('2026-11-18 10:00:00');
        $this->user = User::factory()->create();
    });

    test('it adds up only the runner\'s runs from Monday to Sunday', function (string $locale) {
        app()->setLocale($locale);
        Run::factory()->for($this->user)->create(['ran_on' => '2026-11-15', 'distance_km' => 10]);
        Run::factory()->for($this->user)->create(['ran_on' => '2026-11-16', 'distance_km' => 8]);
        Run::factory()->for($this->user)->create(['ran_on' => '2026-11-22', 'distance_km' => 6.5]);
        Run::factory()->for($this->user)->create(['ran_on' => '2026-11-23', 'distance_km' => 5]);
        Run::factory()->create(['ran_on' => '2026-11-18', 'distance_km' => 20]);

        $this->actingAs($this->user)
            ->get(route('runs.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.distance', '14.50 km')
                ->where('summary.runs', '2 runs')
                ->where('summary.goal', '14.50 / 40 km')
                ->where('summary.progress', 36.25));
    })->with(['en', 'en_US']);

    test('it counts runs and caps the goal bar', function (array $distances, string $distance, string $runs, string $goal, float|int $progress) {
        foreach ($distances as $km) {
            Run::factory()->for($this->user)->create(['ran_on' => '2026-11-17', 'distance_km' => $km]);
        }

        $this->actingAs($this->user)
            ->get(route('runs.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.distance', $distance)
                ->where('summary.runs', $runs)
                ->where('summary.goal', $goal)
                ->where('summary.progress', $progress));
    })->with([
        'an empty week' => [[], '0.00 km', '0 runs', '0.00 / 40 km', 0],
        'a single run' => [[5], '5.00 km', '1 run', '5.00 / 40 km', 12.5],
        'past the goal' => [[25, 20], '45.00 km', '2 runs', '45.00 / 40 km', 100],
    ]);

    test('a run logged today shows up in it', function () {
        $this->actingAs($this->user)
            ->post(route('runs.store'), [
                'ran_on' => '2026-11-18',
                'distance_km' => '10',
                'duration' => '52:30',
            ])
            ->assertRedirect(route('runs.index'));

        $this->actingAs($this->user)
            ->get(route('runs.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.distance', '10.00 km')
                ->where('summary.runs', '1 run'));
    });
});
