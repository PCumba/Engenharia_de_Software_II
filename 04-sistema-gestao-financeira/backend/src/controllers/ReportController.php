<?php
/**
 * Controller de Análises e Relatórios
 */

class ReportController {
    private $analyticsService;

    public function __construct($database) {
        $this->analyticsService = new AnalyticsService($database);
    }

    public function getExpensesByCategory() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate = $_GET['endDate'] ?? date('Y-m-t');

            $data = $this->analyticsService->getExpensesByCategory($token['userId'], $startDate, $endDate);
            return Response::success('Despesas por categoria', $data);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getIncomeByCategory() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate = $_GET['endDate'] ?? date('Y-m-t');

            $data = $this->analyticsService->getIncomeByCategory($token['userId'], $startDate, $endDate);
            return Response::success('Receitas por categoria', $data);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getMonthlyEvolution() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $year = $_GET['year'] ?? date('Y');
            $data = $this->analyticsService->getMonthlyEvolution($token['userId'], $year);
            return Response::success('Evolução mensal', $data);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getPeriodReport() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate = $_GET['endDate'] ?? date('Y-m-t');

            $report = $this->analyticsService->generatePeriodReport($token['userId'], $startDate, $endDate);
            return Response::success('Relatório do período', $report);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
