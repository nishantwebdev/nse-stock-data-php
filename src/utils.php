<?php

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
function getDateRangeChunks(DateTime $startDate, DateTime $endDate, int $chunkInDays): array
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

/**
 * Get data schema from input data.
 *
 * @param mixed $data
 * @param bool $isTypeStrict
 * @return array|string
 */
function getDataSchema($data, bool $isTypeStrict = true): array|string
{
    if (!is_object($data) && !is_array($data)) {
        return $isTypeStrict ? gettype($data) : 'any';
    }

    if (is_array($data) && !empty($data) && (!is_object($data[0]) && !is_array($data[0]))) {
        return $isTypeStrict ? gettype($data[0]) . '[]' : 'any';
    }

    $result = [];
    foreach ($data as $key => $value) {
        if ($value instanceof DateTime) {
            $result[] = "$key: " . ($isTypeStrict ? 'Date' : 'any');
        } elseif ($value === null || is_string($value)) {
            $result[] = "$key: " . ($isTypeStrict ? 'string|null' : 'any');
        } elseif (is_array($value)) {
            $typeForEmpty = $isTypeStrict ? [] : 'any';
            $result[] = [
                $key => !empty($value) ? getDataSchema($value[0], $isTypeStrict) : $typeForEmpty
            ];
        } elseif (is_object($value)) {
            $result[] = [
                $key => getDataSchema($value, $isTypeStrict)
            ];
        } else {
            $result[] = "$key: " . ($isTypeStrict ? gettype($value) : 'any');
        }
    }

    return $result;
}
