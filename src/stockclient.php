<?php

namespace NseData;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Include the interfaces and utility functions
require_once __DIR__ . '/interfaces.php';
require_once __DIR__ . '/utils.php';


class StockClient
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
        $holidayFile = $this->getHolidayFilePath($year);
        
        if (!file_exists($holidayFile)) {
            $holidayData = $this->getDataByEndpoint('/api/holiday-master?type=trading');

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
     * Get holiday file path for a specific year.
     *
     * @param int $year
     * @return string
     */
    private function getHolidayFilePath(int $year): string
    {
        // Use sys_get_temp_dir() for better cross-platform compatibility
        $cacheDir = sys_get_temp_dir() . '/nse-india-api/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        return $cacheDir . '/holiday_' . $year . '.json';
    }

    /**
     * Clear holiday cache for a specific year or all years.
     *
     * @param int|null $year If null, clears all cached holiday data
     * @return bool
     */
    public function clearHolidayCache(?int $year = null): bool
    {
        $cacheDir = sys_get_temp_dir() . '/nse-india-api/cache';
        
        if ($year === null) {
            // Clear all holiday files
            $files = glob($cacheDir . '/holiday_*.json');
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            return true;
        } else {
            // Clear specific year
            $holidayFile = $this->getHolidayFilePath($year);
            if (file_exists($holidayFile)) {
                return unlink($holidayFile);
            }
            return true;
        }
    }

    /**
     * Check if the given date is a holiday.
     *
     * @param \DateTime $date
     * @return bool
     */
    public function checkHoliday(\DateTime $date): bool
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
     * Get market status.
     *
     * @return MarketStatus
     */
    public function getMarketStatus(): MarketStatus
    {
        $data = $this->getDataByEndpoint('/api/marketStatus');
        return new MarketStatus($data);
    }

    /**
     * Get all indices.
     *
     * @return array<Index>
     */

    public function getAllIndices(): array
    {
        $data = $this->getDataByEndpoint('/api/allIndices');
        return array_map(fn ($item) => new Index($item), $data['data']);
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
            'start' => (new \DateTime())->modify('-1 month'),
            'end' => new \DateTime()
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
     * @param \DateTime $date
     * @return int|float
     * @throws Exception
     */
    public function getEquityPriceByDate(string $symbol, \DateTime $date): int|float
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
     * Get equity stock indices.
     *
     * @param string $index
     * @return IndexDetails
     */
    public function getEquityStockIndices(string $index): IndexDetails
    {
        $data = $this->getDataByEndpoint('/api/equity-stockIndices?index=' . urlencode(strtoupper($index)));
        return new IndexDetails($data);
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

}
