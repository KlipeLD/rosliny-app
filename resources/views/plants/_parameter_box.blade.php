@php
    $rangeIdealMin = (float) $ranges[0];
    $rangeIdealMax = (float) $ranges[1];
    $rangeWarnMin = (float) $ranges[2];
    $rangeWarnMax = (float) $ranges[3];
    $numericValue = $value !== null ? (float) $value : null;
    $scaleMin = min($rangeWarnMin, $rangeIdealMin, $numericValue ?? $rangeWarnMin);
    $scaleMax = max($rangeWarnMax, $rangeIdealMax, $numericValue ?? $rangeWarnMax);
    $scaleSpan = $scaleMax - $scaleMin;
    $warnLeft = $scaleSpan > 0 ? (($rangeWarnMin - $scaleMin) / $scaleSpan) * 100 : 0;
    $warnWidth = $scaleSpan > 0 ? (($rangeWarnMax - $rangeWarnMin) / $scaleSpan) * 100 : 0;
    $idealLeft = $scaleSpan > 0 ? (($rangeIdealMin - $scaleMin) / $scaleSpan) * 100 : 0;
    $idealWidth = $scaleSpan > 0 ? (($rangeIdealMax - $rangeIdealMin) / $scaleSpan) * 100 : 0;
    $markerLeft = $numericValue !== null && $scaleSpan > 0
        ? (($numericValue - $scaleMin) / $scaleSpan) * 100
        : null;
    $displayValue = $numericValue !== null
        ? rtrim(rtrim(number_format($numericValue, 2, ',', ''), '0'), ',')
        : 'brak';
    $displayMin = rtrim(rtrim(number_format($scaleMin, 2, ',', ''), '0'), ',');
    $displayMax = rtrim(rtrim(number_format($scaleMax, 2, ',', ''), '0'), ',');
    $displayIdealMin = rtrim(rtrim(number_format($rangeIdealMin, 2, ',', ''), '0'), ',');
    $displayIdealMax = rtrim(rtrim(number_format($rangeIdealMax, 2, ',', ''), '0'), ',');
    $hintIcon = $hintIcon ?? null;
    $hintLabel = $hintLabel ?? null;
@endphp

<div class="{{ $columnClass }}">
    <div class="param-box bg-{{ $status['class'] }}">
        <div class="param-label">{{ $label }}</div>
        <div class="param-value">
            {{ $displayValue }} @if($numericValue !== null){{ $unit }}@endif
            @if($hintIcon)
                <span class="param-value__hint" title="{{ $hintLabel }}" aria-label="{{ $hintLabel }}">{!! $hintIcon !!}</span>
            @endif
        </div>
        <div class="param-status">{{ $status['label'] }}</div>

        <div class="param-scale" aria-label="{{ $label }}: zakres idealny {{ $displayIdealMin }}-{{ $displayIdealMax }} {{ $unit }}">
            <div class="param-scale__base"></div>
            <div class="param-scale__range param-scale__range--warn" style="left: {{ $warnLeft }}%; width: {{ $warnWidth }}%;"></div>
            <div class="param-scale__range param-scale__range--ideal" style="left: {{ $idealLeft }}%; width: {{ $idealWidth }}%;"></div>
            @if($markerLeft !== null)
                <div class="param-scale__marker" style="left: {{ $markerLeft }}%;"></div>
            @endif
        </div>

        <div class="param-scale__legend">
            <span>{{ $displayMin }}</span>
            <span>ideał {{ $displayIdealMin }}-{{ $displayIdealMax }}</span>
            <span>{{ $displayMax }}</span>
        </div>
    </div>
</div>
