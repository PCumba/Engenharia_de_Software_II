<?php
/**
 * Controller de Transações
 */

class TransactionController {
    private $financeService;
    private $transactionModel;

    public function __construct($database) {
        $this->financeService = new FinanceService($database);
        $this->transactionModel = new Transaction($database);
    }

    public function getSummary() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $summary = $this->financeService->getSummary($token['userId']);
            return Response::success('Resumo financeiro', $summary);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function create() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $rules = [
                'categoryId' => 'required|numeric',
                'description' => 'required|min:3',
                'amount' => 'required|numeric',
                'type' => 'required'
            ];

            $errors = Validator::validate($data, $rules);
            if (!Validator::isValid($errors)) {
                return Response::error('Dados inválidos', $errors, 422);
            }

            $id = $this->financeService->addTransaction(
                $token['userId'],
                $data['categoryId'],
                $data['description'],
                $data['amount'],
                $data['type'],
                $data['date'] ?? null
            );

            return Response::success('Transação criada', ['id' => $id], 201);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getByPeriod() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate = $_GET['endDate'] ?? date('Y-m-t');

            $transactions = $this->financeService->getTransactionsByPeriod(
                $token['userId'],
                $startDate,
                $endDate
            );

            return Response::success('Transações do período', $transactions);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function getRecent() {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $transactions = $this->transactionModel->getByUser($token['userId'], 20);
            return Response::success('Transações recentes', $transactions);
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function update($transactionId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $data = json_decode(file_get_contents('php://input'), true);

            $this->transactionModel->update(
                $transactionId,
                $token['userId'],
                $data['categoryId'],
                $data['description'],
                $data['amount'],
                $data['date']
            );

            return Response::success('Transação atualizada');
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }

    public function delete($transactionId) {
        try {
            $token = Auth::checkToken();
            if (!$token) return Response::error('Não autenticado', null, 401);

            $this->transactionModel->delete($transactionId, $token['userId']);
            return Response::success('Transação removida');
        } catch (Exception $e) {
            return Response::error('Erro', ['error' => $e->getMessage()], 500);
        }
    }
}
?>
