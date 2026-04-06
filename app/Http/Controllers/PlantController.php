<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
    public function index()
    {
        $plants = Plant::with([
            'entries' => fn ($query) => $query->orderByRaw('COALESCE(recorded_at, created_at) asc'),
        ])->latest()->paginate(20);

        $topWateringPlants = Plant::with([
            'entries' => fn ($query) => $query->orderByRaw('COALESCE(recorded_at, created_at) asc'),
        ])->get()
            ->map(function (Plant $plant) {
                $prediction = $plant->predictedWatering($plant->entries);

                if (! ($prediction['available'] ?? false)) {
                    return null;
                }

                return [
                    'plant' => $plant,
                    'prediction' => $prediction,
                ];
            })
            ->filter()
            ->sortBy(fn ($item) => $item['prediction']['date']->timestamp)
            ->take(6)
            ->values();

        return view('plants.index', compact('plants', 'topWateringPlants'));
    }

    public function show(Plant $plant)
    {
        $entries = $plant->entries()->latest('recorded_at')->paginate(20);
        $predictionEntries = $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
        $wateringPrediction = $plant->predictedWatering($predictionEntries);

        return view('plants.show', compact('plant', 'entries', 'wateringPrediction'));
    }


     public function create()
    {
        return view('plants.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlant($request);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('plants', 'public');
        }

        $plant = Plant::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'plant_type' => $validated['plant_type'],
            'photo_path' => $path,
            'soil_moisture_min' => $validated['soil_moisture_min'] ?? null,
            'soil_moisture_max' => $validated['soil_moisture_max'] ?? null,
            'soil_moisture_ideal_min' => $validated['soil_moisture_ideal_min'] ?? null,
            'soil_moisture_ideal_max' => $validated['soil_moisture_ideal_max'] ?? null,
            'watering_interval_days' => $validated['watering_interval_days'] ?? null,
        ]);

        return redirect()->route('plants.show', $plant)
            ->with('success', 'Roślina dodana');
    }

    public function edit(Plant $plant)
    {
        return view('plants.edit', compact('plant'));
    }

    public function update(Request $request, Plant $plant)
    {
        $validated = $this->validatePlant($request, true);

        // usuwanie zdjęcia checkboxem
        if ($request->boolean('remove_photo') && $plant->photo_path) {
            Storage::disk('public')->delete($plant->photo_path);
            $plant->photo_path = null;
        }

        // nowe zdjęcie nadpisuje stare
        if ($request->hasFile('photo')) {
            if ($plant->photo_path) {
                Storage::disk('public')->delete($plant->photo_path);
            }
            $plant->photo_path = $request->file('photo')->store('plants', 'public');
        }

        $plant->name = $validated['name'];
        $plant->description = $validated['description'] ?? null;
        $plant->plant_type = $validated['plant_type'];
        $plant->soil_moisture_min = $validated['soil_moisture_min'] ?? null;
        $plant->soil_moisture_max = $validated['soil_moisture_max'] ?? null;
        $plant->soil_moisture_ideal_min = $validated['soil_moisture_ideal_min'] ?? null;
        $plant->soil_moisture_ideal_max = $validated['soil_moisture_ideal_max'] ?? null;
        $plant->watering_interval_days = $validated['watering_interval_days'] ?? null;
        $plant->save();

        return redirect()->route('plants.show', $plant)->with('success', 'Zapisano zmiany');
    }

    public function destroy(Plant $plant)
    {
        if ($plant->photo_path) {
            Storage::disk('public')->delete($plant->photo_path);
        }

        $plant->delete();

        return redirect()->route('plants.index')->with('success', 'Roślina została usunięta');
    }

    private function validatePlant(Request $request, bool $isUpdate = false): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plant_type' => ['required', 'in:sensor,manual'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => [$isUpdate ? 'nullable' : 'sometimes', 'boolean'],
            'soil_moisture_min' => ['nullable', 'numeric', 'between:0,100'],
            'soil_moisture_max' => ['nullable', 'numeric', 'between:0,100', 'gte:soil_moisture_min'],
            'soil_moisture_ideal_min' => ['nullable', 'numeric', 'between:0,100'],
            'soil_moisture_ideal_max' => ['nullable', 'numeric', 'between:0,100', 'gte:soil_moisture_ideal_min'],
            'watering_interval_days' => ['nullable', 'integer', 'between:1,365'],
        ]);

        $hasAnyMoistureRange = collect([
            'soil_moisture_min',
            'soil_moisture_max',
            'soil_moisture_ideal_min',
            'soil_moisture_ideal_max',
        ])->contains(fn ($field) => $request->filled($field));

        if ($hasAnyMoistureRange) {
            $request->validate([
                'soil_moisture_min' => ['required'],
                'soil_moisture_max' => ['required'],
                'soil_moisture_ideal_min' => ['required'],
                'soil_moisture_ideal_max' => ['required'],
            ], [
                'soil_moisture_min.required' => 'Uzupełnij pełny zakres wilgotności gleby.',
                'soil_moisture_max.required' => 'Uzupełnij pełny zakres wilgotności gleby.',
                'soil_moisture_ideal_min.required' => 'Uzupełnij pełny zakres idealnej wilgotności gleby.',
                'soil_moisture_ideal_max.required' => 'Uzupełnij pełny zakres idealnej wilgotności gleby.',
            ]);

            if (
                (float) $validated['soil_moisture_ideal_min'] < (float) $validated['soil_moisture_min'] ||
                (float) $validated['soil_moisture_ideal_max'] > (float) $validated['soil_moisture_max']
            ) {
                throw ValidationException::withMessages([
                    'soil_moisture_ideal_min' => 'Zakres idealny musi mieścić się w zakresie dopuszczalnym.',
                ]);
            }
        }

        return $validated;
    }
}
