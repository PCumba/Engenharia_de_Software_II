<?php
/**
 * Serviço de Integração com APIs de Criptomoedas (Kraken - GRATUITA)
 */

class CryptoService {
    private $apiUrl;
    private $apiProvider;

    public function __construct() {
        $this->apiProvider = getenv('CRYPTO_API_PROVIDER') ?: 'kraken';
        $this->apiUrl = getenv('KRAKEN_API_URL') ?: 'https://api.kraken.com/0/public';
    }

    /**
     * Obter lista de top criptomoedas
     * Kraken API tem 25 moedas top disponíveis
     */
    public function getTopCryptos($limit = 25, $currency = 'USD') {
        try {
            // Kraken retorna os pares XBT/USD, ETH/USD, etc
            $pairs = ['XXBTZUSD', 'XETHZUSD', 'ADAZUSD', 'DOTZUSD', 'LTCZUSD', 'XRPZUSD', 
                      'LINKZUSD', 'XLMZUSD', 'BCHUSD', 'ZECUSD', 'XTZUSD', 'MANAZUSD'];
            
            $url = "{$this->apiUrl}/Ticker?pair=" . implode(',', $pairs);
            
            $response = $this->makeRequest($url);

            if (!$response || !isset($response['result'])) {
                throw new Exception("Erro ao buscar criptomoedas");
            }

            return $this->formatCryptos($response['result'], $limit);
        } catch (Exception $e) {
            throw new Exception("Erro no serviço de criptomoedas: " . $e->getMessage());
        }
    }

    /**
     * Obter dados de uma criptomoeda específica
     */
    public function getCryptoDetails($cryptoId, $currency = 'USD') {
        try {
            // Mapper de IDs para pares Kraken
            $pairMap = [
                'bitcoin' => 'XXBTZUSD',
                'ethereum' => 'XETHZUSD',
                'cardano' => 'ADAZUSD',
                'polkadot' => 'DOTZUSD'
            ];
            
            $pair = $pairMap[strtolower($cryptoId)] ?? strtoupper($cryptoId) . 'ZUSD';
            $url = "{$this->apiUrl}/Ticker?pair={$pair}";
            
            $response = $this->makeRequest($url);

            if (!$response || !isset($response['result'])) {
                throw new Exception("Criptomoeda não encontrada");
            }

            $data = current($response['result']);
            return $this->formatCryptoDetails($data, $pair);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar detalhes: " . $e->getMessage());
        }
    }

    /**
     * Obter histórico de preços (últimas 720 horas = 30 dias)
     */
    public function getPriceHistory($cryptoId, $days = 7, $currency = 'USD') {
        try {
            $pairMap = [
                'bitcoin' => 'XXBTZUSD',
                'ethereum' => 'XETHZUSD',
            ];
            
            $pair = $pairMap[strtolower($cryptoId)] ?? 'XXBTZUSD';
            $interval = 1440; // 1 dia em minutos
            
            $url = "{$this->apiUrl}/OHLC?pair={$pair}&interval={$interval}";
            
            $response = $this->makeRequest($url);

            if (!$response || !isset($response['result'])) {
                throw new Exception("Erro ao buscar histórico");
            }

            $data = current($response['result']);
            
            return [
                'prices' => array_map(function($candle) {
                    return [$candle[0] * 1000, $candle[1]];
                }, array_slice($data, -$days)),
                'pair' => $pair
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar histórico: " . $e->getMessage());
        }
    }

    /**
     * Buscar criptomoeda por símbolo
     */
    public function searchCrypto($query) {
        try {
            $query = strtoupper($query);
            
            // Mapa de criptomoedas populares
            $cryptos = [
                'BTC' => ['id' => 'bitcoin', 'name' => 'Bitcoin', 'pair' => 'XXBTZUSD'],
                'ETH' => ['id' => 'ethereum', 'name' => 'Ethereum', 'pair' => 'XETHZUSD'],
                'ADA' => ['id' => 'cardano', 'name' => 'Cardano', 'pair' => 'ADAZUSD'],
                'DOT' => ['id' => 'polkadot', 'name' => 'Polkadot', 'pair' => 'DOTZUSD'],
                'LTC' => ['id' => 'litecoin', 'name' => 'Litecoin', 'pair' => 'LTCZUSD'],
                'XRP' => ['id' => 'ripple', 'name' => 'Ripple', 'pair' => 'XRPZUSD'],
                'LINK' => ['id' => 'chainlink', 'name' => 'Chainlink', 'pair' => 'LINKZUSD'],
                'XLM' => ['id' => 'stellar', 'name' => 'Stellar', 'pair' => 'XLMZUSD'],
            ];

            $results = [];
            foreach ($cryptos as $symbol => $data) {
                if (strpos($symbol, $query) === 0 || strpos($data['name'], $query) === 0) {
                    $results[] = $data;
                }
            }

            if (empty($results)) {
                throw new Exception("Nenhuma criptomoeda encontrada");
            }

            return $results;
        } catch (Exception $e) {
            throw new Exception("Erro na busca: " . $e->getMessage());
        }
    }

    /**
     * Fazer requisição HTTP
     */
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return null;
    }

    /**
     * Formatar dados das criptomoedas
     */
    private function formatCryptos($data, $limit = 25) {
        $formatted = [];
        $rank = 1;
        
        // Map de pares para nomes legíveis
        $pairNames = [
            'XXBTZUSD' => ['name' => 'Bitcoin', 'symbol' => 'BTC', 'id' => 'bitcoin'],
            'XETHZUSD' => ['name' => 'Ethereum', 'symbol' => 'ETH', 'id' => 'ethereum'],
            'ADAZUSD' => ['name' => 'Cardano', 'symbol' => 'ADA', 'id' => 'cardano'],
            'DOTZUSD' => ['name' => 'Polkadot', 'symbol' => 'DOT', 'id' => 'polkadot'],
            'LTCZUSD' => ['name' => 'Litecoin', 'symbol' => 'LTC', 'id' => 'litecoin'],
            'XRPZUSD' => ['name' => 'Ripple', 'symbol' => 'XRP', 'id' => 'ripple'],
            'LINKZUSD' => ['name' => 'Chainlink', 'symbol' => 'LINK', 'id' => 'chainlink'],
            'XLMZUSD' => ['name' => 'Stellar', 'symbol' => 'XLM', 'id' => 'stellar'],
            'BCHUSD' => ['name' => 'Bitcoin Cash', 'symbol' => 'BCH', 'id' => 'bitcoin-cash'],
            'ZECUSD' => ['name' => 'Zcash', 'symbol' => 'ZEC', 'id' => 'zcash'],
            'XTZUSD' => ['name' => 'Tezos', 'symbol' => 'XTZ', 'id' => 'tezos'],
            'MANAZUSD' => ['name' => 'Decentraland', 'symbol' => 'MANA', 'id' => 'decentraland'],
        ];

        foreach ($data as $pair => $prices) {
            if ($rank > $limit) break;

            $info = $pairNames[$pair] ?? ['name' => $pair, 'symbol' => substr($pair, 0, 3), 'id' => strtolower($pair)];
            
            $formatted[] = [
                'id' => $info['id'],
                'symbol' => $info['symbol'],
                'name' => $info['name'],
                'image' => '',
                'price' => (float)$prices['c'][0],
                'marketCap' => 0,
                'marketCapRank' => $rank,
                'volume24h' => (float)$prices['v'][1],
                'percentChange24h' => 0,
                'percentChange7d' => 0,
                'high24h' => (float)$prices['h'][1],
                'low24h' => (float)$prices['l'][1],
                'ath' => 0,
                'atl' => 0
            ];
            
            $rank++;
        }

        return $formatted;
    }

    /**
     * Formatar dados detalhados da criptomoeda
     */
    private function formatCryptoDetails($data, $pair) {
        $pairNames = [
            'XXBTZUSD' => ['name' => 'Bitcoin', 'symbol' => 'BTC'],
            'XETHZUSD' => ['name' => 'Ethereum', 'symbol' => 'ETH'],
        ];

        $info = $pairNames[$pair] ?? ['name' => $pair, 'symbol' => substr($pair, 0, 3)];

        return [
            'id' => strtolower($pair),
            'symbol' => $info['symbol'],
            'name' => $info['name'],
            'image' => '',
            'description' => '',
            'price' => (float)$data['c'][0],
            'marketCap' => 0,
            'volume24h' => (float)$data['v'][1],
            'percentChange24h' => 0,
            'high24h' => (float)$data['h'][1],
            'low24h' => (float)$data['l'][1],
            'ath' => 0,
            'athChangePercent' => 0,
            'atl' => 0,
            'marketCapRank' => 1,
            'totalSupply' => 0,
            'circulatingSupply' => 0,
            'links' => []
        ];
    }
}
?>
