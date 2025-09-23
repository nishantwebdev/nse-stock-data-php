<?php

require_once __DIR__ . '/../vendor/autoload.php';

use NseData\StockClient;

// Initialize the NSE API client
$nse = new StockClient();

try {
    echo "=== Holiday Cache Management ===\n";
    
    // Get current year holiday data (will be cached)
    echo "Getting holiday data for current year...\n";
    $currentYearHolidays = $nse->getHolidayData();
    echo "Current year holidays: " . count($currentYearHolidays) . " days\n";
    echo "First few holidays: " . implode(', ', array_slice($currentYearHolidays, 0, 3)) . "\n\n";
    
    // Get holiday data for a specific year
    echo "Getting holiday data for 2024...\n";
    $holidays2024 = $nse->getHolidayDataForYear(2024);
    echo "2024 holidays: " . count($holidays2024) . " days\n";
    echo "First few holidays: " . implode(', ', array_slice($holidays2024, 0, 3)) . "\n\n";
    
    // Get holiday data for next year
    $nextYear = date('Y') + 1;
    echo "Getting holiday data for $nextYear...\n";
    $nextYearHolidays = $nse->getHolidayDataForYear($nextYear);
    echo "$nextYear holidays: " . count($nextYearHolidays) . " days\n";
    echo "First few holidays: " . implode(', ', array_slice($nextYearHolidays, 0, 3)) . "\n\n";
    
    // Test holiday checking
    echo "=== Testing Holiday Checking ===\n";
    $testDates = [
        new DateTime('2024-01-26'), // Republic Day
        new DateTime('2024-08-15'), // Independence Day
        new DateTime('2024-10-02'), // Gandhi Jayanti
        new DateTime('2024-12-25'), // Christmas
        new DateTime('2024-01-15'), // Regular trading day
    ];
    
    foreach ($testDates as $date) {
        $isHoliday = $nse->checkHoliday($date);
        $status = $isHoliday ? 'Holiday' : 'Trading Day';
        echo $date->format('Y-m-d (l)') . ": $status\n";
    }
    
    echo "\n=== Cache Management ===\n";
    
    // Show cache directory info
    $cacheDir = sys_get_temp_dir() . '/nse-india-api/cache';
    echo "Cache directory: $cacheDir\n";
    
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/holiday_*.json');
        echo "Cached holiday files: " . count($files) . "\n";
        foreach ($files as $file) {
            $year = basename($file, '.json');
            $year = str_replace('holiday_', '', $year);
            echo "  - $year: " . basename($file) . "\n";
        }
    }
    
    // Demonstrate cache clearing
    echo "\nClearing cache for 2024...\n";
    $cleared = $nse->clearHolidayCache(2024);
    echo "Cache cleared: " . ($cleared ? 'Yes' : 'No') . "\n";
    
    // Check if file still exists
    $holidayFile = $cacheDir . '/holiday_2024.json';
    echo "2024 holiday file exists: " . (file_exists($holidayFile) ? 'Yes' : 'No') . "\n";
    
    echo "\n=== Cache Benefits ===\n";
    echo "✓ Reduces API calls to NSE\n";
    echo "✓ Improves performance for repeated requests\n";
    echo "✓ Automatically creates new files for each year\n";
    echo "✓ Cross-platform compatible (uses system temp directory)\n";
    echo "✓ Easy cache management with clearHolidayCache() method\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
