@extends('layouts.app')

@section('title', 'Edytuj roślinę')

@section('content')
<div class="container mt-4" style="max-width: 600px;">

    <h1 class="mb-4">Edytuj roślinę</h1>

    <form method="POST" action="{{ route('plants.update', $plant) }}" enctype="multipart/form-data" class="card p-4">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label class="form-label">Nazwa</label>
            <input type="text" name="name" value="{{ old('name', $plant->name) }}" class="form-control @error('name') is-invalid @enderror">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Typ rośliny</label>
            <select name="plant_type" class="form-select @error('plant_type') is-invalid @enderror">
                <option value="sensor" @selected(old('plant_type', $plant->plant_type ?? 'sensor') === 'sensor')>Z czujnikiem</option>
                <option value="manual" @selected(old('plant_type', $plant->plant_type) === 'manual')>Tylko podlewanie</option>
            </select>
            @error('plant_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $plant->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="form-label d-block">Wilgotność gleby (%)</label>
            <div class="small text-muted mb-2">
                Zielony kolor oznacza zakres idealny, żółty mieści się w dopuszczalnym, ale poza ideałem.
            </div>

            <div class="row g-2 mb-2">
                <div class="col-sm-6">
                    <label class="form-label">Dopuszczalne od</label>
                    <input type="number" step="0.01" min="0" max="100" name="soil_moisture_min" value="{{ old('soil_moisture_min', $plant->soil_moisture_min) }}" class="form-control @error('soil_moisture_min') is-invalid @enderror">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Dopuszczalne do</label>
                    <input type="number" step="0.01" min="0" max="100" name="soil_moisture_max" value="{{ old('soil_moisture_max', $plant->soil_moisture_max) }}" class="form-control @error('soil_moisture_max') is-invalid @enderror">
                </div>
            </div>

            <div class="row g-2">
                <div class="col-sm-6">
                    <label class="form-label">Idealne od</label>
                    <input type="number" step="0.01" min="0" max="100" name="soil_moisture_ideal_min" value="{{ old('soil_moisture_ideal_min', $plant->soil_moisture_ideal_min) }}" class="form-control @error('soil_moisture_ideal_min') is-invalid @enderror">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Idealne do</label>
                    <input type="number" step="0.01" min="0" max="100" name="soil_moisture_ideal_max" value="{{ old('soil_moisture_ideal_max', $plant->soil_moisture_ideal_max) }}" class="form-control @error('soil_moisture_ideal_max') is-invalid @enderror">
                </div>
            </div>

            @error('soil_moisture_min')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('soil_moisture_max')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('soil_moisture_ideal_min')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('soil_moisture_ideal_max')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        @include('plants._parameter_ranges')

        <div class="mb-4">
            <label class="form-label">Podlewanie co ile dni</label>
            <input type="number" min="1" max="365" step="1" name="watering_interval_days" value="{{ old('watering_interval_days', $plant->watering_interval_days) }}" class="form-control @error('watering_interval_days') is-invalid @enderror">
            <div class="small text-muted mt-1">
                Gdy pole jest puste, termin podlewania będzie liczony z historii rośliny.
            </div>
            @error('watering_interval_days')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if($plant->photo_path)
            <div class="mb-3">
                <div class="mb-2">Aktualne zdjęcie:</div>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($plant->photo_path) }}" alt="{{ $plant->name }}" class="img-fluid rounded mb-2" style="max-height: 220px; object-fit: cover;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo">
                    <label class="form-check-label" for="remove_photo">Usuń zdjęcie</label>
                </div>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Nowe zdjęcie</label>
            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror">
            @error('photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="{{ route('plants.show', $plant) }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>

</div>
@endsection
