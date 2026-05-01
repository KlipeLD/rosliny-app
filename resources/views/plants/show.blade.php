@extends('layouts.app')

@section('title', $plant->name)

@section('content')
<div class="container mt-4" style="max-width: 900px;">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="mb-2">{{ $plant->name }}</h1>
            @if($plant->description)
                <p class="mb-0">{{ $plant->description }}</p>
            @endif
            <div class="small text-muted mt-1">
                Typ: {{ $plant->plant_type === 'manual' ? 'Tylko podlewanie' : 'Z czujnikiem' }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('plants.edit', $plant) }}" class="btn btn-outline-primary">Edytuj</a>
            <form method="POST" action="{{ route('plants.destroy', $plant) }}" onsubmit="return confirm('Usunąć tę roślinę?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Usuń</button>
            </form>
            <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary">Wróć</a>
        </div>
    </div>

    @if($plant->photo_path)
        <div class="mb-4">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($plant->photo_path) }}" alt="{{ $plant->name }}" class="img-fluid rounded" style="max-height: 420px; object-fit: cover;">
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="mb-2">Zakres wilgotności gleby</h5>
                <div class="small text-muted">
                    Dopuszczalny: {{ $plant->soil_moisture_min ?? 10 }}-{{ $plant->soil_moisture_max ?? 80 }}%
                    |
                    Idealny: {{ $plant->soil_moisture_ideal_min ?? 20 }}-{{ $plant->soil_moisture_ideal_max ?? 60 }}%
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="mb-2">Przewidywana data podlewania</h5>
                @if($wateringPrediction['available'])
                    <div class="fs-5 fw-semibold mb-1">{{ $wateringPrediction['date']->format('Y-m-d H:i') }}</div>
                    <div class="small text-muted mb-1">{{ $wateringPrediction['details'] }}</div>
                    <div class="small text-muted">
                        Ostatnio podlano: {{ $wateringPrediction['last_watering_at']->format('Y-m-d H:i') }}
                        @if(($wateringPrediction['mode'] ?? null) === 'estimated' && !empty($wateringPrediction['samples']))
                            | próbki: {{ $wateringPrediction['samples'] }}
                        @endif
                    </div>
                @else
                    <div class="text-muted">Brak wystarczających danych</div>
                @endif
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <h5 class="mb-3">Historia wpisów</h5>

        @if($plant->plant_type === 'sensor')
            <form method="POST" action="{{ route('plants.entries.fetch', $plant) }}" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-primary">Pobierz dane z API</button>
            </form>
        @endif

        @php
            $selectedCareActions = session()->hasOldInput()
                ? old('actions', [])
                : ['watering'];
        @endphp

        <form method="POST" action="{{ route('plants.entries.watering', $plant) }}" class="card card-body mb-3">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data wpisu</label>
                    <input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}" class="form-control @error('recorded_at') is-invalid @enderror">
                    @error('recorded_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Czynności</label>
                    <div class="@error('actions') is-invalid @enderror">
                        <div class="form-check">
                            <input type="checkbox" name="actions[]" value="watering" class="form-check-input" id="care-watering" @checked(in_array('watering', $selectedCareActions, true))>
                            <label class="form-check-label" for="care-watering">Podlanie</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="actions[]" value="fertilizing" class="form-check-input" id="care-fertilizing" @checked(in_array('fertilizing', $selectedCareActions, true))>
                            <label class="form-check-label" for="care-fertilizing">Nawożenie</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="actions[]" value="repotting" class="form-check-input" id="care-repotting" @checked(in_array('repotting', $selectedCareActions, true))>
                            <label class="form-check-label" for="care-repotting">Przesadzenie do nowej ziemi</label>
                        </div>
                    </div>
                    @error('actions')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('actions.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Notatka</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="form-control @error('note') is-invalid @enderror">
                    @error('note')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Dodaj</button>
                </div>
            </div>
        </form>

        @if($entries->count() === 0)
            <div class="text-muted">Brak wpisów.</div>
        @else
            @foreach($entries as $entry)
                @if(in_array($entry->source, ['watering', 'fertilizing', 'repotting'], true))
                    @php
                        $careLabels = [
                            'watering' => 'Podlewanie',
                            'fertilizing' => 'Nawożenie',
                            'repotting' => 'Przesadzenie do nowej ziemi',
                        ];
                    @endphp
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-semibold">{{ $careLabels[$entry->source] ?? $entry->source }}</div>
                                    <div class="text-muted small">{{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }}</div>
                                </div>

                                <form method="POST" action="{{ route('entries.destroy', $entry) }}" onsubmit="return confirm('Usunąć ten wpis?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                                </form>
                            </div>

                            @if($entry->note)
                                <div class="text-muted">{{ $entry->note }}</div>
                            @endif

                            @if($entry->current_photo_path)
                                <div class="mt-3">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($entry->current_photo_path) }}" alt="Zdjęcie stanu aktualnego" class="img-fluid rounded" style="max-height: 320px; object-fit: cover;">
                                </div>
                            @endif

                            @if($latestEntry && $entry->is($latestEntry))
                                <form method="POST" action="{{ route('entries.update', $entry) }}" enctype="multipart/form-data" class="mt-3 border-top pt-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label">Zdjęcie stanu aktualnego</label>
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md">
                                            <input type="file" name="current_photo" accept="image/*" class="form-control @error('current_photo') is-invalid @enderror">
                                            @error('current_photo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">Dodaj zdjęcie</button>
                                        </div>
                                    </div>
                                    @if($entry->current_photo_path)
                                        <div class="form-check mt-2">
                                            <input type="checkbox" name="remove_current_photo" value="1" class="form-check-input" id="remove-current-photo-{{ $entry->id }}">
                                            <label class="form-check-label" for="remove-current-photo-{{ $entry->id }}">Usuń obecne zdjęcie przy zapisie</label>
                                        </div>
                                    @endif
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    @php
                        $tempRanges = $plant->temperatureRanges();
                        $moistureRanges = $plant->moistureRanges();
                        $phRanges = $plant->phRanges();
                        $ecRanges = $plant->ecRanges();
                        $nitrogenRanges = $plant->nitrogenRanges();
                        $phosphorusRanges = $plant->phosphorusRanges();
                        $potassiumRanges = $plant->potassiumRanges();
                        $saltRanges = $plant->saltRanges();
                        $temp = param_status($entry->temp_c, $tempRanges);
                        $moist = param_status($entry->moist_pct, $moistureRanges);
                        $ph = param_status($entry->ph, $phRanges);
                        $ec = param_status($entry->ec_uscm, $ecRanges);
                        $n = param_status($entry->n_mgkg, $nitrogenRanges);
                        $p = param_status($entry->p_mgkg, $phosphorusRanges);
                        $k = param_status($entry->k_mgkg, $potassiumRanges);
                        $salt = param_status($entry->salt_mgl, $saltRanges);
                        $tempHintIcon = null;
                        $tempHintLabel = null;
                        $phHintIcon = null;
                        $phHintLabel = null;
                        $ecHintIcon = null;
                        $ecHintLabel = null;
                        $saltHintIcon = null;
                        $saltHintLabel = null;
                        $nitrogenHintIcon = null;
                        $nitrogenHintLabel = null;
                        $phosphorusHintIcon = null;
                        $phosphorusHintLabel = null;
                        $potassiumHintIcon = null;
                        $potassiumHintLabel = null;

                        if ($entry->temp_c !== null) {
                            if ((float) $entry->temp_c > (float) $tempRanges[1]) {
                                $tempHintIcon = '&#10052;';
                                $tempHintLabel = 'Temperatura za wysoka';
                            } elseif ((float) $entry->temp_c < (float) $tempRanges[0]) {
                                $tempHintIcon = '&#9728;&#65039;';
                                $tempHintLabel = 'Temperatura za niska';
                            }
                        }

                        if ($entry->ph !== null) {
                            if ((float) $entry->ph > (float) $phRanges[1]) {
                                $phHintIcon = '&#127819;';
                                $phHintLabel = 'pH za wysokie';
                            } elseif ((float) $entry->ph < (float) $phRanges[0]) {
                                $phHintIcon = '🚿';
                                $phHintLabel = 'pH za niskie - dolomit';
                            }
                        }

                        if ($entry->ec_uscm !== null) {
                            if ((float) $entry->ec_uscm > (float) $ecRanges[1]) {
                                $ecHintIcon = '&#128705;';
                                $ecHintLabel = 'EC za wysokie';
                            } elseif ((float) $entry->ec_uscm < (float) $ecRanges[0]) {
                                $ecHintIcon = '&#129514;';
                                $ecHintLabel = 'EC za niskie';
                            }
                        }

                        if ($entry->salt_mgl !== null) {
                            if ((float) $entry->salt_mgl > (float) $saltRanges[1]) {
                                $saltHintIcon = '&#128705;';
                                $saltHintLabel = 'Zasolenie za wysokie';
                            } elseif ((float) $entry->salt_mgl < (float) $saltRanges[0]) {
                                $saltHintIcon = '&#129514;';
                                $saltHintLabel = 'Zasolenie za niskie';
                            }
                        }

                        if ($entry->n_mgkg !== null) {
                            if ((float) $entry->n_mgkg > (float) $nitrogenRanges[1]) {
                                $nitrogenHintIcon = '&#128705;';
                                $nitrogenHintLabel = 'Azot za wysoki';
                            } elseif ((float) $entry->n_mgkg < (float) $nitrogenRanges[0]) {
                                $nitrogenHintIcon = '&#129514;';
                                $nitrogenHintLabel = 'Azot za niski';
                            }
                        }

                        if ($entry->p_mgkg !== null) {
                            if ((float) $entry->p_mgkg > (float) $phosphorusRanges[1]) {
                                $phosphorusHintIcon = '&#128705;';
                                $phosphorusHintLabel = 'Fosfor za wysoki';
                            } elseif ((float) $entry->p_mgkg < (float) $phosphorusRanges[0]) {
                                $phosphorusHintIcon = '&#129514;';
                                $phosphorusHintLabel = 'Fosfor za niski';
                            }
                        }

                        if ($entry->k_mgkg !== null) {
                            if ((float) $entry->k_mgkg > (float) $potassiumRanges[1]) {
                                $potassiumHintIcon = '&#128705;';
                                $potassiumHintLabel = 'Potas za wysoki';
                            } elseif ((float) $entry->k_mgkg < (float) $potassiumRanges[0]) {
                                $potassiumHintIcon = '&#129514;';
                                $potassiumHintLabel = 'Potas za niski';
                            }
                        }
                        $soilMin = $plant->soil_moisture_min ?? 10;
                        $soilMax = $plant->soil_moisture_max ?? 80;
                        $soilIdealMin = $plant->soil_moisture_ideal_min ?? 20;
                        $soilIdealMax = $plant->soil_moisture_ideal_max ?? 60;
                        $moistValue = $entry->moist_pct;
                        $moistMarker = $moistValue !== null ? max(0, min(100, (float) $moistValue)) : null;
                        $moistHint = null;

                        if ($moistValue !== null) {
                            if ((float) $moistValue < (float) $soilIdealMin) {
                                $moistHint = '💧';
                            } elseif ((float) $moistValue > (float) $soilMax) {
                                $moistHint = '☀️';
                            }
                        }
                    @endphp

                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            @php
                                $copyLastWatering = $copyLastWateringByEntry[$entry->id] ?? null;
                            @endphp

                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-muted small">
                                    {{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }} · {{ $entry->source }}
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary js-copy-entry"
                                            data-entry-id="{{ $entry->id }}">
                                        Kopiuj
                                    </button>
                                    <a href="{{ route('entries.edit', $entry) }}" class="btn btn-sm btn-outline-secondary">Edytuj</a>
                                    <form method="POST" action="{{ route('entries.destroy', $entry) }}" onsubmit="return confirm('Usunąć ten wpis?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                                    </form>
                                </div>
                            </div>

                            <div class="soil-moisture-card soil-moisture-card--{{ $moist['class'] }} mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <div class="soil-moisture-card__label">Wilgotność gleby</div>
                                        <div class="soil-moisture-card__value">
                                            @if($moistValue !== null)
                                                {{ number_format($moistValue, 1, ',', '') }} %
                                                @if($moistHint)
                                                    <span class="soil-moisture-card__hint" aria-hidden="true">{{ $moistHint }}</span>
                                                @endif
                                            @else
                                                brak danych
                                            @endif
                                        </div>
                                    </div>
                                    <div class="soil-moisture-card__status">{{ $moist['label'] }}</div>
                                </div>

                                <div class="soil-moisture-scale">
                                    <div class="soil-moisture-scale__base"></div>
                                    <div class="soil-moisture-scale__range soil-moisture-scale__range--warn" style="left: {{ $soilMin }}%; width: {{ max(0, $soilMax - $soilMin) }}%;"></div>
                                    <div class="soil-moisture-scale__range soil-moisture-scale__range--ideal" style="left: {{ $soilIdealMin }}%; width: {{ max(0, $soilIdealMax - $soilIdealMin) }}%;"></div>
                                    @if($moistMarker !== null)
                                        <div class="soil-moisture-scale__marker" style="left: {{ $moistMarker }}%;">
                                            <span class="soil-moisture-scale__marker-dot"></span>
                                        </div>
                                    @endif
                                </div>

                                <div class="soil-moisture-scale__legend">
                                    <span>0%</span>
                                    <span>Dopuszczalne: {{ $soilMin }}-{{ $soilMax }}%</span>
                                    <span>Idealne: {{ $soilIdealMin }}-{{ $soilIdealMax }}%</span>
                                    <span>100%</span>
                                </div>
                            </div>

                            <div class="row g-3 mb-2">
                                @include('plants._parameter_box', ['columnClass' => 'col-md-3', 'label' => 'Temperatura', 'value' => $entry->temp_c, 'unit' => '°C', 'status' => $temp, 'ranges' => $tempRanges, 'hintIcon' => $tempHintIcon, 'hintLabel' => $tempHintLabel])
                                @include('plants._parameter_box', ['columnClass' => 'col-md-3', 'label' => 'pH gleby', 'value' => $entry->ph, 'unit' => '', 'status' => $ph, 'ranges' => $phRanges, 'hintIcon' => $phHintIcon, 'hintLabel' => $phHintLabel])
                                @include('plants._parameter_box', ['columnClass' => 'col-md-3', 'label' => 'EC', 'value' => $entry->ec_uscm, 'unit' => 'µS/cm', 'status' => $ec, 'ranges' => $ecRanges, 'hintIcon' => $ecHintIcon, 'hintLabel' => $ecHintLabel])
                                @include('plants._parameter_box', ['columnClass' => 'col-md-3', 'label' => 'Zasolenie', 'value' => $entry->salt_mgl, 'unit' => 'mg/l', 'status' => $salt, 'ranges' => $saltRanges, 'hintIcon' => $saltHintIcon, 'hintLabel' => $saltHintLabel])
                            </div>

                            <div class="row g-3 mb-2">
                                @include('plants._parameter_box', ['columnClass' => 'col-md-4', 'label' => 'Azot (N)', 'value' => $entry->n_mgkg, 'unit' => 'mg/kg', 'status' => $n, 'ranges' => $nitrogenRanges, 'hintIcon' => $nitrogenHintIcon, 'hintLabel' => $nitrogenHintLabel])
                                @include('plants._parameter_box', ['columnClass' => 'col-md-4', 'label' => 'Fosfor (P)', 'value' => $entry->p_mgkg, 'unit' => 'mg/kg', 'status' => $p, 'ranges' => $phosphorusRanges, 'hintIcon' => $phosphorusHintIcon, 'hintLabel' => $phosphorusHintLabel])
                                @include('plants._parameter_box', ['columnClass' => 'col-md-4', 'label' => 'Potas (K)', 'value' => $entry->k_mgkg, 'unit' => 'mg/kg', 'status' => $k, 'ranges' => $potassiumRanges, 'hintIcon' => $potassiumHintIcon, 'hintLabel' => $potassiumHintLabel])
                            </div>

                            @if($entry->note)
                                <div class="mt-3 text-muted">{!! nl2br(e($entry->note)) !!}</div>
                            @endif

                            @if($entry->current_photo_path)
                                <div class="mt-3">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($entry->current_photo_path) }}" alt="Zdjęcie stanu aktualnego" class="img-fluid rounded" style="max-height: 320px; object-fit: cover;">
                                </div>
                            @endif

                            @if($latestEntry && $entry->is($latestEntry))
                                <form method="POST" action="{{ route('entries.update', $entry) }}" enctype="multipart/form-data" class="mt-3 border-top pt-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label">Zdjęcie stanu aktualnego</label>
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md">
                                            <input type="file" name="current_photo" accept="image/*" class="form-control @error('current_photo') is-invalid @enderror">
                                            @error('current_photo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">Dodaj zdjęcie</button>
                                        </div>
                                    </div>
                                    @if($entry->current_photo_path)
                                        <div class="form-check mt-2">
                                            <input type="checkbox" name="remove_current_photo" value="1" class="form-check-input" id="remove-current-photo-{{ $entry->id }}">
                                            <label class="form-check-label" for="remove-current-photo-{{ $entry->id }}">Usuń obecne zdjęcie przy zapisie</label>
                                        </div>
                                    @endif
                                </form>
                            @endif

                            <textarea id="copy-entry-{{ $entry->id }}" class="js-copy-source d-none">
Roślina: {{ $plant->name }}
Data: {{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }}
Ostatnie podlewanie:
@if($copyLastWatering)
{{ $copyLastWatering['at']->format('Y-m-d H:i') }} przy wilgotności {{ $copyLastWatering['moist_pct'] !== null ? number_format($copyLastWatering['moist_pct'], 1, ',', '') . ' %' : 'brak danych' }}
@else
brak danych
@endif

Temperatura: {{ $entry->temp_c }} °C
Wilgotność gleby: {{ $entry->moist_pct }} %
pH gleby: {{ $entry->ph }}
EC: {{ $entry->ec_uscm }} µS/cm

Azot (N): {{ $entry->n_mgkg }} mg/kg
Fosfor (P): {{ $entry->p_mgkg }} mg/kg
Potas (K): {{ $entry->k_mgkg }} mg/kg
Zasolenie: {{ $entry->salt_mgl }} mg/l
                            </textarea>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="mt-2">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
