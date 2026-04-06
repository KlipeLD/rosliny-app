@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Rośliny</h1>
        <a href="{{ route('plants.create') }}" class="btn btn-primary">
            Dodaj roślinę
        </a>
    </div>

    @if(isset($topWateringPlants) && $topWateringPlants->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Top 6 do podlania</h5>

                <div class="row g-3">
                    @foreach($topWateringPlants as $item)
                        <div class="col-12 col-md-6 col-xl-4">
                            <a href="{{ route('plants.show', $item['plant']) }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="d-flex align-items-start gap-3">
                                            @if($item['plant']->photo_path)
                                                <img
                                                    src="{{ Storage::disk('public')->url($item['plant']->photo_path) }}"
                                                    alt="{{ $item['plant']->name }}"
                                                    class="rounded flex-shrink-0"
                                                    style="width: 56px; height: 56px; object-fit: cover;"
                                                >
                                            @else
                                                <div
                                                    class="rounded d-flex align-items-center justify-content-center text-muted bg-light flex-shrink-0"
                                                    style="width: 56px; height: 56px;"
                                                >
                                                    🌿
                                                </div>
                                            @endif

                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item['plant']->name }}</div>
                                                <div class="small text-muted">
                                                    {{ $item['prediction']['date']->format('Y-m-d H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        <span aria-hidden="true">💧</span>
                                    </div>

                                    <div class="small text-muted mt-2">
                                        {{ $item['prediction']['details'] }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        @foreach ($plants as $plant)
            @php
                $wateringPrediction = $plant->predictedWatering($plant->entries);
                $needsWateringSoon = $wateringPrediction['available']
                    && $wateringPrediction['date']->copy()->startOfDay()->lessThanOrEqualTo(now()->startOfDay());
            @endphp
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 shadow-sm border-0">

                    <div class="ratio ratio-4x4">
                        <img
                            src="{{ Storage::disk('public')->url($plant->photo_path) }}"
                            alt="{{ $plant->name }}"
                            class="img-fluid rounded-top"
                            style="object-fit: cover;"
                        >
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2 d-flex align-items-center justify-content-between gap-2">
                            {{ $plant->name }}
                            @if($needsWateringSoon)
                                <span title="Przewidywane podlewanie dziś lub po terminie" aria-label="Przewidywane podlewanie dziś lub po terminie">💧</span>
                            @endif
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
