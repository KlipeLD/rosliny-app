<?php

declare(strict_types=1);

namespace App\Actions\PlantEntries;

use App\Models\Plant;
use Illuminate\Support\Facades\DB;

final class StorePlantCare
{
    /**
     * @param  array{actions: array<int, string>, recorded_at: string, note?: string|null}  $data
     */
    public function execute(Plant $plant, array $data): void
    {
        DB::transaction(function () use ($plant, $data): void {
            foreach (array_unique($data['actions']) as $action) {
                $plant->entries()->create([
                    'source' => $action,
                    'payload' => [],
                    'recorded_at' => $data['recorded_at'],
                    'note' => $data['note'] ?? null,
                ]);
            }
        });
    }
}
