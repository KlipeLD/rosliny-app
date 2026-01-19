<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
    public function index()
    {
        $plants = Plant::latest()->paginate(20);
        return view('plants.index', compact('plants'));
    }

    public function show(Plant $plant)
    {
        $entries = $plant->entries()->latest('recorded_at')->paginate(20);
        return view('plants.show', compact('plant', 'entries'));
    }


     public function create()
    {
        return view('plants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'photo' => ['nullable','image','max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('plants', 'public');
        }

        $plant = Plant::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'photo_path' => $path,
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
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'photo' => ['nullable','image','max:2048'],
            'remove_photo' => ['nullable','boolean'],
        ]);

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
        $plant->save();

        return redirect()->route('plants.show', $plant)->with('success', 'Zapisano zmiany');
    }

    
}
