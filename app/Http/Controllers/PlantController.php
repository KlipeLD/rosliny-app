<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Plants\CreatePlant;
use App\Actions\Plants\DeletePlant;
use App\Actions\Plants\UpdatePlant;
use App\Http\Requests\Plants\StorePlantRequest;
use App\Http\Requests\Plants\UpdatePlantRequest;
use App\Models\Plant;
use App\Queries\PlantDashboardQuery;
use App\Services\Plants\LastWateringService;
use App\Services\Plants\WateringPredictionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PlantController extends Controller
{
    public function index(PlantDashboardQuery $dashboardQuery): View
    {
        return view('plants.index', $dashboardQuery->get());
    }

    public function show(
        Plant $plant,
        WateringPredictionService $predictionService,
        LastWateringService $lastWateringService,
    ): View {
        $entries = $plant->entries()->latest('recorded_at')->paginate(50);
        $latestEntry = $plant->entries()->latest('recorded_at')->first();
        $predictionEntries = $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
        $wateringPrediction = $predictionService->predict($plant, $predictionEntries);
        $copyLastWateringByEntry = $entries->getCollection()
            ->mapWithKeys(fn ($entry) => [
                $entry->id => $lastWateringService->detailsForEntry(
                    $plant,
                    $entry,
                    $predictionEntries,
                    $latestEntry,
                ),
            ]);

        return view('plants.show', compact('plant', 'entries', 'latestEntry', 'wateringPrediction', 'copyLastWateringByEntry'));
    }

    public function create(): View
    {
        return view('plants.create');
    }

    public function store(StorePlantRequest $request, CreatePlant $createPlant): RedirectResponse
    {
        $plant = $createPlant->execute(
            $request->validated(),
            $request->file('photo'),
        );

        return redirect()->route('plants.show', $plant)
            ->with('success', 'Roślina dodana');
    }

    public function edit(Plant $plant): View
    {
        return view('plants.edit', compact('plant'));
    }

    public function update(
        UpdatePlantRequest $request,
        Plant $plant,
        UpdatePlant $updatePlant,
    ): RedirectResponse {
        $updatePlant->execute(
            $plant,
            $request->validated(),
            $request->file('photo'),
            $request->boolean('remove_photo'),
        );

        return redirect()->route('plants.show', $plant)->with('success', 'Zapisano zmiany');
    }

    public function destroy(Plant $plant, DeletePlant $deletePlant): RedirectResponse
    {
        $deletePlant->execute($plant);

        return redirect()->route('plants.index')->with('success', 'Roślina została usunięta');
    }
}
