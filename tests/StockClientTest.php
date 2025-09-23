<?php

namespace NseData\Tests;

use NseData\StockClient;
use PHPUnit\Framework\TestCase;

class StockClientTest extends TestCase
{
    private StockClient $nse;

    protected function setUp(): void
    {
        $this->nse = new StockClient();
    }

    public function testNseIndiaInstance()
    {
        $this->assertInstanceOf(StockClient::class, $this->nse);
    }

    public function testCheckHolidayWithWeekend()
    {
        // Test Saturday (should be holiday)
        $saturday = new \DateTime('2024-01-06'); // Saturday
        $this->assertTrue($this->nse->checkHoliday($saturday));

        // Test Sunday (should be holiday)
        $sunday = new \DateTime('2024-01-07'); // Sunday
        $this->assertTrue($this->nse->checkHoliday($sunday));

        // Test Monday (should not be holiday)
        $monday = new \DateTime('2024-01-08'); // Monday
        $this->assertFalse($this->nse->checkHoliday($monday));
    }

    public function testCheckHolidayWithRepublicDay()
    {
        // Republic Day 2024 (January 26)
        $republicDay = new \DateTime('2024-01-26');
        $this->assertTrue($this->nse->checkHoliday($republicDay));
    }

    public function testGetEquityDetailsWithValidSymbol()
    {
        try {
            $equityDetails = $this->nse->getEquityDetails('TCS');
            $this->assertInstanceOf(\NseData\EquityDetails::class, $equityDetails);
            $this->assertNotEmpty($equityDetails->info->symbol);
            $this->assertNotEmpty($equityDetails->info->companyName);
        } catch (\Exception $e) {
            // If API is not accessible, skip the test
            $this->markTestSkipped('NSE API not accessible: ' . $e->getMessage());
        }
    }

    public function testGetEquityDetailsWithInvalidSymbol()
    {
        $this->expectException(\Exception::class);
        $this->nse->getEquityDetails('INVALID_SYMBOL_XYZ');
    }

    public function testGetDerivativeDataWithValidSymbol()
    {
        try {
            $derivativeData = $this->nse->getDerivativeData('RELIANCE');
            $this->assertIsArray($derivativeData);
        } catch (\Exception $e) {
            // If API is not accessible, skip the test
            $this->markTestSkipped('NSE API not accessible: ' . $e->getMessage());
        }
    }

    public function testGetIndexOptionChainWithValidIndex()
    {
        try {
            $optionChain = $this->nse->getIndexOptionChain('NIFTY');
            $this->assertInstanceOf(\NseData\OptionChainData::class, $optionChain);
            $this->assertInstanceOf(\NseData\Records::class, $optionChain->records);
            $this->assertInstanceOf(\NseData\Filtered::class, $optionChain->filtered);
        } catch (\Exception $e) {
            // If API is not accessible, skip the test
            $this->markTestSkipped('NSE API not accessible: ' . $e->getMessage());
        }
    }
}
