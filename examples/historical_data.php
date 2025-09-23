<?php

require_once __DIR__ . '/../vendor/autoload.php';

use NseData\StockClient;
use NseData\DateRange;

// Initialize the NSE API client
$nse = new StockClient();

try {
    echo "=== Getting Historical Data for RELIANCE ===\n";
    
    // Define date range for historical data
    $startDate = new DateTime('2024-01-01');
    $endDate = new DateTime('2024-01-31');
    $dateRange = new DateRange(['start' => $startDate, 'end' => $endDate]);
    
    // Get historical data
    $historicalData = $nse->getEquityHistoricalData('RELIANCE', $dateRange);
    
    echo "Historical data points: " . count($historicalData) . "\n";
    
    if (!empty($historicalData)) {
        $firstData = $historicalData[0];
        if (!empty($firstData->data)) {
            $firstRecord = $firstData->data[0];
            echo "First record date: " . $firstRecord->CH_TIMESTAMP . "\n";
            echo "Open: ₹" . $firstRecord->CH_OPENING_PRICE . "\n";
            echo "High: ₹" . $firstRecord->CH_TRADE_HIGH_PRICE . "\n";
            echo "Low: ₹" . $firstRecord->CH_TRADE_LOW_PRICE . "\n";
            echo "Close: ₹" . $firstRecord->CH_CLOSING_PRICE . "\n";
            echo "Volume: " . $firstRecord->CH_TOT_TRADED_QTY . "\n";
        }
    }
    
    echo "\n=== Getting Intraday Data for TCS ===\n";
    
    // Get intraday data
    $intradayData = $nse->getEquityIntradayData('TCS');
    echo "Symbol: " . $intradayData->identifier . "\n";
    echo "Name: " . $intradayData->name . "\n";
    echo "Close Price: ₹" . $intradayData->closePrice . "\n";
    echo "Data points: " . count($intradayData->graphData) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
