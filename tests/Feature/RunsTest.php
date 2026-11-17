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
    'one second' => ['0:01', 1],
]);

test('a run can be logged for today', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('runs.store'), [
            'ran_on' => today()->toDateString(),
            'distance_km' => '10',
            'duration' => '55:00',
        ])
        ->assertSessionHasNoErrors();

    expect($user->runs()->sole()->ran_on->isToday())->toBeTrue();
});

test('a run that is not a real run is rejected', function (array $input, string $field, string $message) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('runs.create'))
        ->post(route('runs.store'), [
            'ran_on' => '2026-11-17',
            'distance_km' => '10',
            'duration' => '55:00',
            ...$input,
        ])
        ->assertSessionHasErrors([$field => $message])
        ->assertSessionDoesntHaveErrors(array_diff(['ran_on', 'distance_km', 'duration'], [$field]))
        ->assertRedirect(route('runs.create'));

    expect($user->runs()->exists())->toBeFalse();
})->with([
    'zero distance' => [['distance_km' => '0'], 'distance_km', 'Distance must be greater than 0 km.'],
    'negative distance' => [['distance_km' => '-5'], 'distance_km', 'Distance must be greater than 0 km.'],
    'zero minutes and seconds' => [['duration' => '0:00'], 'duration', 'Duration must be longer than 0:00.'],
    'zero padded minutes' => [['duration' => '00:00'], 'duration', 'Duration must be longer than 0:00.'],
    'zero hours, minutes and seconds' => [['duration' => '0:00:00'], 'duration', 'Duration must be longer than 0:00.'],
    'tomorrow' => [fn () => ['ran_on' => today()->addDay()->toDateString()], 'ran_on', "The date can't be in the future."],
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
