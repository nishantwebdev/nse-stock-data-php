# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-XX

### Added
- Initial release of NSE India API PHP library
- Core NseIndia class with comprehensive API methods
- Support for equity data retrieval (real-time and historical)
- Option chain data for indices and stocks
- Derivatives data access
- Holiday calendar and trading status checking
- Intraday data access
- Corporate actions and announcements
- Comprehensive data models with type safety
- Built-in rate limiting and connection management
- Cookie management for session handling
- Retry logic for failed requests
- Example usage scripts
- Web interface for data export
- MIT license
- Comprehensive documentation

### Features
- **Real-time Stock Data**: Current prices, volume, market data
- **Historical Data**: Historical stock prices and trading data
- **Option Chains**: Detailed option chain data for indices and stocks
- **Derivatives Data**: Futures and options data
- **Holiday Calendar**: Trading holidays and market status
- **Intraday Data**: Real-time price movements
- **Corporate Actions**: Corporate announcements and actions

### Technical Details
- PHP 8.0+ requirement
- PSR-4 autoloading
- Namespace: `NseApi`
- Dependencies: GuzzleHttp, Carbon
- Optional: PhpSpreadsheet for Excel export

### API Methods
- `getEquityDetails()` - Get comprehensive equity information
- `getEquityHistoricalData()` - Get historical stock data
- `getEquityIntradayData()` - Get real-time intraday data
- `getIndexOptionChain()` - Get index option chain data
- `getEquityOptionChain()` - Get stock option chain data
- `getDerivativeData()` - Get futures and options data
- `checkHoliday()` - Check trading holidays
- `getAllStockSymbols()` - Get all available symbols

### Data Models
- `EquityDetails` - Complete equity information
- `EquityHistoricalData` - Historical price data
- `OptionChainData` - Option chain information
- `IntradayData` - Real-time price data
- `DateRange` - Date range specification
- And many more specialized models

### Examples
- Basic usage examples
- Historical data analysis
- Option chain analysis
- Web interface for data export

## [Unreleased]

### Planned Features
- Unit tests
- More comprehensive error handling
- Caching support
- Additional data endpoints
- Performance optimizations
