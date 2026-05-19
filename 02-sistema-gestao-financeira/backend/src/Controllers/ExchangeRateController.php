<?php

namespace App\Controllers;

use App\Services\ExchangeRateService;
use App\Utils\Response;

/**
 * Controlador de Taxas de Câmbio - Proxy para API externa
 */
class ExchangeRateController
{
    private ExchangeRateService $service;

    public function __construct()
    {
        $this->service = new ExchangeRateService();
    }

    public function getRates(): void
    {
        try {
            $base = $_GET['base'] ?? 'USD';
            $rates = $this->service->getSupportedRates(strtoupper($base));
            Response::success($rates);
        } catch (\Exception $e) {
            error_log("Exchange rates error: " . $e->getMessage());
            Response::error('Erro ao buscar taxas de câmbio', 500);
        }
    }

    public function convert(): void
    {
        try {
            $amount = (float) ($_GET['amount'] ?? 0);
            $from = strtoupper($_GET['from'] ?? 'USD');
            $to = strtoupper($_GET['to'] ?? 'BRL');

            if ($amount <= 0) {
                Response::validation(['Valor deve ser positivo']);
                return;
            }

            $result = $this->service->convert($amount, $from, $to);
            if (!$result) {
                Response::error('Não foi possível converter', 400);
                return;
            }

            Response::success($result);
        } catch (\Exception $e) {
            error_log("Exchange convert error: " . $e->getMessage());
            Response::error('Erro na conversão', 500);
        }
    }
}
