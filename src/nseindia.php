<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/interfaces.php';

enum ApiList: string
{
    case GLOSSARY = '/api/cmsContent?url=/glossary';
    case HOLIDAY_TRADING = '/api/holiday-master?type=trading';
    case HOLIDAY_CLEARING = '/api/holiday-master?type=clearing';
    case MARKET_STATUS = '/api/marketStatus';
    case MARKET_TURNOVER = '/api/market-turnover';
    case ALL_INDICES = '/api/allIndices';
    case INDEX_NAMES = '/api/index-names';
    case CIRCULARS = '/api/circulars';
    case LATEST_CIRCULARS = '/api/latest-circular';
    case EQUITY_MASTER = '/api/equity-master';
    case MARKET_DATA_PRE_OPEN = '/api/market-data-pre-open?key=ALL';
    case MERGED_DAILY_REPORTS_CAPITAL = '/api/merged-daily-reports?key=favCapital';
    case MERGED_DAILY_REPORTS_DERIVATIVES = '/api/merged-daily-reports?key=favDerivatives';
    case MERGED_DAILY_REPORTS_DEBT = '/api/merged-daily-reports?key=favDebt';
}

class NseIndia
{
    private string $baseUrl = 'https://www.nseindia.com';
    private int $cookieMaxAge = 60; // in seconds
    private array $baseHeaders = [
        'Authority' => 'www.nseindia.com',
        'Referer' => 'https://www.nseindia.com/',
        'Accept' => '*/*',
        'Origin' => 'https://www.nseindia.com',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Accept-Encoding' => 'application/json, text/plain, */*',
        'Connection' => 'keep-alive'
    ];
    private string $userAgent = '';
    private string $cookies = '';
    private int $cookieUsedCount = 0;
    private int $cookieExpiry = 0;
    private int $noOfConnections = 0;
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => $this->baseUrl]);
        $this->cookieExpiry = time() + $this->cookieMaxAge;
    }

    private function getNseCookies(): string
    {
        if ($this->cookies === '' || $this->cookieUsedCount > 10 || $this->cookieExpiry <= time()) {
            $this->userAgent = $this->generateUserAgent();
            try {
                $response = $this->client->get('/get-quotes/equity?symbol=TCS', [
                    'headers' => array_merge($this->baseHeaders, ['User-Agent' => $this->userAgent])
                ]);
                $setCookies = $response->getHeader('set-cookie');
                $cookies = [];
                foreach ($setCookies as $cookie) {
                    $cookieKeyValue = explode(';', $cookie)[0];
                    $cookies[] = $cookieKeyValue;
                }
                $this->cookies = implode('; ', $cookies);
                $this->cookieUsedCount = 0;
                $this->cookieExpiry = time() + $this->cookieMaxAge;
            } catch (RequestException $e) {
                throw new Exception('Failed to fetch cookies: ' . $e->getMessage());
            }
        }
        $this->cookieUsedCount++;
        return $this->cookies;
    }

    private function generateUserAgent(): string
    {
        // Simple user-agent generator (replace with a more robust solution if needed)
        return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    }

    /**
     * Fetch data from NSE India API.
     *
     * @param string $url
     * @return mixed
     * @throws Exception
     */
    public function getData(string $url): mixed
    {
        $retries = 0;
        $hasError = false;

        do {
            while ($this->noOfConnections >= 5) {
                sleepMilliseconds(500);
            }
            $this->noOfConnections++;
            try {
                $response = $this->client->get($url, [
                    'headers' => array_merge($this->baseHeaders, [
                        'Cookie' => $this->getNseCookies(),
                        'User-Agent' => $this->userAgent
                    ])
                ]);
                $this->noOfConnections--;
                return json_decode($response->getBody()->getContents(), true);
            } catch (RequestException $e) {
                $hasError = true;
                $retries++;
                $this->noOfConnections--;
                if ($retries >= 10) {
                    throw new Exception('Failed to fetch data after retries: ' . $e->getMessage());
                }
            }
        } while ($hasError);

        return null;
    }

    /**
     * Fetch data by API endpoint.
     *
     * @param string $apiEndpoint
     * @return mixed
     */
    public function getDataByEndpoint(string $apiEndpoint): mixed
    {
        return $this->getData($this->baseUrl . $apiEndpoint);
    }


    /**
     * Fetch holiday data by API endpoint.
     *
     * @return array<string>
     */
    public function getHolidayData(): array
    {
        $year = date('Y');
        $holidayFile = __DIR__ . '/assets/json/holiday_' . $year . '.json';
        if (!file_exists($holidayFile)) {
            $holidayData = $this->getDataByEndpoint(ApiList::HOLIDAY_TRADING->value);

            if (!is_array($holidayData) || empty($holidayData)) {
                throw new Exception('Failed to fetch holiday data');
            }
            $holidayData = $holidayData['CBM'] ?? [];
            if (!is_array($holidayData)) {
                throw new Exception('Invalid holiday data format');
            }
            // Save the holiday data to a file
            $holidayData = array_map(fn ($item) => $item['tradingDate'], $holidayData);
            // Ensure the directory exists
            if (!is_dir(dirname($holidayFile))) {
                mkdir(dirname($holidayFile), 0777, true);
            }
            file_put_contents($holidayFile, json_encode($holidayData));
        } else {
            $holidayData = json_decode(file_get_contents($holidayFile), true);
        }
        return $holidayData;
    }

    /**
     * Check if the given date is a holiday.
     *
     * @param DateTime $date
     * @return bool
     */
    public function checkHoliday(DateTime $date): bool
    {
        // Check if Saturday (6) or Sunday (0)
        $dayOfWeek = (int)$date->format('w');
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return true;
        }
        $holidayData = $this->getHolidayData();
        // Convert input date to 'd-M-Y' format (e.g., 05-Aug-2025)
        $dateStr = $date->format('d-M-Y');
        return in_array($dateStr, $holidayData, true);
    }

    /**
     * Get all stock symbols.
     *
     * @return array<string>
     */
    public function getAllStockSymbols(): array
    {
        $data = $this->getDataByEndpoint(ApiList::MARKET_DATA_PRE_OPEN->value);
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
        $symbols = array_map(fn ($obj) => $obj['metadata']['symbol'], $data);
        sort($symbols);
        return $symbols;
    }

    /**
     * Get equity details.
     *
     * @param string $symbol
     * @return EquityDetails
     */
    public function getEquityDetails(string $symbol): EquityDetails
    {
        $data = $this->getDataByEndpoint('/api/quote-equity?symbol=' . urlencode(strtoupper($symbol)));
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit;
        return new EquityDetails($data);
    }

    /**
     * Get equity trade info.
     *
     * @param string $symbol
     * @return EquityTradeInfo
     */
    public function getEquityTradeInfo(string $symbol): EquityTradeInfo
    {
        $data = $this->getDataByEndpoint('/api/quote-equity?symbol=' . urlencode(strtoupper($symbol)) . '&section=trade_info');
        return new EquityTradeInfo($data);
    }

    /**
     * Get equity corporate info.
     *
     * @param string $symbol
     * @return EquityCorporateInfo
     */
    public function getEquityCorporateInfo(string $symbol): EquityCorporateInfo
    {
        $data = $this->getDataByEndpoint('/api/top-corp-info?symbol=' . urlencode(strtoupper($symbol)) . '&market=equities');
        return new EquityCorporateInfo($data);
    }

    /**
     * Get equity intraday data.
     *
     * @param string $symbol
     * @param bool $isPreOpenData
     * @return IntradayData
     */
    public function getEquityIntradayData(string $symbol, bool $isPreOpenData = false): IntradayData
    {
        $details = $this->getEquityDetails(strtoupper($symbol));
        $identifier = $details->info->identifier;
        $url = '/api/chart-databyindex?index=' . $identifier;
        if ($isPreOpenData) {
            $url .= '&preopen=true';
        }
        $data = $this->getDataByEndpoint($url);
        return new IntradayData($data);
    }

    /**
     * Get equity historical data.
     *
     * @param string $symbol
     * @param DateRange|null $range
     * @return array<EquityHistoricalData>
     */
    public function getEquityHistoricalData(string $symbol, ?DateRange $range = null): array
    {
        $data = $this->getEquityDetails(strtoupper($symbol));
        $activeSeries = !empty($data->info->activeSeries) ? $data->info->activeSeries[0] : 'EQ';
        if ($range === null) {
            $range = new DateRange([
            'start' => new DateTime($data->metadata->listingDate),
            'end' => new DateTime()
            ]);
        }
        $dateRanges = getDateRangeChunks($range->start, $range->end, 66);
        $promises = array_map(function ($dateRange) use ($symbol, $activeSeries) {
            $url = '/api/historical/cm/equity?symbol=' . urlencode(strtoupper($symbol)) .
               '&series=["' . $activeSeries . '"]&from=' . $dateRange['start'] . '&to=' . $dateRange['end'];
            return $this->getDataByEndpoint($url);
        }, $dateRanges);
        return array_map(fn ($data) => new EquityHistoricalData($data), $promises);
    }

    /**
     * Get equity price by date.
     *
     * @param string $symbol
     * @param DateTime $date
     * @return int|float
     * @throws Exception
     */
    public function getEquityPriceByDate(string $symbol, DateTime $date): int|float
    {
        // if date is today then return the current price
        if ($date->format('d-m-Y') === date('d-m-Y')) {
            $equityDetails = $this->getEquityDetails($symbol);

            if (isset($equityDetails->priceInfo->lastPrice)) {
                return $equityDetails->priceInfo->lastPrice;
            }
            throw new Exception('No last price available for today');
        }
        // Check if the date is a holiday if it is holiday then consider the last trading day which is not a holiday
        // Loop backwards until a non-holiday (trading) day is found
        while ($this->checkHoliday($date)) {
            $date->modify('-1 day');
        }
        // Fetch the historical data for the given date
        $url = '/api/historical/cm/equity?symbol=' . urlencode(strtoupper($symbol)) .
               '&series=["EQ"]&from=' . $date->format('d-m-Y') . '&to=' . $date->format('d-m-Y');
        $equityData = $this->getDataByEndpoint($url);
        if (empty($equityData['data']) || !isset($equityData['data'][0]['CH_LAST_TRADED_PRICE'])) {
            throw new Exception('No data found for the given date: ' . $date->format('d-m-Y'));
        }
        // Return the last traded price for the given date
        return $equityData["data"][0]["CH_LAST_TRADED_PRICE"] ?? 0;
    }
    /**
     * Get derivative data for a given symbol.
     *
     * @param string $symbol
     * @return mixed
     */
    public function getDerivativeData(string $symbol): mixed
    {
        $endpoint = '/api/quote-derivative?symbol=' . urlencode(strtoupper($symbol));
        $derivativeData = $this->getDataByEndpoint($endpoint);

        // Filter only "Stock Futures" and map to required structure
        $futuresData = [];
        foreach ($derivativeData['stocks'] as $stock) {
            if (
                isset($stock['metadata']['instrumentType']) &&
                $stock['metadata']['instrumentType'] === 'Stock Futures' &&
                isset($stock['metadata']['expiryDate'], $stock['metadata']['lastPrice'])
            ) {
                $futuresData[$stock['metadata']['expiryDate']] = $stock['metadata']['lastPrice'];
            }
        }
        return $futuresData;
    }
    /**
     * Get equity series.
     *
     * @param string $symbol
     * @return SeriesData
     */
    public function getEquitySeries(string $symbol): SeriesData
    {
        $data = $this->getDataByEndpoint('/api/historical/cm/equity/series?symbol=' . urlencode(strtoupper($symbol)));
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit;
        return new SeriesData($data);
    }

    /**
     * Get equity stock indices.
     *
     * @param string $index
     * @return IndexDetails
     */
    public function getEquityStockIndices(string $index): IndexDetails
    {
        $data = $this->getDataByEndpoint('/api/equity-stockIndices?index=' . urlencode(strtoupper($index)));
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
        return new IndexDetails($data);
    }

    /**
     * Get index intraday data.
     *
     * @param string $index
     * @param bool $isPreOpenData
     * @return IntradayData
     */
    public function getIndexIntradayData(string $index, bool $isPreOpenData = false): IntradayData
    {
        $endpoint = '/api/chart-databyindex?index=' . urlencode(strtoupper($index)) . '&indices=true';
        if ($isPreOpenData) {
            $endpoint .= '&preopen=true';
        }
        $data = $this->getDataByEndpoint($endpoint);
        return new IntradayData($data);
    }

    /**
     * Get index historical data.
     *
     * @param string $index
     * @param DateRange $range
     * @return array<IndexHistoricalData>
     */
    public function getIndexHistoricalData(string $index, DateRange $range): array
    {
        $dateRanges = getDateRangeChunks($range->start, $range->end, 66);
        $promises = array_map(function ($dateRange) use ($index) {
            $url = '/api/historical/indicesHistory?indexType=' . urlencode(strtoupper($index)) .
                   '&from=' . $dateRange['start'] . '&to=' . $dateRange['end'];
            return $this->getDataByEndpoint($url);
        }, $dateRanges);
        return array_map(fn ($data) => new IndexHistoricalData($data), $promises);
    }

    /**
     * Get index option chain.
     *
     * @param string $indexSymbol
     * @return OptionChainData
     */
    public function getIndexOptionChain(string $indexSymbol): OptionChainData
    {
        $data = $this->getDataByEndpoint('/api/option-chain-indices?symbol=' . urlencode(strtoupper($indexSymbol)));
        return new OptionChainData($data);
    }

    /**
     * Get equity option chain.
     *
     * @param string $symbol
     * @return OptionChainData
     */
    public function getEquityOptionChain(string $symbol): OptionChainData
    {
        $data = $this->getDataByEndpoint('/api/option-chain-equities?symbol=' . urlencode(strtoupper($symbol)));
        return new OptionChainData($data);
    }

    /**
     * Get commodity option chain.
     *
     * @param string $symbol
     * @return OptionChainData
     */
    public function getCommodityOptionChain(string $symbol): OptionChainData
    {
        $data = $this->getDataByEndpoint('/api/option-chain-com?symbol=' . urlencode(strtoupper($symbol)));
        return new OptionChainData($data);
    }
}
