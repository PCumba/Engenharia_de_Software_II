<?php
/**
 * Serviço de Exportação de Dados (CSV e PDF)
 */

class ExportService {
    /**
     * Exportar histórico em CSV
     */
    public static function exportToCSV($data, $filename = 'weather_history.csv') {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        $output = fopen('php://output', 'w');
        
        // Cabeçalho
        fputcsv($output, ['Data', 'Cidade', 'País', 'Temperatura', 'Descrição', 'Humidade']);
        
        // Dados
        foreach ($data as $row) {
            $weatherData = is_string($row['weather_data']) ? json_decode($row['weather_data'], true) : $row['weather_data'];
            
            fputcsv($output, [
                $row['created_at'],
                $row['city'],
                $row['country'],
                $weatherData['temperature'] . '°C',
                $weatherData['description'],
                $weatherData['humidity'] . '%'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar histórico em PDF
     * Nota: Requer TCPDF ou similar
     */
    public static function exportToPDF($data, $filename = 'weather_history.pdf') {
        // Implementação requer biblioteca PDF (ex: TCPDF)
        // Para este exemplo, apenas retornamos uma mensagem
        throw new Exception("PDF export requer configuração adicional");
    }

    /**
     * Gerar relatório de tempo
     */
    public static function generateWeatherReport($weatherData, $language = 'pt') {
        $report = [
            'title' => $language === 'pt' ? 'Relatório de Previsão do Tempo' : 'Weather Forecast Report',
            'generatedAt' => date('Y-m-d H:i:s'),
            'location' => $weatherData['city'] . ', ' . $weatherData['country'],
            'currentConditions' => [
                'temperature' => $weatherData['temperature'],
                'description' => $weatherData['description'],
                'humidity' => $weatherData['humidity'],
                'windSpeed' => $weatherData['windSpeed']
            ],
            'summary' => $this->generateSummary($weatherData, $language)
        ];

        return $report;
    }

    private static function generateSummary($data, $language = 'pt') {
        if ($language === 'pt') {
            return sprintf(
                "Em %s: %s. Temperatura: %d°C (sensação térmica: %d°C). Humidade: %d%%. Velocidade do vento: %.1f m/s.",
                $data['city'],
                $data['description'],
                (int)$data['temperature'],
                (int)$data['feelsLike'],
                $data['humidity'],
                $data['windSpeed']
            );
        } else {
            return sprintf(
                "In %s: %s. Temperature: %d°C (feels like: %d°C). Humidity: %d%%. Wind speed: %.1f m/s.",
                $data['city'],
                $data['description'],
                (int)$data['temperature'],
                (int)$data['feelsLike'],
                $data['humidity'],
                $data['windSpeed']
            );
        }
    }
}
?>
