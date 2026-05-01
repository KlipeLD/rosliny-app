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
        ])->latest()->paginate(50);

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

        $topUnstablePlants = Plant::with([
            'entries' => fn ($query) => $query->orderByRaw('COALESCE(recorded_at, created_at) asc'),
        ])->get()
            ->map(function (Plant $plant) {
                $instability = $plant->wateringInstability($plant->entries);

                if (! ($instability['available'] ?? false)) {
                    return null;
                }

                return [
                    'plant' => $plant,
                    'instability' => $instability,
                ];
            })
            ->filter()
            ->sortByDesc(fn ($item) => $item['instability']['score'])
            ->take(6)
            ->values();

        $topWaterRetainingPlants = Plant::with([
            'entries' => fn ($query) => $query->orderByRaw('COALESCE(recorded_at, created_at) asc'),
        ])->get()
            ->map(function (Plant $plant) {
                $retention = $plant->waterRetention($plant->entries);

                if (! ($retention['available'] ?? false)) {
                    return null;
                }

                return [
                    'plant' => $plant,
                    'retention' => $retention,
                ];
            })
            ->filter()
            ->sortByDesc(fn ($item) => $item['retention']['score'])
            ->take(6)
            ->values();

        return view('plants.index', compact('plants', 'topWateringPlants', 'topUnstablePlants', 'topWaterRetainingPlants'));
    }

    public function show(Plant $plant)
    {
        $entries = $plant->entries()->latest('recorded_at')->paginate(50);
        $latestEntry = $plant->entries()->latest('recorded_at')->first();
        $predictionEntries = $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
        $wateringPrediction = $plant->predictedWatering($predictionEntries);
        $copyLastWateringByEntry = $entries->getCollection()
            ->mapWithKeys(fn ($entry) => [
                $entry->id => $plant->copyLastWateringDetailsForEntry($entry, $predictionEntries, $latestEntry),
            ]);

        return view('plants.show', compact('plant', 'entries', 'latestEntry', 'wateringPrediction', 'copyLastWateringByEntry'));
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
            ...$this->validatedParameterRanges($validated),
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
        foreach ($this->parameterRangeFields() as $field) {
            $plant->{$field} = $validated[$field] ?? null;
        }
        $plant->save();

        return redirect()->route('plants.show', $plant)->with('success', 'Zapisano zmiany');
    }

    public function destroy(Plant $plant)
    {
        if ($plant->photo_path) {
            Storage::disk('public')->delete($plant->photo_path);
        }

        $plant->entries()
            ->whereNotNull('current_photo_path')
            ->pluck('current_photo_path')
            ->each(fn ($path) => Storage::disk('public')->delete($path));

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
            'temp_min' => ['nullable', 'numeric', 'between:-50,80'],
            'temp_max' => ['nullable', 'numeric', 'between:-50,80', 'gte:temp_min'],
            'temp_ideal_min' => ['nullable', 'numeric', 'between:-50,80'],
            'temp_ideal_max' => ['nullable', 'numeric', 'between:-50,80', 'gte:temp_ideal_min'],
            'ph_min' => ['nullable', 'numeric', 'between:0,14'],
            'ph_max' => ['nullable', 'numeric', 'between:0,14', 'gte:ph_min'],
            'ph_ideal_min' => ['nullable', 'numeric', 'between:0,14'],
            'ph_ideal_max' => ['nullable', 'numeric', 'between:0,14', 'gte:ph_ideal_min'],
            'ec_min' => ['nullable', 'numeric', 'min:0'],
            'ec_max' => ['nullable', 'numeric', 'min:0', 'gte:ec_min'],
            'ec_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'ec_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:ec_ideal_min'],
            'n_min' => ['nullable', 'numeric', 'min:0'],
            'n_max' => ['nullable', 'numeric', 'min:0', 'gte:n_min'],
            'n_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'n_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:n_ideal_min'],
            'p_min' => ['nullable', 'numeric', 'min:0'],
            'p_max' => ['nullable', 'numeric', 'min:0', 'gte:p_min'],
            'p_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'p_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:p_ideal_min'],
            'k_min' => ['nullable', 'numeric', 'min:0'],
            'k_max' => ['nullable', 'numeric', 'min:0', 'gte:k_min'],
            'k_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'k_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:k_ideal_min'],
            'salt_min' => ['nullable', 'numeric', 'min:0'],
            'salt_max' => ['nullable', 'numeric', 'min:0', 'gte:salt_min'],
            'salt_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'salt_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:salt_ideal_min'],
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

        foreach ($this->parameterRangePairs() as $label => [$minField, $maxField]) {
            if ($request->filled($minField) xor $request->filled($maxField)) {
                throw ValidationException::withMessages([
                    $request->filled($minField) ? $maxField : $minField => 'Uzupełnij pełny zakres idealny dla pola: '.$label.'.',
                ]);
            }
        }

        foreach ($this->parameterRangeDefinitions() as $range) {
            [$minField, $maxField] = $range['acceptable'];
            [$idealMinField, $idealMaxField] = $range['ideal'];

            if ($request->filled($minField) xor $request->filled($maxField)) {
                throw ValidationException::withMessages([
                    $request->filled($minField) ? $maxField : $minField => 'Uzupełnij pełny zakres dopuszczalny dla pola: '.$range['label'].'.',
                ]);
            }

            if (
                $request->filled($minField) &&
                $request->filled($maxField) &&
                $request->filled($idealMinField) &&
                $request->filled($idealMaxField) &&
                (
                    (float) $validated[$idealMinField] < (float) $validated[$minField] ||
                    (float) $validated[$idealMaxField] > (float) $validated[$maxField]
                )
            ) {
                throw ValidationException::withMessages([
                    $idealMinField => 'Zakres idealny musi mieścić się w zakresie dopuszczalnym dla pola: '.$range['label'].'.',
                ]);
            }
        }

        return $validated;
    }

    private function parameterRangePairs(): array
    {
        return [
            'temperatura' => ['temp_ideal_min', 'temp_ideal_max'],
            'pH' => ['ph_ideal_min', 'ph_ideal_max'],
            'EC' => ['ec_ideal_min', 'ec_ideal_max'],
            'azot' => ['n_ideal_min', 'n_ideal_max'],
            'fosfor' => ['p_ideal_min', 'p_ideal_max'],
            'potas' => ['k_ideal_min', 'k_ideal_max'],
            'zasolenie' => ['salt_ideal_min', 'salt_ideal_max'],
        ];
    }

    private function parameterRangeDefinitions(): array
    {
        return [
            ['label' => 'temperatura', 'acceptable' => ['temp_min', 'temp_max'], 'ideal' => ['temp_ideal_min', 'temp_ideal_max']],
            ['label' => 'pH', 'acceptable' => ['ph_min', 'ph_max'], 'ideal' => ['ph_ideal_min', 'ph_ideal_max']],
            ['label' => 'EC', 'acceptable' => ['ec_min', 'ec_max'], 'ideal' => ['ec_ideal_min', 'ec_ideal_max']],
            ['label' => 'azot', 'acceptable' => ['n_min', 'n_max'], 'ideal' => ['n_ideal_min', 'n_ideal_max']],
            ['label' => 'fosfor', 'acceptable' => ['p_min', 'p_max'], 'ideal' => ['p_ideal_min', 'p_ideal_max']],
            ['label' => 'potas', 'acceptable' => ['k_min', 'k_max'], 'ideal' => ['k_ideal_min', 'k_ideal_max']],
            ['label' => 'zasolenie', 'acceptable' => ['salt_min', 'salt_max'], 'ideal' => ['salt_ideal_min', 'salt_ideal_max']],
        ];
    }

    private function parameterRangeFields(): array
    {
        return collect($this->parameterRangeDefinitions())
            ->flatMap(fn ($range) => [
                ...$range['acceptable'],
                ...$range['ideal'],
            ])
            ->values()
            ->all();
    }

    private function validatedParameterRanges(array $validated): array
    {
        return collect($this->parameterRangeFields())
            ->mapWithKeys(fn ($field) => [$field => $validated[$field] ?? null])
            ->all();
    }
}
