<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRunRequest;
use App\Http\Resources\RunResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RunController extends Controller
{
    /**
     * Show the runner's runs, latest first.
     */
    public function index(Request $request): Response
    {
        $runs = $request->user()->runs()->latest('ran_on')->latest('id')->get();

        return Inertia::render('runs/Index', [
            'runs' => RunResource::collection($runs)->resolve(),
        ]);
    }

    /**
     * Show the form to log a run.
     */
    public function create(): Response
    {
        return Inertia::render('runs/Create', [
            'today' => today()->toDateString(),
        ]);
    }

    /**
     * Store a newly logged run.
     */
    public function store(StoreRunRequest $request): RedirectResponse
    {
        $request->user()->runs()->create([
            'ran_on' => $request->validated('ran_on'),
            'distance_km' => $request->validated('distance_km'),
            'duration_seconds' => $request->durationInSeconds(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Run logged.')]);

        return to_route('runs.index');
    }
}
