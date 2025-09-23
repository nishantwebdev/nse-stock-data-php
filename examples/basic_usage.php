<?php

require_once __DIR__ . '/../vendor/autoload.php';

use NseData\StockClient;

// Initialize the NSE API client
$nse = new StockClient();

try {
    // Get equity details for a stock
    echo "=== Getting Equity Details for TCS ===\n";
    $equityDetails = $nse->getEquityDetails('TCS');
    echo "Company: " . $equityDetails->info->companyName . "\n";
    echo "Symbol: " . $equityDetails->info->symbol . "\n";
    echo "Last Price: ₹" . $equityDetails->priceInfo->lastPrice . "\n";
    echo "Change: " . $equityDetails->priceInfo->change . " (" . $equityDetails->priceInfo->pChange . "%)\n";
    echo "Previous Close: ₹" . $equityDetails->priceInfo->previousClose . "\n";
    echo "Open: ₹" . $equityDetails->priceInfo->open . "\n";
    echo "High: ₹" . $equityDetails->priceInfo->intraDayHighLow['max'] . "\n";
    echo "Low: ₹" . $equityDetails->priceInfo->intraDayHighLow['min'] . "\n\n";

    // Get equity price for a specific date
    echo "=== Getting Historical Price ===\n";
    $date = new DateTime('2024-01-15');
    $price = $nse->getEquityPriceByDate('TCS', $date);
    echo "TCS price on " . $date->format('Y-m-d') . ": ₹" . $price . "\n\n";

    // Get derivative data
    echo "=== Getting Derivative Data for RELIANCE ===\n";
    $derivativeData = $nse->getDerivativeData('RELIANCE');
    foreach ($derivativeData as $expiry => $price) {
        echo "Expiry: $expiry, Price: ₹$price\n";
    }
    echo "\n";

    // Get option chain data
    echo "=== Getting Option Chain for NIFTY ===\n";
    $optionChain = $nse->getIndexOptionChain('NIFTY');
    echo "Underlying Value: ₹" . $optionChain->records->underlyingValue . "\n";
    echo "Total CE OI: " . $optionChain->filtered->CE->totOI . "\n";
    echo "Total PE OI: " . $optionChain->filtered->PE->totOI . "\n";
    echo "Total CE Volume: " . $optionChain->filtered->CE->totVol . "\n";
    echo "Total PE Volume: " . $optionChain->filtered->PE->totVol . "\n\n";

    // Check if a date is a holiday
    echo "=== Checking Holidays ===\n";
    $testDate = new DateTime('2024-01-26'); // Republic Day
    $isHoliday = $nse->checkHoliday($testDate);
    echo "Is " . $testDate->format('Y-m-d') . " a holiday? " . ($isHoliday ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
