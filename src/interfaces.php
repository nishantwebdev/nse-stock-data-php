<?php

namespace NseData;

class MarketStatus
{
    public array $marketState;
    public MarketCap $marketCap;
    public GiftNifty $giftNifty;

    public function __construct(array $data)
    {
        $this->marketState = array_map(fn($state) => new MarketState($state), $data['marketState'] ?? []);
        $this->marketCap = new MarketCap($data['marketcap'] ?? []);
        $this->giftNifty = new GiftNifty($data['giftnifty'] ?? []);
    }
}

class MarketState 
{
    public string $market;
    public string $marketStatus; 
    public string $tradeDate;
    public string|null $index;
    public float|null $last;
    public float|null $variation;
    public float|null $percentChange;
    public string $marketStatusMessage;

    public function __construct(array $data)
    {
        $this->market = $data['market'] ?? '';
        $this->marketStatus = $data['marketStatus'] ?? '';
        $this->tradeDate = $data['tradeDate'] ?? '';
        $this->index = $data['index'] ?? null;
        $this->last = isset($data['last']) && $data['last'] !== '' ? (float)$data['last'] : null;
        $this->variation = isset($data['variation']) && $data['variation'] !== '' ? (float)$data['variation'] : null;
        $this->percentChange = isset($data['percentChange']) && $data['percentChange'] !== '' ? (float)$data['percentChange'] : null;
        $this->marketStatusMessage = $data['marketStatusMessage'] ?? '';
    }
}

class MarketCap
{
    public string $timeStamp;
    public float $marketCapInTrDollars;
    public float $marketCapInLacCrRupees;
    public float $marketCapInCrRupees;
    public string $marketCapInCrRupeesFormatted;
    public string $marketCapInLacCrRupeesFormatted;
    public string $underlying;

    public function __construct(array $data) 
    {
        $this->timeStamp = $data['timeStamp'] ?? '';
        $this->marketCapInTrDollars = $data['marketCapinTRDollars'] ?? 0.0;
        $this->marketCapInLacCrRupees = $data['marketCapinLACCRRupees'] ?? 0.0;
        $this->marketCapInCrRupees = $data['marketCapinCRRupees'] ?? 0.0;
        $this->marketCapInCrRupeesFormatted = $data['marketCapinCRRupeesFormatted'] ?? '';
        $this->marketCapInLacCrRupeesFormatted = $data['marketCapinLACCRRupeesFormatted'] ?? '';
        $this->underlying = $data['underlying'] ?? '';
    }
}

class GiftNifty
{
    public string $instrumentType;
    public string $symbol;
    public string $expiryDate;
    public string $optionType;
    public string $strikePrice;
    public float $lastPrice;
    public float $dayChange;
    public float $percentChange;
    public int $contractsTraded;
    public string $timestamp;
    public string $id;

    public function __construct(array $data)
    {
        $this->instrumentType = $data['INSTRUMENTTYPE'] ?? '';
        $this->symbol = $data['SYMBOL'] ?? '';
        $this->expiryDate = $data['EXPIRYDATE'] ?? '';
        $this->optionType = $data['OPTIONTYPE'] ?? '';
        $this->strikePrice = $data['STRIKEPRICE'] ?? '';
        $this->lastPrice = $data['LASTPRICE'] ?? 0.0;
        $this->dayChange = $data['DAYCHANGE'] ?? 0.0;
        $this->percentChange = $data['PERCHANGE'] ?? 0.0;
        $this->contractsTraded = $data['CONTRACTSTRADED'] ?? 0;
        $this->timestamp = $data['TIMESTMP'] ?? '';
        $this->id = $data['id'] ?? '';
    }
}


class DateRange
{
    public \DateTime $start;
    public \DateTime $end;

    public function __construct(array $data)
    {
        $this->start = $data['start'] ?? new \DateTime();
        $this->end = $data['end'] ?? new \DateTime();
    }
}

class EquityInfo
{
    public string $symbol;
    public string $companyName;
    public string $industry;
    public array $activeSeries;
    public array $debtSeries;
    public array $tempSuspendedSeries;
    public bool $isFNOSec;
    public bool $isCASec;
    public bool $isSLBSec;
    public bool $isDebtSec;
    public bool $isSuspended;
    public bool $isETFSec;
    public bool $isDelisted;
    public string $isin;
    public bool $isTop10;
    public string $identifier;

    public function __construct(array $data)
    {
        $this->symbol = $data['symbol'] ?? '';
        $this->companyName = $data['companyName'] ?? '';
        $this->industry = $data['industry'] ?? '';
        $this->activeSeries = $data['activeSeries'] ?? [];
        $this->debtSeries = $data['debtSeries'] ?? [];
        $this->tempSuspendedSeries = $data['tempSuspendedSeries'] ?? [];
        $this->isFNOSec = $data['isFNOSec'] ?? false;
        $this->isCASec = $data['isCASec'] ?? false;
        $this->isSLBSec = $data['isSLBSec'] ?? false;
        $this->isDebtSec = $data['isDebtSec'] ?? false;
        $this->isSuspended = $data['isSuspended'] ?? false;
        $this->isETFSec = $data['isETFSec'] ?? false;
        $this->isDelisted = $data['isDelisted'] ?? false;
        $this->isin = $data['isin'] ?? '';
        $this->isTop10 = $data['isTop10'] ?? false;
        $this->identifier = $data['identifier'] ?? '';
    }
}


class Records
{
    public array $expiryDates;
    public array $data; // Datum[]
    public string $timestamp;
    public float $underlyingValue;
    public array $strikePrices;

    public function __construct(array $data)
    {
        $this->expiryDates = $data['expiryDates'] ?? [];
        $this->data = array_map(fn ($item) => new Datum($item), $data['data'] ?? []);
        $this->timestamp = $data['timestamp'] ?? '';
        $this->underlyingValue = $data['underlyingValue'] ?? 0.0;
        $this->strikePrices = $data['strikePrices'] ?? [];
    }
}

class Filtered
{
    public array $data; // Datum[]
    public OptionsData $CE;
    public OptionsData $PE;

    public function __construct(array $data)
    {
        $this->data = array_map(fn ($item) => new Datum($item), $data['data'] ?? []);
        $this->CE = new OptionsData($data['CE'] ?? []);
        $this->PE = new OptionsData($data['PE'] ?? []);
    }
}

class OptionsData
{
    public float $totOI;
    public float $totVol;

    public function __construct(array $data)
    {
        $this->totOI = $data['totOI'] ?? 0.0;
        $this->totVol = $data['totVol'] ?? 0.0;
    }
}

class Datum
{
    public float $strikePrice;
    public string $expiryDate;
    public ?OptionsDetails $PE;
    public ?OptionsDetails $CE;

    public function __construct(array $data)
    {
        $this->strikePrice = $data['strikePrice'] ?? 0.0;
        $this->expiryDate = $data['expiryDate'] ?? '';
        $this->PE = isset($data['PE']) ? new OptionsDetails($data['PE']) : null;
        $this->CE = isset($data['CE']) ? new OptionsDetails($data['CE']) : null;
    }
}

class OptionsDetails
{
    public float $strikePrice;
    public string $expiryDate;
    public string $underlying; // Using string instead of enum for simplicity
    public string $identifier;
    public float $openInterest;
    public float $changeinOpenInterest;
    public float $pchangeinOpenInterest;
    public float $totalTradedVolume;
    public float $impliedVolatility;
    public float $lastPrice;
    public float $change;
    public float $pChange;
    public float $totalBuyQuantity;
    public float $totalSellQuantity;
    public float $bidQty;
    public float $bidprice;
    public float $askQty;
    public float $askPrice;
    public float $underlyingValue;

    public function __construct(array $data)
    {
        $this->strikePrice = $data['strikePrice'] ?? 0.0;
        $this->expiryDate = $data['expiryDate'] ?? '';
        $this->underlying = $data['underlying'] ?? '';
        $this->identifier = $data['identifier'] ?? '';
        $this->openInterest = $data['openInterest'] ?? 0.0;
        $this->changeinOpenInterest = $data['changeinOpenInterest'] ?? 0.0;
        $this->pchangeinOpenInterest = $data['pchangeinOpenInterest'] ?? 0.0;
        $this->totalTradedVolume = $data['totalTradedVolume'] ?? 0.0;
        $this->impliedVolatility = $data['impliedVolatility'] ?? 0.0;
        $this->lastPrice = $data['lastPrice'] ?? 0.0;
        $this->change = $data['change'] ?? 0.0;
        $this->pChange = $data['pChange'] ?? 0.0;
        $this->totalBuyQuantity = $data['totalBuyQuantity'] ?? 0.0;
        $this->totalSellQuantity = $data['totalSellQuantity'] ?? 0.0;
        $this->bidQty = $data['bidQty'] ?? 0.0;
        $this->bidprice = $data['bidprice'] ?? 0.0;
        $this->askQty = $data['askQty'] ?? 0.0;
        $this->askPrice = $data['askPrice'] ?? 0.0;
        $this->underlyingValue = $data['underlyingValue'] ?? 0.0;
    }
}

class EquityMetadata
{
    public string $series;
    public string $symbol;
    public string $isin;
    public string $status;
    public string $listingDate;
    public string $industry;
    public string $lastUpdateTime;
    public float $pdSectorPe;
    public float $pdSymbolPe;
    public string $pdSectorInd;

    public function __construct(array $data)
    {
        $this->series = $data['series'] ?? '';
        $this->symbol = $data['symbol'] ?? '';
        $this->isin = $data['isin'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->listingDate = $data['listingDate'] ?? '';
        $this->industry = $data['industry'] ?? '';
        $this->lastUpdateTime = $data['lastUpdateTime'] ?? '';
        $this->pdSectorPe = $data['pdSectorPe'] ?? 0.0;
        $this->pdSymbolPe = $data['pdSymbolPe'] ?? 0.0;
        $this->pdSectorInd = $data['pdSectorInd'] ?? '';
    }
}

class EquitySecurityInfo
{
    public string $boardStatus;
    public string $tradingStatus;
    public string $tradingSegment;
    public string $sessionNo;
    public string $slb;
    public string $classOfShare;
    public string $derivatives;
    public array $surveillance; // ['surv' => string, 'desc' => string]
    public float $faceValue;
    public float $issuedSize;

    public function __construct(array $data)
    {
        $this->boardStatus = $data['boardStatus'] ?? '';
        $this->tradingStatus = $data['tradingStatus'] ?? '';
        $this->tradingSegment = $data['tradingSegment'] ?? '';
        $this->sessionNo = $data['sessionNo'] ?? '';
        $this->slb = $data['slb'] ?? '';
        $this->classOfShare = $data['classOfShare'] ?? '';
        $this->derivatives = $data['derivatives'] ?? '';
        $this->surveillance = $data['surveillance'] ?? ['surv' => '', 'desc' => ''];
        $this->faceValue = $data['faceValue'] ?? 0.0;
        $this->issuedSize = $data['issuedSize'] ?? 0.0;
    }
}

class EquityPriceInfo
{
    public float $lastPrice;
    public float $change;
    public float $pChange;
    public float $previousClose;
    public float $open;
    public float $close;
    public float $vwap;
    public string $lowerCP;
    public string $upperCP;
    public string $pPriceBand;
    public float $basePrice;
    public array $intraDayHighLow; // {min: number, max: number, value: number}
    public array $weekHighLow; // {min: number, minDate: string, max: number, maxDate: string, value: number}

    public function __construct(array $data)
    {
        $this->lastPrice = $data['lastPrice'] ?? 0.0;
        $this->change = $data['change'] ?? 0.0;
        $this->pChange = $data['pChange'] ?? 0.0;
        $this->previousClose = $data['previousClose'] ?? 0.0;
        $this->open = $data['open'] ?? 0.0;
        $this->close = $data['close'] ?? 0.0;
        $this->vwap = $data['vwap'] ?? 0.0;
        $this->lowerCP = $data['lowerCP'] ?? '';
        $this->upperCP = $data['upperCP'] ?? '';
        $this->pPriceBand = $data['pPriceBand'] ?? '';
        $this->basePrice = $data['basePrice'] ?? 0.0;
        $this->intraDayHighLow = $data['intraDayHighLow'] ?? [];
        $this->weekHighLow = $data['weekHighLow'] ?? [];
    }
}

class PreOpenDetails
{
    public float $price;
    public float $buyQty;
    public float $sellQty;

    public function __construct(array $data)
    {
        $this->price = $data['price'] ?? 0.0;
        $this->buyQty = $data['buyQty'] ?? 0.0;
        $this->sellQty = $data['sellQty'] ?? 0.0;
    }
}

class EquityPreOpenMarket
{
    public array $preopen; // PreOpenDetails[]
    public array $ato; // {buy: number, sell: number}
    public float $IEP;
    public float $totalTradedVolume;
    public float $finalPrice;
    public float $finalQuantity;
    public string $lastUpdateTime;
    public float $totalBuyQuantity;
    public float $totalSellQuantity;
    public float $atoBuyQty;
    public float $atoSellQty;

    public function __construct(array $data)
    {
        $this->preopen = array_map(fn ($item) => new PreOpenDetails($item), $data['preopen'] ?? []);
        $this->ato = $data['ato'] ?? [];
        $this->IEP = $data['IEP'] ?? 0.0;
        $this->totalTradedVolume = $data['totalTradedVolume'] ?? 0.0;
        $this->finalPrice = $data['finalPrice'] ?? 0.0;
        $this->finalQuantity = $data['finalQuantity'] ?? 0.0;
        $this->lastUpdateTime = $data['lastUpdateTime'] ?? '';
        $this->totalBuyQuantity = $data['totalBuyQuantity'] ?? 0.0;
        $this->totalSellQuantity = $data['totalSellQuantity'] ?? 0.0;
        $this->atoBuyQty = $data['atoBuyQty'] ?? 0.0;
        $this->atoSellQty = $data['atoSellQty'] ?? 0.0;
    }
}

class EquityDetails
{
    public EquityInfo $info;
    public EquityMetadata $metadata;
    public EquitySecurityInfo $securityInfo;
    public EquityPriceInfo $priceInfo;
    public EquityPreOpenMarket $preOpenMarket;

    public function __construct(array $data)
    {
        $this->info = new EquityInfo($data['info'] ?? []);
        $this->metadata = new EquityMetadata($data['metadata'] ?? []);
        $this->securityInfo = new EquitySecurityInfo($data['securityInfo'] ?? []);
        $this->priceInfo = new EquityPriceInfo($data['priceInfo'] ?? []);
        $this->preOpenMarket = new EquityPreOpenMarket($data['preOpenMarket'] ?? []);
    }
}

class EquityTradeInfo
{
    public bool $noBlockDeals;
    public array $bulkBlockDeals; // {name: string}[]
    public array $marketDeptOrderBook; // Complex structure
    public array $securityWiseDP; // Complex structure

    public function __construct(array $data)
    {
        $this->noBlockDeals = $data['noBlockDeals'] ?? false;
        $this->bulkBlockDeals = $data['bulkBlockDeals'] ?? [];
        $this->marketDeptOrderBook = $data['marketDeptOrderBook'] ?? [];
        $this->securityWiseDP = $data['securityWiseDP'] ?? [];
    }
}

class DirectoryDetails
{
    public string $webAddress;
    public string $smName;
    public string $symbol;
    public string $office;
    public string $address;
    public string $city;
    public string $pincode;
    public string $telephone;
    public string $fax;
    public string $email;

    public function __construct(array $data)
    {
        $this->webAddress = $data['webAddress'] ?? '';
        $this->smName = $data['smName'] ?? '';
        $this->symbol = $data['symbol'] ?? '';
        $this->office = $data['office'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->city = $data['city'] ?? '';
        $this->pincode = $data['pincode'] ?? '';
        $this->telephone = $data['telephone'] ?? '';
        $this->fax = $data['fax'] ?? '';
        $this->email = $data['email'] ?? '';
    }
}

class EquityCorporateInfo
{
    public array $latest_announcements; // {data: {symbol: string, broadcastdate: string, subject: string}[]}
    public array $corporate_actions; // {data: {symbol: string, exdate: string, purpose: string}[]}
    public array $shareholdings_patterns; // {data: any}
    public array $financial_results; // Complex structure
    public array $borad_meeting; // {data: {symbol: string, purpose: string, meetingdate: string}[]}

    public function __construct(array $data)
    {
        $this->latest_announcements = $data['latest_announcements'] ?? [];
        $this->corporate_actions = $data['corporate_actions'] ?? [];
        $this->shareholdings_patterns = $data['shareholdings_patterns'] ?? [];
        $this->financial_results = $data['financial_results'] ?? [];
        $this->borad_meeting = $data['borad_meeting'] ?? [];
    }
}

class EquityHistoricalInfo
{
    public string $_id;
    public string $CH_SYMBOL;
    public string $CH_SERIES;
    public string $CH_MARKET_TYPE;
    public float $CH_TRADE_HIGH_PRICE;
    public float $CH_TRADE_LOW_PRICE;
    public float $CH_OPENING_PRICE;
    public float $CH_CLOSING_PRICE;
    public float $CH_LAST_TRADED_PRICE;
    public float $CH_PREVIOUS_CLS_PRICE;
    public float $CH_TOT_TRADED_QTY;
    public float $CH_TOT_TRADED_VAL;
    public float $CH_52WEEK_HIGH_PRICE;
    public float $CH_52WEEK_LOW_PRICE;
    public ?float $CH_TOTAL_TRADES;
    public string $CH_ISIN;
    public string $CH_TIMESTAMP;
    public string $TIMESTAMP;
    public string $createdAt;
    public string $updatedAt;
    public float $__v;
    public float $VWAP;
    public string $mTIMESTAMP;

    public function __construct(array $data)
    {
        $this->_id = $data['_id'] ?? '';
        $this->CH_SYMBOL = $data['CH_SYMBOL'] ?? '';
        $this->CH_SERIES = $data['CH_SERIES'] ?? '';
        $this->CH_MARKET_TYPE = $data['CH_MARKET_TYPE'] ?? '';
        $this->CH_TRADE_HIGH_PRICE = $data['CH_TRADE_HIGH_PRICE'] ?? 0.0;
        $this->CH_TRADE_LOW_PRICE = $data['CH_TRADE_LOW_PRICE'] ?? 0.0;
        $this->CH_OPENING_PRICE = $data['CH_OPENING_PRICE'] ?? 0.0;
        $this->CH_CLOSING_PRICE = $data['CH_CLOSING_PRICE'] ?? 0.0;
        $this->CH_LAST_TRADED_PRICE = $data['CH_LAST_TRADED_PRICE'] ?? 0.0;
        $this->CH_PREVIOUS_CLS_PRICE = $data['CH_PREVIOUS_CLS_PRICE'] ?? 0.0;
        $this->CH_TOT_TRADED_QTY = $data['CH_TOT_TRADED_QTY'] ?? 0.0;
        $this->CH_TOT_TRADED_VAL = $data['CH_TOT_TRADED_VAL'] ?? 0.0;
        $this->CH_52WEEK_HIGH_PRICE = $data['CH_52WEEK_HIGH_PRICE'] ?? 0.0;
        $this->CH_52WEEK_LOW_PRICE = $data['CH_52WEEK_LOW_PRICE'] ?? 0.0;
        $this->CH_TOTAL_TRADES = $data['CH_TOTAL_TRADES'] ?? null;
        $this->CH_ISIN = $data['CH_ISIN'] ?? '';
        $this->CH_TIMESTAMP = $data['CH_TIMESTAMP'] ?? '';
        $this->TIMESTAMP = $data['TIMESTAMP'] ?? '';
        $this->createdAt = $data['createdAt'] ?? '';
        $this->updatedAt = $data['updatedAt'] ?? '';
        $this->__v = $data['__v'] ?? 0.0;
        $this->VWAP = $data['VWAP'] ?? 0.0;
        $this->mTIMESTAMP = $data['mTIMESTAMP'] ?? '';
    }
}

class EquityHistoricalData
{
    public array $data; // EquityHistoricalInfo[]
    public array $meta; // {series: string[], fromDate: string, toDate: string, symbols: string[]}

    public function __construct(array $data)
    {
        $this->data = array_map(fn ($item) => new EquityHistoricalInfo($item), $data['data'] ?? []);
        $this->meta = $data['meta'] ?? [];
    }
}

class IndexHistoricalData
{
    public array $data; // Complex structure

    public function __construct(array $data)
    {
        $this->data = $data['data'] ?? [];
    }
}

class Index
{
    public string $name;
    public string $symbol;
    public float $open;
    public float $dayHigh;
    public float $dayLow;
    public float $lastPrice;
    public float $previousClose;

    public function __construct(array $data)
    {
         $this->name = $data['index'] ?? '';
         $this->symbol = $data['indexSymbol'] ?? '';
         $this->open = $data['open'] ?? 0.0;
         $this->dayHigh = $data['high'] ?? 0.0;
         $this->dayLow = $data['low'] ?? 0.0;
         $this->lastPrice = $data['last'] ?? 0.0;
         $this->previousClose = $data['previousClose'] ?? 0.0;
    }
}
class IndexEquityInfo
{
    public int $priority;
    public string $symbol;
    public string $identifier;
    public string $series;
    public float $open;
    public float $dayHigh;
    public float $dayLow;
    public float $lastPrice;
    public float $previousClose;
    public float $change;
    public float $pChange;
    public float $totalTradedVolume;
    public float $totalTradedValue;
    public string $lastUpdateTime;
    public float $yearHigh;
    public float $ffmc;
    public float $yearLow;
    public float $nearWKH;
    public float $nearWKL;
    public float $perChange365d;
    public string $date365dAgo;
    public string $chart365dPath;
    public string $date30dAgo;
    public float $perChange30d;
    public string $chart30dPath;
    public string $chartTodayPath;
    public array $meta; // Complex structure

    public function __construct(array $data)
    {
        $this->priority = $data['priority'] ?? 0;
        $this->symbol = $data['symbol'] ?? '';
        $this->identifier = $data['identifier'] ?? '';
        $this->series = $data['series'] ?? '';
        $this->open = $data['open'] ?? 0.0;
        $this->dayHigh = $data['dayHigh'] ?? 0.0;
        $this->dayLow = $data['dayLow'] ?? 0.0;
        $this->lastPrice = $data['lastPrice'] ?? 0.0;
        $this->previousClose = $data['previousClose'] ?? 0.0;
        $this->change = $data['change'] ?? 0.0;
        $this->pChange = $data['pChange'] ?? 0.0;
        $this->totalTradedVolume = $data['totalTradedVolume'] ?? 0.0;
        $this->totalTradedValue = $data['totalTradedValue'] ?? 0.0;
        $this->lastUpdateTime = $data['lastUpdateTime'] ?? '';
        $this->yearHigh = $data['yearHigh'] ?? 0.0;
        $this->ffmc = $data['ffmc'] ?? 0.0;
        $this->yearLow = $data['yearLow'] ?? 0.0;
        $this->nearWKH = $data['nearWKH'] ?? 0.0;
        $this->nearWKL = $data['nearWKL'] ?? 0.0;
        $this->perChange365d = $data['perChange365d'] ?? 0.0;
        $this->date365dAgo = $data['date365dAgo'] ?? '';
        $this->chart365dPath = $data['chart365dPath'] ?? '';
        $this->date30dAgo = $data['date30dAgo'] ?? '';
        $this->perChange30d = $data['perChange30d'] ?? 0.0;
        $this->chart30dPath = $data['chart30dPath'] ?? '';
        $this->chartTodayPath = $data['chartTodayPath'] ?? '';
        $this->meta = $data['meta'] ?? [];
    }
}

class IndexDetails
{
    public string $name;
    public array $advance; // {declines: string, advances: string, unchanged: string}
    public string $timestamp;
    public array $data; // IndexEquityInfo[]
    public array $metadata; // Complex structure
    public array $marketStatus; // Complex structure
    public string $date30dAgo;
    public string $date365dAgo;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->advance = $data['advance'] ?? [];
        $this->timestamp = $data['timestamp'] ?? '';
        $this->data = array_map(fn ($item) => new IndexEquityInfo($item), $data['data'] ?? []);
        $this->metadata = $data['metadata'] ?? [];
        $this->marketStatus = $data['marketStatus'] ?? [];
        $this->date30dAgo = $data['date30dAgo'] ?? '';
        $this->date365dAgo = $data['date365dAgo'] ?? '';
    }
}
