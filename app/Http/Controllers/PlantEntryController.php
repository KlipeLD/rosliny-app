<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Plant;
use App\Models\PlantEntry;

class PlantEntryController extends Controller
{
    public function index(Plant $plant)
    {
        $entries = $plant->entries()->latest('recorded_at')->paginate(50);
        return view('entries.index', compact('plant', 'entries'));
    }


    public function edit(PlantEntry $entry)
    {
        return view('entries.edit', compact('entry'));
    }

    public function update(Request $request, PlantEntry $entry)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
            'temp_c' => ['nullable', 'numeric'],
            'moist_pct' => ['nullable', 'numeric'],
            'ec_uscm' => ['nullable', 'integer'],
            'ph' => ['nullable', 'numeric'],
            'n_mgkg' => ['nullable', 'integer'],
            'p_mgkg' => ['nullable', 'integer'],
            'k_mgkg' => ['nullable', 'integer'],
            'salt_mgl' => ['nullable', 'integer'],
        ]);

        $entry->update($validated);

        return redirect()
            ->route('plants.show', $entry->plant_id)
            ->with('success', 'Wpis zaktualizowany');
    }

    public function destroy(PlantEntry $entry)
    {
        $plantId = $entry->plant_id;

        $entry->delete();

        return redirect()
            ->route('plants.show', $plantId)
            ->with('success', 'Wpis został usunięty');
    }

    public function fetchFromApi(Plant $plant)
    {
        $response = Http::timeout(5)
            ->acceptJson()
            ->get(rtrim(config('services.plant_api.base_url'), '/').'/soil');

        if (!$response->successful()) {
            return back()->with('error', 'Błąd pobierania danych z czujnika (HTTP '.$response->status().')');
        }

        $data = $response->json();

        if (!is_array($data) || !($data['ok'] ?? false)) {
            return back()->with('error', 'Czujnik zwrócił niepoprawne dane');
        }

        $plant->entries()->create([
            'source' => 'soil',
            'payload' => $data,
            'recorded_at' => now(),
            'ts_ms' => $data['ts_ms'] ?? null,
            'temp_c' => $data['temp_c'] ?? null,
            'moist_pct' => $data['moist_pct'] ?? null,
            'ec_uscm' => $data['ec_uscm'] ?? null,
            'ph' => $data['ph'] ?? null,
            'n_mgkg' => $data['n_mgkg'] ?? null,
            'p_mgkg' => $data['p_mgkg'] ?? null,
            'k_mgkg' => $data['k_mgkg'] ?? null,
            'salt_mgl' => $data['salt_mgl'] ?? null,
        ]);

        return back()->with('success', 'Dodano nowy wpis z czujnika');
    }
}
