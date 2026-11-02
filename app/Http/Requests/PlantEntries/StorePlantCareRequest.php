<?php

declare(strict_types=1);

namespace App\Http\Requests\PlantEntries;

use App\Enums\PlantEntrySource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePlantCareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => [
                'string',
                Rule::enum(PlantEntrySource::class)->only([
                    PlantEntrySource::Watering,
                    PlantEntrySource::Fertilizing,
                    PlantEntrySource::Repotting,
                ]),
            ],
            'recorded_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
