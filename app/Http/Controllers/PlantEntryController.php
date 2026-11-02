<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PlantEntries\DeletePlantEntry;
use App\Actions\PlantEntries\StorePlantCare;
use App\Actions\PlantEntries\StoreSensorReading;
use App\Actions\PlantEntries\UpdatePlantEntry;
use App\Enums\PlantType;
use App\Http\Requests\PlantEntries\StorePlantCareRequest;
use App\Http\Requests\PlantEntries\UpdatePlantEntryRequest;
use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Sensors\SensorClient;
use App\Services\Sensors\SensorClientException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PlantEntryController extends Controller
{
    public function __construct(
        private readonly SensorClient $sensorClient,
    ) {}

    public function index(Plant $plant): View
    {
        $entries = $plant->entries()->latest('recorded_at')->paginate(50);

        return view('entries.index', compact('plant', 'entries'));
    }

    public function edit(PlantEntry $entry): View
    {
        return view('entries.edit', compact('entry'));
    }

    public function update(
        UpdatePlantEntryRequest $request,
        PlantEntry $entry,
        UpdatePlantEntry $updatePlantEntry,
    ): RedirectResponse {
        $updatePlantEntry->execute(
            $entry,
            $request->validated(),
            $request->file('current_photo'),
            $request->boolean('remove_current_photo'),
        );

        return redirect()
            ->route('plants.show', $entry->plant_id)
            ->with('success', 'Wpis zaktualizowany');
    }

    public function destroy(PlantEntry $entry, DeletePlantEntry $deletePlantEntry): RedirectResponse
    {
        $plantId = $entry->plant_id;

        $deletePlantEntry->execute($entry);

        return redirect()
            ->route('plants.show', $plantId)
            ->with('success', 'Wpis został usunięty');
    }

    public function fetchFromApi(
        Plant $plant,
        StoreSensorReading $storeSensorReading,
    ): RedirectResponse {
        if ($plant->plant_type !== PlantType::Sensor) {
            return back()->with(
                'error',
                'Ta roślina nie korzysta z danych z czujnika.',
            );
        }

        try {
            $reading = $this->sensorClient->fetchSoilReading();
        } catch (SensorClientException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $storeSensorReading->execute($plant, $reading);

        return back()->with(
            'success',
            'Dodano nowy wpis z czujnika',
        );
    }

    public function storeWatering(
        StorePlantCareRequest $request,
        Plant $plant,
        StorePlantCare $storePlantCare,
    ): RedirectResponse {
        $storePlantCare->execute($plant, $request->validated());

        return back()->with('success', 'Dodano wpis pielęgnacji');
    }
}
