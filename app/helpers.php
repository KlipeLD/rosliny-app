<?php

function param_status(float|int|null $value, array $ranges): array
{
    if ($value === null) {
        return ['class' => 'secondary', 'label' => 'brak'];
    }

    [$goodMin, $goodMax, $warnMin, $warnMax] = $ranges;

    if ($value >= $goodMin && $value <= $goodMax) {
        return ['class' => 'success', 'label' => 'dobry'];
    }

    if ($value >= $warnMin && $value <= $warnMax) {
        return ['class' => 'warning', 'label' => 'uwaga'];
    }

    return ['class' => 'danger', 'label' => 'krytyczny'];
}
