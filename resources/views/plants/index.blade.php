@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Rośliny</h1>
        <a href="{{ route('plants.create') }}" class="btn btn-primary">
            Dodaj roślinę
        </a>
    </div>

    <div class="row g-4">
        @foreach ($plants as $plant)
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 shadow-sm border-0">

                    <div class="ratio ratio-4x3">
                        <img
                            src="{{ Storage::disk('public')->url($plant->photo_path) }}"
                            alt="{{ $plant->name }}"
                            class="img-fluid rounded-top"
                            style="object-fit: cover;"
                        >
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2">
                            {{ $plant->name }}
                        </h5>

                        <div class="mt-auto">
                            <a
                                href="{{ route('plants.show', $plant) }}"
                                class="btn btn-outline-primary w-100"
                            >
                                Szczegóły
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $plants->links() }}
    </div>

</div>
@endsection
