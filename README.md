# NSE India API - PHP Library

A comprehensive PHP library for accessing NSE (National Stock Exchange) India stock market data. This library provides easy-to-use methods to fetch real-time and historical stock data, and more.

## Features

- **Real-time Stock Data**: Get current prices, volume, and market data
- **Historical Data**: Fetch historical stock prices and trading data
- **Holiday Calendar**: Check trading holidays and market status
- **Intraday Data**: Access real-time intraday price movements


## Installation

### Using Composer (Recommended)

```bash
composer require nishantwebdev/nse-stock-data-php
```

### Manual Installation

1. Clone this repository:
```bash
git clone https://github.com/nishantwebdev/nse-stock-data-php.git
cd nse-stock-data-php
```

2. Install dependencies:
```bash
composer install
```

## Quick Start

```php
use NseData\StockClient;
use NseData\DateRange;

// Initialize the NSE client
$nse = new StockClient();

try {
    // Get equity details for a stock
    $equityDetails = $nse->getEquityDetails('TCS');
    echo "Company: " . $equityDetails->info->companyName . "\n";
    echo "Last Price: ₹" . $equityDetails->priceInfo->lastPrice . "\n";
    echo "Change: " . $equityDetails->priceInfo->change . " (" . $equityDetails->priceInfo->pChange . "%)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```
## Usage Examples

### Main Methods

#### `getEquityDetails(string $symbol): EquityDetails`
Get comprehensive equity information including price, volume, and company details.

#### `getEquityHistoricalData(string $symbol, DateRange $range): array`
Get historical stock data for a specified date range.

#### `getEquityIntradayData(string $symbol, bool $isPreOpenData = false): IntradayData`
Get real-time intraday price data.

#### `getIndexOptionChain(string $indexSymbol): OptionChainData`
Get option chain data for indices (NIFTY, BANKNIFTY, etc.).

#### `getEquityOptionChain(string $symbol): OptionChainData`
Get option chain data for individual stocks.

#### `getDerivativeData(string $symbol): array`
Get futures and options data for a stock.

#### `checkHoliday(DateTime $date): bool`
Check if a given date is a trading holiday.

#### `getAllStockSymbols(): array`
Get list of all available stock symbols.

#### `getHolidayData(): array`
Get holiday data for the current year (cached automatically).

#### `getHolidayDataForYear(int $year): array`
Get holiday data for a specific year.

#### `clearHolidayCache(?int $year = null): bool`
Clear holiday cache for a specific year or all years.

### Complete Method Documentation

#### Holiday and Market Status Methods

##### `checkHoliday(DateTime $date): bool`
Check if a given date is a trading holiday. Returns `true` if the date is a holiday (weekend or trading holiday), `false` otherwise.

**Parameters:**
- `$date` (DateTime): The date to check

**Returns:** bool - `true` if holiday, `false` if trading day

**Example:**
```php
$testDate = new DateTime('2024-01-26'); // Republic Day
$isHoliday = $nse->checkHoliday($testDate);
echo "Is " . $testDate->format('Y-m-d') . " a holiday? " . ($isHoliday ? 'Yes' : 'No');
```

##### `getMarketStatus(): MarketStatus`
Get current market status including market state, trade date, and other market information.

**Returns:** MarketStatus - Object containing market status details

**Example:**
```php
$marketStatus = $nse->getMarketStatus();
echo "Market State: " . $marketStatus->marketState;
echo "Trade Date: " . $marketStatus->tradeDate;
```

#### Index Methods

##### `getAllIndices(): array<Index>`
Get all available market indices with their current values and changes.

**Returns:** array<Index> - Array of Index objects containing index information

**Example:**
```php
$indices = $nse->getAllIndices();
foreach ($indices as $index) {
    echo "Index: " . $index->index . "\n";
    echo "Last: " . $index->last . "\n";
    echo "Change: " . $index->change . "\n";
}
```

##### `getEquityStockIndices(string $index): IndexDetails`
Get detailed information for a specific equity stock index.

**Parameters:**
- `$index` (string): The index symbol (e.g., 'NIFTY 50', 'BANK NIFTY')

**Returns:** IndexDetails - Object containing detailed index information

**Example:**
```php
$niftyDetails = $nse->getEquityStockIndices('NIFTY 50');
echo "Index: " . $niftyDetails->index;
echo "Last: " . $niftyDetails->last;
```

#### Equity Information Methods

##### `getEquityDetails(string $symbol): EquityDetails`
Get comprehensive equity information including price, volume, company details, and market data.

**Parameters:**
- `$symbol` (string): Stock symbol (e.g., 'TCS', 'RELIANCE')

**Returns:** EquityDetails - Object containing complete equity information

**Example:**
```php
$equityDetails = $nse->getEquityDetails('TCS');
echo "Company: " . $equityDetails->info->companyName;
echo "Last Price: ₹" . $equityDetails->priceInfo->lastPrice;
echo "Change: " . $equityDetails->priceInfo->change;
```

##### `getEquityTradeInfo(string $symbol): EquityTradeInfo`
Get detailed trade information for a specific equity including volume, value, and trade statistics.

**Parameters:**
- `$symbol` (string): Stock symbol

**Returns:** EquityTradeInfo - Object containing trade information

**Example:**
```php
$tradeInfo = $nse->getEquityTradeInfo('TCS');
echo "Total Traded Volume: " . $tradeInfo->totalTradedVolume;
echo "Total Traded Value: ₹" . $tradeInfo->totalTradedValue;
```

##### `getEquityCorporateInfo(string $symbol): EquityCorporateInfo`
Get corporate information for a specific equity including company details, announcements, and corporate actions.

**Parameters:**
- `$symbol` (string): Stock symbol

**Returns:** EquityCorporateInfo - Object containing corporate information

**Example:**
```php
$corporateInfo = $nse->getEquityCorporateInfo('TCS');
echo "Company Name: " . $corporateInfo->companyName;
echo "Industry: " . $corporateInfo->industry;
```

##### `getEquityIntradayData(string $symbol, bool $isPreOpenData = false): IntradayData`
Get real-time intraday price data for a specific equity.

**Parameters:**
- `$symbol` (string): Stock symbol
- `$isPreOpenData` (bool, optional): Whether to fetch pre-open market data (default: false)

**Returns:** IntradayData - Object containing intraday price data

**Example:**
```php
// Regular intraday data
$intradayData = $nse->getEquityIntradayData('TCS');

// Pre-open market data
$preOpenData = $nse->getEquityIntradayData('TCS', true);
```

#### Historical Data Methods

##### `getEquityHistoricalData(string $symbol, DateRange $range = null): array<EquityHistoricalData>`
Get historical stock data for a specified date range. If no range is provided, defaults to the last month.

**Parameters:**
- `$symbol` (string): Stock symbol
- `$range` (DateRange, optional): Date range for historical data

**Returns:** array<EquityHistoricalData> - Array of historical data objects

**Example:**
```php
use NseData\DateRange;

$startDate = new DateTime('2024-01-01');
$endDate = new DateTime('2024-01-31');
$dateRange = new DateRange(['start' => $startDate, 'end' => $endDate]);

$historicalData = $nse->getEquityHistoricalData('TCS', $dateRange);

foreach ($historicalData as $data) {
    foreach ($data->data as $record) {
        echo "Date: " . $record->CH_TIMESTAMP;
        echo "Open: ₹" . $record->CH_OPENING_PRICE;
        echo "High: ₹" . $record->CH_TRADE_HIGH_PRICE;
        echo "Low: ₹" . $record->CH_TRADE_LOW_PRICE;
        echo "Close: ₹" . $record->CH_CLOSING_PRICE;
        echo "Volume: " . $record->CH_TOT_TRADED_QTY;
    }
}
```

##### `getEquityPriceByDate(string $symbol, DateTime $date): int|float`
Get the closing price of a stock for a specific date. If the date is a holiday, returns the price from the last trading day.

**Parameters:**
- `$symbol` (string): Stock symbol
- `$date` (DateTime): The date to get price for

**Returns:** int|float - The closing price for the specified date

**Throws:** Exception if no data is found for the given date

**Example:**
```php
$priceDate = new DateTime('2024-01-15');
$price = $nse->getEquityPriceByDate('TCS', $priceDate);
echo "TCS price on " . $priceDate->format('Y-m-d') . ": ₹" . $price;
```

##### `getIndexHistoricalData(string $index, DateRange $range): array<IndexHistoricalData>`
Get historical data for a specific index over a date range.

**Parameters:**
- `$index` (string): Index symbol (e.g., 'NIFTY 50', 'BANK NIFTY')
- `$range` (DateRange): Date range for historical data

**Returns:** array<IndexHistoricalData> - Array of historical index data

**Example:**
```php
use NseData\DateRange;

$startDate = new DateTime('2024-01-01');
$endDate = new DateTime('2024-01-31');
$dateRange = new DateRange(['start' => $startDate, 'end' => $endDate]);

$indexData = $nse->getIndexHistoricalData('NIFTY 50', $dateRange);

foreach ($indexData as $data) {
    foreach ($data->data as $record) {
        echo "Date: " . $record->CH_TIMESTAMP;
        echo "Open: " . $record->CH_OPENING_INDEX_VALUE;
        echo "High: " . $record->CH_HIGH_INDEX_VALUE;
        echo "Low: " . $record->CH_LOW_INDEX_VALUE;
        echo "Close: " . $record->CH_CLOSING_INDEX_VALUE;
    }
}
```

#### Holiday Data Management Methods

##### `getHolidayData(): array`
Get holiday data for the current year. Data is automatically cached to improve performance.

**Returns:** array - Array of holiday dates in 'd-M-Y' format

**Example:**
```php
$holidays = $nse->getHolidayData();
foreach ($holidays as $holiday) {
    echo "Holiday: " . $holiday;
}
```

##### `clearHolidayCache(?int $year = null): bool`
Clear cached holiday data for a specific year or all years.

**Parameters:**
- `$year` (int|null, optional): Year to clear cache for. If null, clears all cached data.

**Returns:** bool - `true` if cache was cleared successfully

**Example:**
```php
// Clear cache for specific year
$nse->clearHolidayCache(2024);

// Clear all cached holiday data
$nse->clearHolidayCache();
```

## Data Models

The library provides strongly-typed data models for all API responses:

- `EquityDetails`: Complete equity information
- `EquityHistoricalData`: Historical price data
- `OptionChainData`: Option chain information
- `IntradayData`: Real-time price data
- `DateRange`: Date range specification

## Error Handling

The library throws exceptions for various error conditions:

```php
try {
    $equityDetails = $nse->getEquityDetails('INVALID_SYMBOL');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Rate Limiting

The library includes built-in rate limiting and connection management to respect NSE's API limits. It automatically handles:
- Connection pooling
- Request throttling
- Cookie management
- Retry logic

## Requirements

- PHP 8.0 or higher

## Examples

Check the `examples/` directory for more detailed usage examples:

- `basic_usage.php`: Basic stock data retrieval
- `historical_data.php`: Historical data analysis
- `option_chain_analysis.php`: Option chain analysis

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Disclaimer

This library is for educational and research purposes. Please ensure compliance with NSE's terms of service and applicable regulations when using this library for commercial purposes.

## Support

For issues and questions:
- Create an issue on GitHub
- Check the examples directory

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history.
