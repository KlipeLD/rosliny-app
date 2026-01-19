@extends('layouts.app')

@section('title', 'Dodaj roślinę')

@section('content')
<div class="container mt-4" style="max-width: 600px;">

    <h1 class="mb-4">Dodaj roślinę</h1>

    <form method="POST" action="{{ route('plants.store') }}" enctype="multipart/form-data" class="card p-4">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nazwa</label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
            >
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea
                name="description"
                rows="4"
                class="form-control"
            >{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Zdjęcie</label>
            <input
                type="file"
                name="photo"
                class="form-control @error('photo') is-invalid @enderror"
            >
            @error('photo')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Zapisz
            </button>

            <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary">
                Anuluj
            </a>
        </div>
    </form>

</div>
@endsection
