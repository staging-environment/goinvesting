<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class YahooFinanceService
{
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';

    /**
     * Search for assets using autocomplete endpoint.
     */
    public function search(string $query)
    {
        return Cache::remember("yahoo_search_" . urlencode($query), 300, function () use ($query) {
            try {
                $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                    ->timeout(10)
                    ->get("https://query2.finance.yahoo.com/v1/finance/search", [
                        'q' => $query,
                        'quotesCount' => 10,
                        'newsCount' => 0
                    ]);

                if ($response->successful()) {
                    return $response->json()['quotes'] ?? [];
                }
            } catch (\Exception $e) {
                Log::error("Yahoo Finance Search error: " . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Fetch quotes and day spark data for multiple symbols.
     */
    public function getSparkQuotes(array $symbols)
    {
        if (empty($symbols)) {
            return [];
        }

        // Cache based on the exact list of symbols requested
        $symbolsStr = implode(',', $symbols);
        $cacheKey = "yahoo_spark_" . md5($symbolsStr);

        return Cache::remember($cacheKey, 15, function () use ($symbols) {
            // Yahoo Spark API has a strict limit of 20 symbols per request.
            // We batch them in chunks of 15 to be safe.
            $chunks = array_chunk($symbols, 15);
            $results = [];

            try {
                foreach ($chunks as $chunk) {
                    $chunkStr = implode(',', $chunk);
                    $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                        ->timeout(10)
                        ->get("https://query1.finance.yahoo.com/v7/finance/spark", [
                            'symbols' => $chunkStr
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['spark']['result'])) {
                            foreach ($data['spark']['result'] as $item) {
                                $symbol = $item['symbol'];
                                if (isset($item['response'][0])) {
                                    $res = $item['response'][0];
                                    $meta = $res['meta'] ?? [];
                                    $indicators = $res['indicators']['quote'][0]['close'] ?? [];
                                    
                                    // Clean nulls from sparkline close values
                                    $sparkline = array_values(array_filter($indicators, fn($v) => !is_null($v)));

                                    $price = $meta['regularMarketPrice'] ?? null;
                                    $prevClose = $meta['chartPreviousClose'] ?? null;
                                    
                                    $change = null;
                                    $changePercent = null;
                                    if ($price && $prevClose) {
                                        $change = $price - $prevClose;
                                        $changePercent = ($change / $prevClose) * 100;
                                    }

                                    $results[$symbol] = [
                                        'symbol' => $symbol,
                                        'price' => $price,
                                        'change' => $change,
                                        'changePercent' => $changePercent,
                                        'currency' => $meta['currency'] ?? 'USD',
                                        'exchange' => $meta['exchangeName'] ?? '',
                                        'instrumentType' => $meta['instrumentType'] ?? '',
                                        'longName' => $meta['longName'] ?? $meta['shortName'] ?? $symbol,
                                        'shortName' => $meta['shortName'] ?? $symbol,
                                        'sparkline' => $sparkline,
                                        'dayHigh' => $meta['regularMarketDayHigh'] ?? null,
                                        'dayLow' => $meta['regularMarketDayLow'] ?? null,
                                        'volume' => $meta['regularMarketVolume'] ?? null,
                                    ];
                                }
                            }
                        }
                    }
                }
                return $results;
            } catch (\Exception $e) {
                Log::error("Yahoo Finance Spark error: " . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Fetch historical chart data for details page.
     */
    public function getChartData(string $symbol, string $range = '1d', string $interval = '5m')
    {
        $cacheKey = "yahoo_chart_{$symbol}_{$range}_{$interval}";
        $cacheTime = ($range === '1d') ? 15 : 300; // Cache 1d chart shorter than historical charts

        return Cache::remember($cacheKey, $cacheTime, function () use ($symbol, $range, $interval) {
            try {
                $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                    ->timeout(10)
                    ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}", [
                        'range' => $range,
                        'interval' => $interval
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['chart']['result'][0])) {
                        $result = $data['chart']['result'][0];
                        $meta = $result['meta'] ?? [];
                        $timestamps = $result['timestamp'] ?? [];
                        $quote = $result['indicators']['quote'][0] ?? [];

                        $opens = $quote['open'] ?? [];
                        $highs = $quote['high'] ?? [];
                        $lows = $quote['low'] ?? [];
                        $closes = $quote['close'] ?? [];
                        $volumes = $quote['volume'] ?? [];

                        $candles = [];
                        foreach ($timestamps as $index => $timestamp) {
                            if (
                                !isset($closes[$index]) || is_null($closes[$index]) ||
                                !isset($opens[$index]) || is_null($opens[$index])
                            ) {
                                continue;
                            }
                            $candles[] = [
                                'time' => $timestamp,
                                'open' => (float)$opens[$index],
                                'high' => (float)($highs[$index] ?? $closes[$index]),
                                'low' => (float)($lows[$index] ?? $closes[$index]),
                                'close' => (float)$closes[$index],
                                'volume' => (int)($volumes[$index] ?? 0),
                            ];
                        }

                        $prevClose = $meta['chartPreviousClose'] ?? null;
                        $price = $meta['regularMarketPrice'] ?? null;
                        $change = null;
                        $changePercent = null;
                        if ($price && $prevClose) {
                            $change = $price - $prevClose;
                            $changePercent = ($change / $prevClose) * 100;
                        }

                        return [
                            'symbol' => $symbol,
                            'longName' => $meta['longName'] ?? $meta['shortName'] ?? $symbol,
                            'shortName' => $meta['shortName'] ?? $symbol,
                            'currency' => $meta['currency'] ?? 'USD',
                            'exchange' => $meta['exchangeName'] ?? '',
                            'price' => $price,
                            'change' => $change,
                            'changePercent' => $changePercent,
                            'dayHigh' => $meta['regularMarketDayHigh'] ?? null,
                            'dayLow' => $meta['regularMarketDayLow'] ?? null,
                            'fiftyTwoWeekHigh' => $meta['fiftyTwoWeekHigh'] ?? null,
                            'fiftyTwoWeekLow' => $meta['fiftyTwoWeekLow'] ?? null,
                            'volume' => $meta['regularMarketVolume'] ?? null,
                            'candles' => $candles,
                            'validRanges' => $meta['validRanges'] ?? ['1d', '5d', '1m', '3m', '6m', '1y', '2y', '5y', '10y', 'ytd', 'max']
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Yahoo Finance Chart error: " . $e->getMessage());
            }
            return null;
        });
    }
}
