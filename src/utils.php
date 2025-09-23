<?php

namespace NseData;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Get date range chunks.
 *
 * @param DateTime $startDate
 * @param DateTime $endDate
 * @param int $chunkInDays
 * @return array<array{start: string, end: string}>
 */
function getDateRangeChunks(\DateTime $startDate, \DateTime $endDate, int $chunkInDays): array
{
    $range = CarbonPeriod::create(Carbon::instance($startDate), "$chunkInDays days", Carbon::instance($endDate));
    $chunks = iterator_to_array($range);
    $dateRanges = [];

    for ($i = 0; $i < count($chunks); $i++) {
        $dateRanges[] = [
            'start' => $i > 0 ? $chunks[$i]->addDay()->format('d-m-Y') : $chunks[$i]->format('d-m-Y'),
            'end' => isset($chunks[$i + 1]) ? $chunks[$i + 1]->format('d-m-Y') : Carbon::instance($endDate)->format('d-m-Y')
        ];
    }

    return $dateRanges;
}

/**
 * Sleep for a specified number of milliseconds.
 *
 * @param int $ms
 * @return void
 */
function sleepMilliseconds(int $ms): void
{
    usleep($ms * 1000);
}
