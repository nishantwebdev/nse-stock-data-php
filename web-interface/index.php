<?php


use NseData\DateRange;

$errors = [];

try {
    $nse = new \NseData\StockClient();
    $startDate = new \DateTime('2025-01-01');
    $endDate = new \DateTime('2025-01-31');
    $dateRange = new DateRange(['start' => $startDate, 'end' => $endDate]);
    $marketStatus = $nse->getEquityStockIndices('NIFTY 50');
    echo "<pre>";
    print_r($marketStatus);
    echo "</pre>";
    exit;
} catch (Throwable $e) {
    $errors[] = "Fatal error: " . htmlspecialchars($e->getMessage());
    echo "<pre>";
    print_r($e);
    echo "</pre>";
    exit;
}
?>
