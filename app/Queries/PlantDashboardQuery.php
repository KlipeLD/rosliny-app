<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Plants\WateringInstabilityService;
use App\Services\Plants\WateringPredictionService;
use App\Services\Plants\WaterRetentionService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final readonly class PlantDashboardQuery
{
    private const RANKING_LIMIT = 6;

    private const RANKING_CANDIDATE_LIMIT = 100;

    private const PER_PAGE = 50;

    private const HISTORY_DAYS = 120;

    private const ENTRY_LIMIT_PER_PLANT = 500;

    private const CACHE_SECONDS = 300;

    private const CACHE_KEY = 'plant-dashboard-rankings';

    public function __construct(
        private WateringPredictionService $predictionService,
        private WateringInstabilityService $instabilityService,
        private WaterRetentionService $retentionService,
        private CacheRepository $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $historyStart = now()->subDays(self::HISTORY_DAYS);

        $plants = $this->plantsWithRecentEntries($historyStart)
            ->latest()
            ->paginate(self::PER_PAGE);

        $wateringPredictions = $plants
            ->getCollection()
            ->mapWithKeys(fn (Plant $plant): array => [
                $plant->id => $this->predictionService->predict(
                    $plant,
                    $this->entriesForAnalysis($plant),
                ),
            ]);

        /**
         * @var array{
         *     topWateringPlants: Collection<int, array<string, mixed>>,
         *     topUnstablePlants: Collection<int, array<string, mixed>>,
         *     topWaterRetainingPlants: Collection<int, array<string, mixed>>
         * } $rankings
         */
        $rankings = $this->cache->remember(
            $this->rankingCacheKey(),
            self::CACHE_SECONDS,
            fn (): array => $this->buildRankings($historyStart),
        );

        return [
            'plants' => $plants,
            'wateringPredictions' => $wateringPredictions,
            ...$rankings,
        ];
    }

    /**
     * @return array{
     *     topWateringPlants: Collection<int, covariant array{
     *         plant: Plant,
     *         prediction: non-empty-array
     *     }>,
     *     topUnstablePlants: Collection<int, covariant array{
     *         plant: Plant,
     *         instability: non-empty-array
     *     }>,
     *     topWaterRetainingPlants: Collection<int, covariant array{
     *         plant: Plant,
     *         retention: non-empty-array
     *     }>
     * }
     */
    private function buildRankings(CarbonInterface $historyStart): array
    {
        $analyses = $this->rankingCandidates($historyStart)
            ->mapWithKeys(function (Plant $plant): array {
                $entries = $this->entriesForAnalysis($plant);

                return [
                    $plant->id => [
                        'plant' => $plant,
                        'prediction' => $this->predictionService->predict(
                            $plant,
                            $entries,
                        ),
                        'instability' => $this->instabilityService->analyze(
                            $plant,
                            $entries,
                        ),
                        'retention' => $this->retentionService->analyze(
                            $plant,
                            $entries,
                        ),
                    ],
                ];
            });

        $topWateringPlants = $analyses
            ->filter(
                fn (array $item): bool => $item['prediction']['available']
                    ?? false,
            )
            ->map(fn (array $item): array => [
                'plant' => $item['plant'],
                'prediction' => $item['prediction'],
            ])
            ->sortBy(
                fn (array $item): int => $item['prediction']['date']
                    ->timestamp,
            )
            ->take(self::RANKING_LIMIT)
            ->values();

        $topUnstablePlants = $analyses
            ->filter(
                fn (array $item): bool => $item['instability']['available']
                    ?? false,
            )
            ->map(fn (array $item): array => [
                'plant' => $item['plant'],
                'instability' => $item['instability'],
            ])
            ->sortByDesc(
                fn (array $item): int => $item['instability']['score'],
            )
            ->take(self::RANKING_LIMIT)
            ->values();

        $topWaterRetainingPlants = $analyses
            ->filter(
                fn (array $item): bool => $item['retention']['available']
                    ?? false,
            )
            ->map(fn (array $item): array => [
                'plant' => $item['plant'],
                'retention' => $item['retention'],
            ])
            ->sortByDesc(
                fn (array $item): int => $item['retention']['score'],
            )
            ->take(self::RANKING_LIMIT)
            ->values();

        return compact(
            'topWateringPlants',
            'topUnstablePlants',
            'topWaterRetainingPlants',
        );
    }

    /**
     * @return Collection<int, Plant>
     */
    private function rankingCandidates(
        CarbonInterface $historyStart,
    ): Collection {
        return $this->plantsWithRecentEntries($historyStart)
            ->whereHas(
                'entries',
                fn (Builder $query) => $this->onlyRecentEntries(
                    $query,
                    $historyStart,
                ),
            )
            ->addSelect([
                'latest_entry_at' => PlantEntry::query()
                    ->selectRaw(
                        'MAX(COALESCE(recorded_at, created_at))',
                    )
                    ->whereColumn('plant_id', 'plants.id'),
            ])
            ->orderByDesc('latest_entry_at')
            ->limit(self::RANKING_CANDIDATE_LIMIT)
            ->get();
    }

    /**
     * @return Builder<Plant>
     */
    private function plantsWithRecentEntries(
        CarbonInterface $historyStart,
    ): Builder {
        return Plant::query()->with([
            'entries' => function (HasMany $query) use (
                $historyStart,
            ): void {
                $this->onlyRecentEntries($query, $historyStart);

                $query
                    ->orderByRaw(
                        'COALESCE(recorded_at, created_at) DESC',
                    )
                    ->limit(self::ENTRY_LIMIT_PER_PLANT);
            },
        ]);
    }

    /**
     * @param  Builder<PlantEntry>|HasMany<PlantEntry, Plant>  $query
     */
    private function onlyRecentEntries(
        Builder|HasMany $query,
        CarbonInterface $historyStart,
    ): void {
        $query->whereRaw(
            'COALESCE(recorded_at, created_at) >= ?',
            [$historyStart],
        );
    }

    /**
     * @return Collection<int, PlantEntry>
     */
    private function entriesForAnalysis(Plant $plant): Collection
    {
        return $plant->entries
            ->sortBy(
                fn (PlantEntry $entry): int => (
                    $entry->recorded_at
                    ?? $entry->created_at
                )->timestamp,
            )
            ->values();
    }

    private function rankingCacheKey(): string
    {
        $version = implode('|', [
            (string) Plant::query()->count(),
            (string) Plant::query()->max('updated_at'),
            (string) PlantEntry::query()->count(),
            (string) PlantEntry::query()->max('updated_at'),
            now()->format('Y-m-d'),
        ]);

        return self::CACHE_KEY.':'.sha1($version);
    }
}
