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
            <input
                type="text"
                name="name"
                value="{{ old('name', $plant->name) }}"
                class="form-control @error('name') is-invalid @enderror"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $plant->description) }}</textarea>
        </div>

        @if($plant->photo_path)
            <div class="mb-3">
                <div class="mb-2">Aktualne zdjęcie:</div>
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($plant->photo_path) }}"
                    alt="{{ $plant->name }}"
                    class="img-fluid rounded mb-2"
                    style="max-height: 220px; object-fit: cover;"
                >
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
