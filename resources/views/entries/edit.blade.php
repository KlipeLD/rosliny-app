@extends('layouts.app')

@section('title', 'Edytuj wpis')

@section('content')
<div class="container mt-4" style="max-width: 700px;">

    <h1 class="mb-3">Edytuj wpis</h1>

    <div class="mb-3">
        <a href="{{ route('plants.show', $entry->plant_id) }}" class="btn btn-outline-secondary">Wróć</a>
    </div>

    <form method="POST" action="{{ route('entries.update', $entry) }}" class="card p-4" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label class="form-label">Notatka</label>
            <textarea name="note" rows="4" class="form-control">{{ old('note', $entry->note) }}</textarea>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Temp (°C)</label>
                <input type="text" name="temp_c" class="form-control" value="{{ old('temp_c', $entry->temp_c) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Wilg (%)</label>
                <input type="text" name="moist_pct" class="form-control" value="{{ old('moist_pct', $entry->moist_pct) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">pH</label>
                <input type="text" name="ph" class="form-control" value="{{ old('ph', $entry->ph) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">EC</label>
                <input type="text" name="ec_uscm" class="form-control" value="{{ old('ec_uscm', $entry->ec_uscm) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">N (Azot)</label>
                <input type="text" name="n_mgkg" class="form-control" value="{{ old('n_mgkg', $entry->n_mgkg) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">P (Fosfor)</label>
                <input type="text" name="p_mgkg" class="form-control" value="{{ old('p_mgkg', $entry->p_mgkg) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">K (Potas)</label>
                <input type="text" name="k_mgkg" class="form-control" value="{{ old('k_mgkg', $entry->k_mgkg) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sól</label>
                <input type="text" name="salt_mgl" class="form-control" value="{{ old('salt_mgl', $entry->salt_mgl) }}">
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label">Zdjęcie stanu aktualnego</label>
            @if($entry->current_photo_path)
                <div class="mb-2">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($entry->current_photo_path) }}" alt="Zdjęcie stanu aktualnego" class="img-fluid rounded" style="max-height: 260px; object-fit: cover;">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="remove_current_photo" value="1" class="form-check-input" id="remove_current_photo">
                    <label class="form-check-label" for="remove_current_photo">Usuń obecne zdjęcie</label>
                </div>
            @endif
            <input type="file" name="current_photo" accept="image/*" class="form-control @error('current_photo') is-invalid @enderror">
            @error('current_photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="{{ route('plants.show', $entry->plant_id) }}" class="btn btn-outline-secondary">Anuluj</a>
        </div>
    </form>
</div>
@endsection
