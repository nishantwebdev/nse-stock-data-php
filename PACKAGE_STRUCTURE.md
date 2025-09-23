# Package Structure

This document outlines the structure of the NSE India API PHP library package.

## Directory Structure

```
nse-stock-data-php/
├── src/                          # Core library source code
│   ├── stockclient.php          # Main StockClient class
│   ├── interfaces.php           # Data model classes
│   └── utils.php                # Utility functions
├── examples/                    # Usage examples
│   ├── basic_usage.php          # Basic API usage
│   ├── historical_data.php      # Historical data examples
│   └── option_chain_analysis.php # Option chain analysis
├── tests/                       # Unit tests
│   └── StockClientTest.php      # Main test class
├── composer.json                # Package configuration
├── phpunit.xml                  # PHPUnit configuration
├── README.md                     # Main documentation
├── CHANGELOG.md                  # Version history
guidelines
├── LICENSE                       # MIT License
├── .gitignore                    # Git ignore rules
└── PACKAGE_STRUCTURE.md          # This file
```

## Core Library Files

### `src/stockclient.php`
- Main `StockClient` class
- All API methods for data retrieval
- Connection management and rate limiting
- Cookie handling and session management

### `src/interfaces.php`
- Data model classes for API responses
- Type-safe data structures
- Comprehensive model coverage for all API endpoints

### `src/utils.php`
- Utility functions for date handling
- Helper functions for data processing
- Common utility methods

## Examples

### `examples/basic_usage.php`
Demonstrates basic API usage:
- Getting equity details
- Historical price data
- Derivative data
- Option chain data
- Holiday checking

### `examples/historical_data.php`
Shows historical data analysis:
- Date range handling
- Historical data retrieval
- Intraday data access


## Testing

### `tests/StockClientTest.php`
Comprehensive unit tests covering:
- Basic functionality
- Error handling
- Data validation
- API integration (with mocking)

## Package Configuration

### `composer.json`
- Package metadata and dependencies
- PSR-4 autoloading configuration
- Development dependencies
- Script definitions

### `phpunit.xml`
- PHPUnit configuration
- Test suite setup
- Coverage reporting

## Documentation

### `README.md`
- Installation instructions
- Usage examples
- API reference
- Feature overview

### `CHANGELOG.md`
- Version history
- Feature additions
- Bug fixes
- Breaking changes

### `CONTRIBUTING.md`
- Contribution guidelines
- Code standards
- Development setup
- Pull request process

## Key Features

### Core Library
- **Namespace**: `NseData`
- **PHP Version**: 8.0+
- **Dependencies**: GuzzleHttp, Carbon

### API Coverage
- Real-time stock data
- Historical data
- Holiday calendar

### Data Models
- Type-safe data structures
- Comprehensive model coverage
- Easy-to-use object properties
- Proper data validation

### Error Handling
- Exception-based error handling
- Meaningful error messages
- Graceful failure handling
- Retry logic for failed requests

### Performance
- Connection pooling
- Rate limiting
- Cookie management
- Efficient data processing

## Usage

### Installation
```bash
composer require nishantwebdev/nse-stock-data-php
```

### Basic Usage
```php
use NseData\StockClient;

$client = new StockClient();
$equityDetails = $nse->getEquityDetails('TCS');
```

### Advanced Usage
```php
use NseData\StockClient;
use NseData\DateRange;

$client = new StockClient();
$dateRange = new DateRange([
    'start' => new DateTime('2024-01-01'),
    'end' => new DateTime('2024-01-31')
]);
$historicalData = $nse->getEquityHistoricalData('TCS', $dateRange);
```

## Development

### Running Tests
```bash
composer test
```

### Code Standards
- PSR-12 coding standards
- Type hints throughout
- Comprehensive documentation
- Unit test coverage

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make changes with tests
4. Submit a pull request
