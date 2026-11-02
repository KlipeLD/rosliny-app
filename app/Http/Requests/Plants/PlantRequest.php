<?php

declare(strict_types=1);

namespace App\Http\Requests\Plants;

use App\Enums\PlantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class PlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plant_type' => ['required', Rule::enum(PlantType::class)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'soil_moisture_min' => ['nullable', 'numeric', 'between:0,100'],
            'soil_moisture_max' => ['nullable', 'numeric', 'between:0,100', 'gte:soil_moisture_min'],
            'soil_moisture_ideal_min' => ['nullable', 'numeric', 'between:0,100'],
            'soil_moisture_ideal_max' => ['nullable', 'numeric', 'between:0,100', 'gte:soil_moisture_ideal_min'],
            'watering_interval_days' => ['nullable', 'integer', 'between:1,365'],
            'temp_min' => ['nullable', 'numeric', 'between:-50,80'],
            'temp_max' => ['nullable', 'numeric', 'between:-50,80', 'gte:temp_min'],
            'temp_ideal_min' => ['nullable', 'numeric', 'between:-50,80'],
            'temp_ideal_max' => ['nullable', 'numeric', 'between:-50,80', 'gte:temp_ideal_min'],
            'ph_min' => ['nullable', 'numeric', 'between:0,14'],
            'ph_max' => ['nullable', 'numeric', 'between:0,14', 'gte:ph_min'],
            'ph_ideal_min' => ['nullable', 'numeric', 'between:0,14'],
            'ph_ideal_max' => ['nullable', 'numeric', 'between:0,14', 'gte:ph_ideal_min'],
            'ec_min' => ['nullable', 'numeric', 'min:0'],
            'ec_max' => ['nullable', 'numeric', 'min:0', 'gte:ec_min'],
            'ec_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'ec_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:ec_ideal_min'],
            'n_min' => ['nullable', 'numeric', 'min:0'],
            'n_max' => ['nullable', 'numeric', 'min:0', 'gte:n_min'],
            'n_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'n_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:n_ideal_min'],
            'p_min' => ['nullable', 'numeric', 'min:0'],
            'p_max' => ['nullable', 'numeric', 'min:0', 'gte:p_min'],
            'p_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'p_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:p_ideal_min'],
            'k_min' => ['nullable', 'numeric', 'min:0'],
            'k_max' => ['nullable', 'numeric', 'min:0', 'gte:k_min'],
            'k_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'k_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:k_ideal_min'],
            'salt_min' => ['nullable', 'numeric', 'min:0'],
            'salt_max' => ['nullable', 'numeric', 'min:0', 'gte:salt_min'],
            'salt_ideal_min' => ['nullable', 'numeric', 'min:0'],
            'salt_ideal_max' => ['nullable', 'numeric', 'min:0', 'gte:salt_ideal_min'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateMoistureRange($validator);
                $this->validateParameterRanges($validator);
            },
        ];
    }

    private function validateMoistureRange(Validator $validator): void
    {
        $fields = [
            'soil_moisture_min',
            'soil_moisture_max',
            'soil_moisture_ideal_min',
            'soil_moisture_ideal_max',
        ];

        if (! collect($fields)->contains(fn (string $field): bool => $this->filled($field))) {
            return;
        }

        $messages = [
            'soil_moisture_min' => 'Uzupełnij pełny zakres wilgotności gleby.',
            'soil_moisture_max' => 'Uzupełnij pełny zakres wilgotności gleby.',
            'soil_moisture_ideal_min' => 'Uzupełnij pełny zakres idealnej wilgotności gleby.',
            'soil_moisture_ideal_max' => 'Uzupełnij pełny zakres idealnej wilgotności gleby.',
        ];

        foreach ($fields as $field) {
            if (! $this->filled($field)) {
                $validator->errors()->add($field, $messages[$field]);
            }
        }

        if (
            collect($fields)->every(fn (string $field): bool => $this->filled($field))
            && (
                (float) $this->input('soil_moisture_ideal_min') < (float) $this->input('soil_moisture_min')
                || (float) $this->input('soil_moisture_ideal_max') > (float) $this->input('soil_moisture_max')
            )
        ) {
            $validator->errors()->add(
                'soil_moisture_ideal_min',
                'Zakres idealny musi mieścić się w zakresie dopuszczalnym.',
            );
        }
    }

    private function validateParameterRanges(Validator $validator): void
    {
        foreach ($this->parameterRangeDefinitions() as $range) {
            [$minField, $maxField] = $range['acceptable'];
            [$idealMinField, $idealMaxField] = $range['ideal'];

            $this->validateCompletePair($validator, $minField, $maxField, $range['label'], 'dopuszczalny');
            $this->validateCompletePair($validator, $idealMinField, $idealMaxField, $range['label'], 'idealny');

            if (
                $this->filled($minField)
                && $this->filled($maxField)
                && $this->filled($idealMinField)
                && $this->filled($idealMaxField)
                && (
                    (float) $this->input($idealMinField) < (float) $this->input($minField)
                    || (float) $this->input($idealMaxField) > (float) $this->input($maxField)
                )
            ) {
                $validator->errors()->add(
                    $idealMinField,
                    'Zakres idealny musi mieścić się w zakresie dopuszczalnym dla pola: '.$range['label'].'.',
                );
            }
        }
    }

    private function validateCompletePair(
        Validator $validator,
        string $minField,
        string $maxField,
        string $label,
        string $rangeType,
    ): void {
        if ($this->filled($minField) === $this->filled($maxField)) {
            return;
        }

        $missingField = $this->filled($minField) ? $maxField : $minField;

        $validator->errors()->add(
            $missingField,
            'Uzupełnij pełny zakres '.$rangeType.' dla pola: '.$label.'.',
        );
    }

    private function parameterRangeDefinitions(): array
    {
        return [
            ['label' => 'temperatura', 'acceptable' => ['temp_min', 'temp_max'], 'ideal' => ['temp_ideal_min', 'temp_ideal_max']],
            ['label' => 'pH', 'acceptable' => ['ph_min', 'ph_max'], 'ideal' => ['ph_ideal_min', 'ph_ideal_max']],
            ['label' => 'EC', 'acceptable' => ['ec_min', 'ec_max'], 'ideal' => ['ec_ideal_min', 'ec_ideal_max']],
            ['label' => 'azot', 'acceptable' => ['n_min', 'n_max'], 'ideal' => ['n_ideal_min', 'n_ideal_max']],
            ['label' => 'fosfor', 'acceptable' => ['p_min', 'p_max'], 'ideal' => ['p_ideal_min', 'p_ideal_max']],
            ['label' => 'potas', 'acceptable' => ['k_min', 'k_max'], 'ideal' => ['k_ideal_min', 'k_ideal_max']],
            ['label' => 'zasolenie', 'acceptable' => ['salt_min', 'salt_max'], 'ideal' => ['salt_ideal_min', 'salt_ideal_max']],
        ];
    }
}
