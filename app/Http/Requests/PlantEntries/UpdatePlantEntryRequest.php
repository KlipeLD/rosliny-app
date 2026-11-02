<?php

declare(strict_types=1);

namespace App\Http\Requests\PlantEntries;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePlantEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
            'temp_c' => ['nullable', 'numeric', 'between:-50,80'],
            'moist_pct' => ['nullable', 'numeric', 'between:0,100'],
            'ec_uscm' => ['nullable', 'integer', 'min:0'],
            'ph' => ['nullable', 'numeric', 'between:0,14'],
            'n_mgkg' => ['nullable', 'integer', 'min:0'],
            'p_mgkg' => ['nullable', 'integer', 'min:0'],
            'k_mgkg' => ['nullable', 'integer', 'min:0'],
            'salt_mgl' => ['nullable', 'integer', 'min:0'],
            'current_photo' => ['nullable', 'image', 'max:4096'],
            'remove_current_photo' => ['nullable', 'boolean'],
        ];
    }
}
