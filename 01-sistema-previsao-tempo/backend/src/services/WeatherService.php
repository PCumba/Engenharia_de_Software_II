<?php
/**
 * Serviço de Integração com API OpenWeatherMap
 */

class WeatherService {
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        $this->apiKey = getenv('WEATHER_API_KEY');
        $this->apiUrl = getenv('WEATHER_API_URL') ?: 'https://api.openweathermap.org/data/2.5';
    }

    /**
     * Buscar previsão atual de uma cidade
     */
    public function getCurrentWeather($city, $language = 'pt') {
        try {
            $cityParam = rawurlencode($city);
            $url = "{$this->apiUrl}/weather?q={$cityParam}&appid={$this->apiKey}&units=metric&lang={$language}";
            
            $response = $this->makeRequest($url);

            if (!$response) {
                throw new Exception("Erro ao buscar dados meteorológicos");
            }

            return $this->formatWeatherData($response);
        } catch (Exception $e) {
            throw new Exception("Erro no serviço de tempo: " . $e->getMessage());
        }
    }

    /**
     * Buscar previsão de 5 dias
     */
    public function getFiveDayForecast($city, $language = 'pt') {
        try {
            $cityParam = rawurlencode($city);
            $url = "{$this->apiUrl}/forecast?q={$cityParam}&appid={$this->apiKey}&units=metric&lang={$language}";
            
            $response = $this->makeRequest($url);

            if (!$response) {
                throw new Exception("Erro ao buscar previsão");
            }

            return $this->formatForecastData($response);
        } catch (Exception $e) {
            throw new Exception("Erro na previsão: " . $e->getMessage());
        }
    }

    /**
     * Buscar previsão por coordenadas
     */
    public function getWeatherByCoords($lat, $lon, $language = 'pt') {
        try {
            $url = "{$this->apiUrl}/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric&lang={$language}";
            
            $response = $this->makeRequest($url);

            if (!$response) {
                throw new Exception("Erro ao buscar dados meteorológicos");
            }

            return $this->formatWeatherData($response);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar por coordenadas: " . $e->getMessage());
        }
    }

    /**
     * Fazer requisição HTTP
     */
    private function makeRequest($url) {
        if (empty($this->apiKey)) {
            throw new Exception('WEATHER_API_KEY não configurada');
        }

        $sslVerify = filter_var(getenv('WEATHER_SSL_VERIFY') ?: 'false', FILTER_VALIDATE_BOOLEAN);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Em ambiente local, alguns macOS podem não ter cadeia de certificados configurada para cURL.
        if (!$sslVerify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            throw new Exception('Falha na comunicação com API externa: ' . $error);
        }

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        throw new Exception('API externa retornou HTTP ' . $httpCode);

        return null;
    }

    /**
     * Formatar dados de tempo atual
     */
    private function formatWeatherData($data) {
        return [
            'city' => $data['name'] ?? '',
            'country' => $data['sys']['country'] ?? '',
            'coordinates' => [
                'lat' => $data['coord']['lat'] ?? 0,
                'lon' => $data['coord']['lon'] ?? 0
            ],
            'temperature' => $data['main']['temp'] ?? 0,
            'feelsLike' => $data['main']['feels_like'] ?? 0,
            'humidity' => $data['main']['humidity'] ?? 0,
            'pressure' => $data['main']['pressure'] ?? 0,
            'windSpeed' => $data['wind']['speed'] ?? 0,
            'cloudiness' => $data['clouds']['all'] ?? 0,
            'description' => $data['weather'][0]['description'] ?? '',
            'icon' => $data['weather'][0]['icon'] ?? '',
            'visibility' => $data['visibility'] ?? 0,
            'sunrise' => date('H:i:s', $data['sys']['sunrise']),
            'sunset' => date('H:i:s', $data['sys']['sunset']),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Formatar dados de previsão
     */
    private function formatForecastData($data) {
        $forecasts = [];
        
        foreach ($data['list'] as $item) {
            $forecasts[] = [
                'dateTime' => date('Y-m-d H:i:s', $item['dt']),
                'temperature' => $item['main']['temp'],
                'feelsLike' => $item['main']['feels_like'],
                'humidity' => $item['main']['humidity'],
                'description' => $item['weather'][0]['description'],
                'icon' => $item['weather'][0]['icon'],
                'windSpeed' => $item['wind']['speed'],
                'probability' => $item['pop'] * 100
            ];
        }

        return [
            'city' => $data['city']['name'],
            'country' => $data['city']['country'],
            'forecasts' => $forecasts
        ];
    }
}
?>
