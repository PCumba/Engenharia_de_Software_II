<?php

namespace App\Controllers;

use App\Models\Category;
use App\Utils\Response;

/**
 * Controlador de Categorias
 */
class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $type = $_GET['type'] ?? null;
            $categories = $this->categoryModel->getUserCategories($userId, $type);
            Response::success(['categories' => $categories]);
        } catch (\Exception $e) {
            error_log("Categories index error: " . $e->getMessage());
            Response::error('Erro ao listar categorias', 500);
        }
    }

    public function create(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name'])) {
                Response::validation(['Nome é obrigatório']);
                return;
            }

            if ($this->categoryModel->nameExists($userId, $data['name'])) {
                Response::conflict('Categoria com este nome já existe');
                return;
            }

            $data['user_id'] = $userId;
            $id = $this->categoryModel->create($data);
            Response::success(['category' => $this->categoryModel->find($id)], 'Categoria criada com sucesso', 201);
        } catch (\Exception $e) {
            error_log("Category create error: " . $e->getMessage());
            Response::error('Erro ao criar categoria', 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $category = $this->categoryModel->find($id);
            if (!$category) {
                Response::notFound('Categoria não encontrada');
                return;
            }
            Response::success(['category' => $category]);
        } catch (\Exception $e) {
            Response::error('Erro ao buscar categoria', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $category = $this->categoryModel->find($id);

            if (!$category || ($category['user_id'] != $userId && $category['user_id'] != 0)) {
                Response::notFound('Categoria não encontrada');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $this->categoryModel->update($id, $data);
            Response::success(['category' => $this->categoryModel->find($id)], 'Categoria atualizada');
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar categoria', 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $category = $this->categoryModel->find($id);

            if (!$category || $category['user_id'] != $userId) {
                Response::notFound('Categoria não encontrada');
                return;
            }

            $this->categoryModel->update($id, ['is_active' => false]);
            Response::success(null, 'Categoria removida com sucesso');
        } catch (\Exception $e) {
            Response::error('Erro ao remover categoria', 500);
        }
    }
}
