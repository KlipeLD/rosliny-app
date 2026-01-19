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
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('plants.edit', $plant) }}" class="btn btn-outline-primary">Edytuj</a>
            <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary">Wróć</a>
        </div>
    </div>

    @if($plant->photo_path)
        <div class="mb-4">
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($plant->photo_path) }}"
                alt="{{ $plant->name }}"
                class="img-fluid rounded"
                style="max-height: 420px; object-fit: cover;"
            >
        </div>
    @endif

    <div class="card p-3 mb-4">
        <h5 class="mb-3">Historia wpisów</h5>

        <form method="POST" action="{{ route('plants.entries.fetch', $plant) }}" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-primary">Pobierz dane z API</button>
        </form>

        @if($entries->count() === 0)
            <div class="text-muted">Brak wpisów.</div>
        @else
            @foreach($entries as $entry)
                @php
                    $temp  = param_status($entry->temp_c,     [18,26,15,30]);
                    $moist = param_status($entry->moist_pct,  [20,60,10,80]);
                    $ph    = param_status($entry->ph,         [6.0,7.2,5.5,7.8]);
                    $ec    = param_status($entry->ec_uscm,    [200,1200,100,2000]);

                    $n     = param_status($entry->n_mgkg,     [20,60,10,100]);
                    $p     = param_status($entry->p_mgkg,     [10,40,5,80]);
                    $k     = param_status($entry->k_mgkg,     [20,80,10,150]);

                    $salt  = param_status($entry->salt_mgl,   [0,200,200,400]);
                @endphp

                <div class="card mb-3 shadow-sm">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-muted small">
                                {{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }}
                                · {{ $entry->source }}
                            </div>

                            <button type="button"
                                    class="btn btn-sm btn-outline-primary js-copy-entry"
                                    data-entry-id="{{ $entry->id }}">
                                Kopiuj
                            </button>



                            <div class="d-flex gap-2">
                                <a href="{{ route('entries.edit', $entry) }}"
                                class="btn btn-sm btn-outline-secondary">
                                    Edytuj
                                </a>

                                <form method="POST"
                                    action="{{ route('entries.destroy', $entry) }}"
                                    onsubmit="return confirm('Usunąć ten wpis?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                        Usuń
                                    </button>
                                </form>
                            </div>
                        </div>


                        {{-- PODSTAWOWE PARAMETRY --}}
                        <div class="row g-3 mb-2">

                            <div class="col-md-3">
                                <div class="param-box bg-{{ $temp['class'] }}">
                                    <div class="param-label">Temperatura</div>
                                    <div class="param-value">{{ $entry->temp_c }} °C</div>
                                    <div class="param-status">{{ $temp['label'] }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="param-box bg-{{ $moist['class'] }}">
                                    <div class="param-label">Wilgotność</div>
                                    <div class="param-value">{{ $entry->moist_pct }} %</div>
                                    <div class="param-status">{{ $moist['label'] }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="param-box bg-{{ $ph['class'] }}">
                                    <div class="param-label">pH gleby</div>
                                    <div class="param-value">{{ $entry->ph }}</div>
                                    <div class="param-status">{{ $ph['label'] }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="param-box bg-{{ $ec['class'] }}">
                                    <div class="param-label">EC</div>
                                    <div class="param-value">{{ $entry->ec_uscm }} µS/cm</div>
                                    <div class="param-status">{{ $ec['label'] }}</div>
                                </div>
                            </div>

                        </div>

                        {{-- NPK --}}
                        <div class="row g-3 mb-2">

                            <div class="col-md-4">
                                <div class="param-box bg-{{ $n['class'] }}">
                                    <div class="param-label">Azot (N)</div>
                                    <div class="param-value">{{ $entry->n_mgkg }} mg/kg</div>
                                    <div class="param-status">{{ $n['label'] }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="param-box bg-{{ $p['class'] }}">
                                    <div class="param-label">Fosfor (P)</div>
                                    <div class="param-value">{{ $entry->p_mgkg }} mg/kg</div>
                                    <div class="param-status">{{ $p['label'] }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="param-box bg-{{ $k['class'] }}">
                                    <div class="param-label">Potas (K)</div>
                                    <div class="param-value">{{ $entry->k_mgkg }} mg/kg</div>
                                    <div class="param-status">{{ $k['label'] }}</div>
                                </div>
                            </div>

                        </div>

                        {{-- ZASOLENIE --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="param-box bg-{{ $salt['class'] }}">
                                    <div class="param-label">Zasolenie</div>
                                    <div class="param-value">{{ $entry->salt_mgl }} mg/l</div>
                                    <div class="param-status">{{ $salt['label'] }}</div>
                                </div>
                            </div>
                        </div>

                        @if($entry->note)
                            <div class="mt-3 text-muted">
                                {!! nl2br(e($entry->note)) !!}
                            </div>
                        @endif
                        
                        <textarea
                            id="copy-entry-{{ $entry->id }}"
                            class="js-copy-source d-none">
Roślina: {{ $plant->name }}
Data: {{ ($entry->recorded_at ?? $entry->created_at)->format('Y-m-d H:i') }}

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
                @endforeach

            <div class="mt-2">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
