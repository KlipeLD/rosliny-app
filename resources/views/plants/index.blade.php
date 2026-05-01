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
                        @php
                            $needsWateringSoon = $item['prediction']['date']->copy()->startOfDay()->lessThanOrEqualTo(now()->startOfDay());
                        @endphp
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
                                                    &#127793;
                                                </div>
                                            @endif

                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item['plant']->name }}</div>
                                                <div class="small text-muted">
                                                    {{ $item['prediction']['date']->format('Y-m-d H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        @if($needsWateringSoon)
                                            <span title="Przewidywane podlewanie dziś lub po terminie" aria-label="Przewidywane podlewanie dziś lub po terminie">&#128167;</span>
                                        @endif
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

    @if(isset($topUnstablePlants) && $topUnstablePlants->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Top 6 niestabilnych roślin</h5>

                <div class="row g-3">
                    @foreach($topUnstablePlants as $item)
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
                                                    &#127793;
                                                </div>
                                            @endif

                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item['plant']->name }}</div>
                                                <div class="small text-muted">
                                                    wynik niestabilności: {{ $item['instability']['score'] }}
                                                    z {{ $item['instability']['period_days'] }} dni
                                                </div>
                                            </div>
                                        </div>
                                        <span title="Roślina często schnie za mocno albo przed terminem" aria-label="Roślina często schnie za mocno albo przed terminem">&#9888;</span>
                                    </div>

                                    <div class="small text-muted mt-2">
                                        {{ $item['instability']['critical_dry_events'] }}/{{ $item['instability']['total_events'] }}
                                        podlewań w czerwonym poziomie
                                        ({{ $item['instability']['critical_dry_percent'] }}%).
                                        @if($item['instability']['early_events'] > 0)
                                            Podlewana przed terminem: {{ $item['instability']['early_events'] }} razy.
                                        @endif
                                    </div>

                                    <div class="small text-muted mt-1">
                                        Ostatni sygnał: {{ $item['instability']['last_event_at']->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(isset($topWaterRetainingPlants) && $topWaterRetainingPlants->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Top 6 roślin trzymających wodę</h5>

                <div class="row g-3">
                    @foreach($topWaterRetainingPlants as $item)
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
                                                    &#127793;
                                                </div>
                                            @endif

                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item['plant']->name }}</div>
                                                <div class="small text-muted">
                                                    +{{ $item['retention']['late_days'] }} dni po terminie
                                                    z {{ $item['retention']['period_days'] }} dni
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="small text-muted mt-2">
                                        Podlana po przewidywanym terminie:
                                        {{ $item['retention']['late_events'] }}/{{ $item['retention']['total_events'] }} razy.
                                        Średnio +{{ number_format($item['retention']['average_late_days'], 1, ',', '') }} dnia.
                                    </div>

                                    <div class="small text-muted mt-1">
                                        Ostatni sygnał: {{ $item['retention']['last_event_at']->format('Y-m-d H:i') }}
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
                <a href="{{ route('plants.show', $plant) }}" class="text-decoration-none d-block h-100">
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
                        <h5 class="card-title mb-2 d-flex align-items-center justify-content-between gap-2 text-dark">
                            {{ $plant->name }}
                            @if($needsWateringSoon)
                                <span title="Przewidywane podlewanie dziś lub po terminie" aria-label="Przewidywane podlewanie dziś lub po terminie">&#128167;</span>
                            @endif
                        </h5>

                        <div class="mt-auto">
                            <span class="btn btn-outline-primary w-100">
                                Szczegóły
                            </span>
                        </div>
                    </div>

                </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $plants->links() }}
    </div>

</div>
@endsection
