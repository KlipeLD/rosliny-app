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
        @else
            <form method="POST" action="{{ route('plants.entries.watering', $plant) }}" class="card card-body mb-3">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Data podlewania</label>
                        <input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}" class="form-control @error('recorded_at') is-invalid @enderror">
                        @error('recorded_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
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
        @endif

        @if($entries->count() === 0)
            <div class="text-muted">Brak wpisów.</div>
        @else
            @foreach($entries as $entry)
                @if($plant->plant_type === 'manual')
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-semibold">Podlewanie</div>
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
                        </div>
                    </div>
                @else
                    @php
                        $temp = param_status($entry->temp_c, [18, 26, 15, 30]);
                        $moist = param_status($entry->moist_pct, $plant->moistureRanges());
                        $ph = param_status($entry->ph, [6.0, 7.2, 5.5, 7.8]);
                        $ec = param_status($entry->ec_uscm, [200, 1200, 100, 2000]);
                        $n = param_status($entry->n_mgkg, [20, 60, 10, 100]);
                        $p = param_status($entry->p_mgkg, [10, 40, 5, 80]);
                        $k = param_status($entry->k_mgkg, [20, 80, 10, 150]);
                        $salt = param_status($entry->salt_mgl, [0, 200, 200, 400]);
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
                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-muted small">
                                    {{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }} · {{ $entry->source }}
                                </div>

                                <div class="d-flex gap-2">
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
                                <div class="col-md-3"><div class="param-box bg-{{ $temp['class'] }}"><div class="param-label">Temperatura</div><div class="param-value">{{ $entry->temp_c }} °C</div><div class="param-status">{{ $temp['label'] }}</div></div></div>
                                <div class="col-md-3"><div class="param-box bg-{{ $ph['class'] }}"><div class="param-label">pH gleby</div><div class="param-value">{{ $entry->ph }}</div><div class="param-status">{{ $ph['label'] }}</div></div></div>
                                <div class="col-md-3"><div class="param-box bg-{{ $ec['class'] }}"><div class="param-label">EC</div><div class="param-value">{{ $entry->ec_uscm }} µS/cm</div><div class="param-status">{{ $ec['label'] }}</div></div></div>
                                <div class="col-md-3"><div class="param-box bg-{{ $salt['class'] }}"><div class="param-label">Zasolenie</div><div class="param-value">{{ $entry->salt_mgl }} mg/l</div><div class="param-status">{{ $salt['label'] }}</div></div></div>
                            </div>

                            <div class="row g-3 mb-2">
                                <div class="col-md-4"><div class="param-box bg-{{ $n['class'] }}"><div class="param-label">Azot (N)</div><div class="param-value">{{ $entry->n_mgkg }} mg/kg</div><div class="param-status">{{ $n['label'] }}</div></div></div>
                                <div class="col-md-4"><div class="param-box bg-{{ $p['class'] }}"><div class="param-label">Fosfor (P)</div><div class="param-value">{{ $entry->p_mgkg }} mg/kg</div><div class="param-status">{{ $p['label'] }}</div></div></div>
                                <div class="col-md-4"><div class="param-box bg-{{ $k['class'] }}"><div class="param-label">Potas (K)</div><div class="param-value">{{ $entry->k_mgkg }} mg/kg</div><div class="param-status">{{ $k['label'] }}</div></div></div>
                            </div>

                            @if($entry->note)
                                <div class="mt-3 text-muted">{!! nl2br(e($entry->note)) !!}</div>
                            @endif
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
