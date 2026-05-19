<?php

namespace App\Services;

/**
 * Serviço de Taxas de Câmbio - Integração com API externa
 * Usa a API gratuita: https://open.er-api.com
 */
class ExchangeRateService
{
    private string $baseUrl = 'https://open.er-api.com/v6/latest/';
    private int $cacheTtl = 3600; // 1 hora

    /**
     * Obter taxas de câmbio para uma moeda base
     */
    public function getRates(string $baseCurrency = 'USD'): ?array
    {
        try {
            $cacheFile = __DIR__ . '/../../cache/exchange_rates_' . strtolower($baseCurrency) . '.json';

            // Verificar cache
            if (file_exists($cacheFile)) {
                $cached = json_decode(file_get_contents($cacheFile), true);
                if ($cached && (time() - ($cached['cached_at'] ?? 0)) < $this->cacheTtl) {
                    return $cached;
                }
            }

            // Chamar API externa
            $url = $this->baseUrl . strtoupper($baseCurrency);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header' => 'User-Agent: SmartBudget/1.0'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                error_log("ExchangeRate API: Failed to fetch rates for {$baseCurrency}");
                return $this->getCachedOrDefault($baseCurrency);
            }

            $data = json_decode($response, true);
            if (!$data || ($data['result'] ?? '') !== 'success') {
                error_log("ExchangeRate API: Invalid response");
                return $this->getCachedOrDefault($baseCurrency);
            }

            // Guardar em cache
            $data['cached_at'] = time();
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cacheFile, json_encode($data));

            return $data;

        } catch (\Exception $e) {
            error_log("ExchangeRate error: " . $e->getMessage());
            return $this->getCachedOrDefault($baseCurrency);
        }
    }

    /**
     * Converter valor entre moedas
     */
    public function convert(float $amount, string $from, string $to): ?array
    {
        $rates = $this->getRates($from);
        if (!$rates || !isset($rates['rates'][$to])) {
            return null;
        }

        $rate = (float) $rates['rates'][$to];
        $converted = round($amount * $rate, 2);

        return [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'rate' => $rate,
            'converted' => $converted,
            'last_updated' => $rates['time_last_update_utc'] ?? null
        ];
    }

    /**
     * Obter taxas filtradas para moedas suportadas
     */
    public function getSupportedRates(string $baseCurrency = 'USD'): array
    {
        $supported = ['BRL', 'USD', 'EUR', 'GBP', 'JPY', 'ARS', 'CLP', 'COP', 'MXN'];
        $rates = $this->getRates($baseCurrency);

        if (!$rates || !isset($rates['rates'])) {
            return [];
        }

        $filtered = [];
        foreach ($supported as $currency) {
            if (isset($rates['rates'][$currency])) {
                $filtered[$currency] = $rates['rates'][$currency];
            }
        }

        return [
            'base' => $baseCurrency,
            'rates' => $filtered,
            'last_updated' => $rates['time_last_update_utc'] ?? null
        ];
    }

    /**
     * Retornar cache ou valores padrão
     */
    private function getCachedOrDefault(string $baseCurrency): array
    {
        $cacheFile = __DIR__ . '/../../cache/exchange_rates_' . strtolower($baseCurrency) . '.json';
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true) ?? $this->getDefaultRates();
        }
        return $this->getDefaultRates();
    }

    private function getDefaultRates(): array
    {
        return [
            'result' => 'fallback',
            'base_code' => 'USD',
            'rates' => [
                'USD' => 1.0, 'BRL' => 5.05, 'EUR' => 0.92,
                'GBP' => 0.79, 'JPY' => 155.0
            ]
        ];
    }
}
