@php
    $parameterRanges = [
        ['label' => 'Temperatura (°C)', 'min' => 'temp_min', 'max' => 'temp_max', 'idealMin' => 'temp_ideal_min', 'idealMax' => 'temp_ideal_max', 'step' => '0.01', 'minValue' => '-50', 'maxValue' => '80'],
        ['label' => 'pH gleby', 'min' => 'ph_min', 'max' => 'ph_max', 'idealMin' => 'ph_ideal_min', 'idealMax' => 'ph_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => '14'],
        ['label' => 'EC (µS/cm)', 'min' => 'ec_min', 'max' => 'ec_max', 'idealMin' => 'ec_ideal_min', 'idealMax' => 'ec_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => null],
        ['label' => 'Azot N (mg/kg)', 'min' => 'n_min', 'max' => 'n_max', 'idealMin' => 'n_ideal_min', 'idealMax' => 'n_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => null],
        ['label' => 'Fosfor P (mg/kg)', 'min' => 'p_min', 'max' => 'p_max', 'idealMin' => 'p_ideal_min', 'idealMax' => 'p_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => null],
        ['label' => 'Potas K (mg/kg)', 'min' => 'k_min', 'max' => 'k_max', 'idealMin' => 'k_ideal_min', 'idealMax' => 'k_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => null],
        ['label' => 'Zasolenie (mg/l)', 'min' => 'salt_min', 'max' => 'salt_max', 'idealMin' => 'salt_ideal_min', 'idealMax' => 'salt_ideal_max', 'step' => '0.01', 'minValue' => '0', 'maxValue' => null],
    ];
@endphp

<div class="mb-4">
    <label class="form-label d-block">Zakresy parametrów</label>
    <div class="small text-muted mb-2">
        Zielony kolor oznacza zakres idealny, żółty mieści się w dopuszczalnym, ale poza ideałem.
    </div>

    @foreach($parameterRanges as $range)
        <div class="mb-3">
            <div class="small fw-semibold mb-1">{{ $range['label'] }}</div>
            <div class="row g-2 mb-2">
                <div class="col-sm-6">
                    <label class="form-label">Dopuszczalne od</label>
                    <input
                        type="number"
                        step="{{ $range['step'] }}"
                        @if($range['minValue'] !== null) min="{{ $range['minValue'] }}" @endif
                        @if($range['maxValue'] !== null) max="{{ $range['maxValue'] }}" @endif
                        name="{{ $range['min'] }}"
                        value="{{ old($range['min'], isset($plant) ? $plant->{$range['min']} : null) }}"
                        class="form-control @error($range['min']) is-invalid @enderror"
                    >
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Dopuszczalne do</label>
                    <input
                        type="number"
                        step="{{ $range['step'] }}"
                        @if($range['minValue'] !== null) min="{{ $range['minValue'] }}" @endif
                        @if($range['maxValue'] !== null) max="{{ $range['maxValue'] }}" @endif
                        name="{{ $range['max'] }}"
                        value="{{ old($range['max'], isset($plant) ? $plant->{$range['max']} : null) }}"
                        class="form-control @error($range['max']) is-invalid @enderror"
                    >
                </div>
            </div>
            <div class="row g-2">
                <div class="col-sm-6">
                    <label class="form-label">Idealne od</label>
                    <input
                        type="number"
                        step="{{ $range['step'] }}"
                        @if($range['minValue'] !== null) min="{{ $range['minValue'] }}" @endif
                        @if($range['maxValue'] !== null) max="{{ $range['maxValue'] }}" @endif
                        name="{{ $range['idealMin'] }}"
                        value="{{ old($range['idealMin'], isset($plant) ? $plant->{$range['idealMin']} : null) }}"
                        class="form-control @error($range['idealMin']) is-invalid @enderror"
                    >
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Idealne do</label>
                    <input
                        type="number"
                        step="{{ $range['step'] }}"
                        @if($range['minValue'] !== null) min="{{ $range['minValue'] }}" @endif
                        @if($range['maxValue'] !== null) max="{{ $range['maxValue'] }}" @endif
                        name="{{ $range['idealMax'] }}"
                        value="{{ old($range['idealMax'], isset($plant) ? $plant->{$range['idealMax']} : null) }}"
                        class="form-control @error($range['idealMax']) is-invalid @enderror"
                    >
                </div>
            </div>
            @error($range['min'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error($range['max'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error($range['idealMin'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error($range['idealMax'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    @endforeach
</div>
