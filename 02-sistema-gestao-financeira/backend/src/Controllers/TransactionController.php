<?php

namespace App\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Utils\Response;
use App\Utils\Validator;

/**
 * Controlador de Transações
 */
class TransactionController
{
    private Transaction $transactionModel;
    private Account $accountModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
    }

    /**
     * Listar transações do usuário
     */
    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            
            // Parâmetros de filtro e paginação
            $filters = [
                'type' => $_GET['type'] ?? null,
                'account_id' => $_GET['account_id'] ?? null,
                'category_id' => $_GET['category_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'amount_min' => $_GET['amount_min'] ?? null,
                'amount_max' => $_GET['amount_max'] ?? null,
                'search' => $_GET['search'] ?? null,
                'status' => $_GET['status'] ?? null
            ];
            
            $page = (int) ($_GET['page'] ?? 1);
            $limit = min((int) ($_GET['limit'] ?? 20), 100);
            
            $result = $this->transactionModel->getUserTransactions($userId, $filters, $page, $limit);
            
            Response::success($result);
            
        } catch (\Exception $e) {
            error_log("Get transactions error: " . $e->getMessage());
            Response::error('Erro ao buscar transações', 500);
        }
    }

    /**
     * Buscar transação específica
     */
    public function show(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            
            $transaction = $this->transactionModel->find($id);
            
            if (!$transaction || $transaction['user_id'] !== $userId) {
                Response::error('Transação não encontrada', 404);
                return;
            }
            
            Response::success(['transaction' => $transaction]);
            
        } catch (\Exception $e) {
            error_log("Get transaction error: " . $e->getMessage());
            Response::error('Erro ao buscar transação', 500);
        }
    }

    /**
     * Criar nova transação
     */
    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar dados
            $errors = $this->transactionModel->validateTransactionData($data);
            
            if (!empty($errors)) {
                Response::error('Dados inválidos', 400, $errors);
                return;
            }
            
            // Verificar se a conta pertence ao usuário
            if (!$this->accountModel->belongsToUser($data['account_id'], $userId)) {
                Response::error('Conta não encontrada', 404);
                return;
            }
            
            // Adicionar user_id
            $data['user_id'] = $userId;
            
            // Definir status padrão
            $data['status'] = $data['status'] ?? 'completed';
            
            // Criar transação
            $transactionId = $this->transactionModel->createTransaction($data);
            
            // Buscar transação criada
            $transaction = $this->transactionModel->find($transactionId);
            
            Response::success([
                'transaction' => $transaction
            ], 'Transação criada com sucesso', 201);
            
        } catch (\Exception $e) {
            error_log("Create transaction error: " . $e->getMessage());
            Response::error('Erro ao criar transação', 500);
        }
    }

    /**
     * Atualizar transação
     */
    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verificar se a transação existe e pertence ao usuário
            $transaction = $this->transactionModel->find($id);
            
            if (!$transaction || $transaction['user_id'] !== $userId) {
                Response::error('Transação não encontrada', 404);
                return;
            }
            
            // Validar dados (apenas campos fornecidos)
            if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
                Response::error('Valor deve ser um número positivo', 400);
                return;
            }
            
            if (isset($data['type']) && !array_key_exists($data['type'], Transaction::TYPES)) {
                Response::error('Tipo de transação inválido', 400);
                return;
            }
            
            if (isset($data['account_id']) && !$this->accountModel->belongsToUser($data['account_id'], $userId)) {
                Response::error('Conta não encontrada', 404);
                return;
            }
            
            // Atualizar transação
            $success = $this->transactionModel->updateTransaction($id, $data);
            
            if (!$success) {
                Response::error('Erro ao atualizar transação', 500);
                return;
            }
            
            // Buscar transação atualizada
            $updatedTransaction = $this->transactionModel->find($id);
            
            Response::success([
                'transaction' => $updatedTransaction
            ], 'Transação atualizada com sucesso');
            
        } catch (\Exception $e) {
            error_log("Update transaction error: " . $e->getMessage());
            Response::error('Erro ao atualizar transação', 500);
        }
    }

    /**
     * Deletar transação
     */
    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            
            // Verificar se a transação existe e pertence ao usuário
            $transaction = $this->transactionModel->find($id);
            
            if (!$transaction || $transaction['user_id'] !== $userId) {
                Response::error('Transação não encontrada', 404);
                return;
            }
            
            // Deletar transação
            $success = $this->transactionModel->deleteTransaction($id);
            
            if (!$success) {
                Response::error('Erro ao deletar transação', 500);
                return;
            }
            
            Response::success(null, 'Transação deletada com sucesso');
            
        } catch (\Exception $e) {
            error_log("Delete transaction error: " . $e->getMessage());
            Response::error('Erro ao deletar transação', 500);
        }
    }

    /**
     * Importar transações de arquivo
     */
    public function import(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            
            if (!isset($_FILES['file'])) {
                Response::error('Arquivo não fornecido', 400);
                return;
            }
            
            $file = $_FILES['file'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['text/csv', 'application/csv', 'text/plain'];
            if (!in_array($file['type'], $allowedTypes)) {
                Response::error('Tipo de arquivo não suportado. Use CSV.', 400);
                return;
            }
            
            // Processar arquivo CSV
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                Response::error('Erro ao ler arquivo', 500);
                return;
            }
            
            $imported = 0;
            $errors = [];
            $line = 0;
            
            // Pular cabeçalho
            fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $line++;
                
                try {
                    // Mapear colunas do CSV
                    $transactionData = [
                        'user_id' => $userId,
                        'account_id' => (int) $row[0],
                        'type' => $row[1],
                        'amount' => (float) $row[2],
                        'description' => $row[3],
                        'transaction_date' => $row[4],
                        'category_id' => !empty($row[5]) ? (int) $row[5] : null,
                        'status' => 'completed'
                    ];
                    
                    // Validar dados
                    $validationErrors = $this->transactionModel->validateTransactionData($transactionData);
                    
                    if (!empty($validationErrors)) {
                        $errors[] = "Linha {$line}: " . implode(', ', $validationErrors);
                        continue;
                    }
                    
                    // Verificar se a conta pertence ao usuário
                    if (!$this->accountModel->belongsToUser($transactionData['account_id'], $userId)) {
                        $errors[] = "Linha {$line}: Conta não encontrada";
                        continue;
                    }
                    
                    // Criar transação
                    $this->transactionModel->createTransaction($transactionData);
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Linha {$line}: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            Response::success([
                'imported' => $imported,
                'errors' => $errors
            ], "Importação concluída. {$imported} transações importadas.");
            
        } catch (\Exception $e) {
            error_log("Import transactions error: " . $e->getMessage());
            Response::error('Erro ao importar transações', 500);
        }
    }

    /**
     * Criar múltiplas transações
     */
    public function bulkCreate(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['transactions']) || !is_array($data['transactions'])) {
                Response::error('Lista de transações é obrigatória', 400);
                return;
            }
            
            $created = 0;
            $errors = [];
            
            $this->transactionModel->beginTransaction();
            
            try {
                foreach ($data['transactions'] as $index => $transactionData) {
                    // Validar dados
                    $validationErrors = $this->transactionModel->validateTransactionData($transactionData);
                    
                    if (!empty($validationErrors)) {
                        $errors[] = "Transação {$index}: " . implode(', ', $validationErrors);
                        continue;
                    }
                    
                    // Verificar se a conta pertence ao usuário
                    if (!$this->accountModel->belongsToUser($transactionData['account_id'], $userId)) {
                        $errors[] = "Transação {$index}: Conta não encontrada";
                        continue;
                    }
                    
                    // Adicionar user_id
                    $transactionData['user_id'] = $userId;
                    $transactionData['status'] = $transactionData['status'] ?? 'completed';
                    
                    // Criar transação
                    $this->transactionModel->createTransaction($transactionData);
                    $created++;
                }
                
                $this->transactionModel->commit();
                
            } catch (\Exception $e) {
                $this->transactionModel->rollback();
                throw $e;
            }
            
            Response::success([
                'created' => $created,
                'errors' => $errors
            ], "Criação em lote concluída. {$created} transações criadas.");
            
        } catch (\Exception $e) {
            error_log("Bulk create transactions error: " . $e->getMessage());
            Response::error('Erro ao criar transações em lote', 500);
        }
    }

    /**
     * Obter resumo financeiro
     */
    public function summary(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $period = $_GET['period'] ?? 'month';
            
            $summary = $this->transactionModel->getFinancialSummary($userId, $period);
            
            Response::success(['summary' => $summary]);
            
        } catch (\Exception $e) {
            error_log("Get summary error: " . $e->getMessage());
            Response::error('Erro ao buscar resumo', 500);
        }
    }

    /**
     * Obter gastos por categoria
     */
    public function expensesByCategory(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $period = $_GET['period'] ?? 'month';
            
            $expenses = $this->transactionModel->getExpensesByCategory($userId, $period);
            
            Response::success(['expenses_by_category' => $expenses]);
            
        } catch (\Exception $e) {
            error_log("Get expenses by category error: " . $e->getMessage());
            Response::error('Erro ao buscar gastos por categoria', 500);
        }
    }

    /**
     * Obter evolução mensal
     */
    public function monthlyEvolution(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $months = (int) ($_GET['months'] ?? 12);
            
            $evolution = $this->transactionModel->getMonthlyEvolution($userId, $months);
            
            Response::success(['monthly_evolution' => $evolution]);
            
        } catch (\Exception $e) {
            error_log("Get monthly evolution error: " . $e->getMessage());
            Response::error('Erro ao buscar evolução mensal', 500);
        }
    }
}