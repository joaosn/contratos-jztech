<?php

namespace src\controllers;

use \core\Controller as ctrl;
use src\handlers\AssinaturasHandler;
use Exception;

/**
 * AssinaturasController - API REST completa para Assinaturas
 * 
 * Rotas:
 * GET    /api/assinaturas              - Listar todas
 * GET    /api/assinaturas/{id}         - Buscar por ID
 * POST   /api/assinaturas              - Criar nova
 * PUT    /api/assinaturas/{id}         - Atualizar
 * DELETE /api/assinaturas/{id}         - Excluir
 * PUT    /api/assinaturas/{id}/status  - Atualizar status
 * GET    /api/assinaturas/{id}/addons  - Listar add-ons
 */
class AssinaturasController extends ctrl
{
    private AssinaturasHandler $handler;

    const CAMPOS_CRIAR = ['idcliente', 'idsistema', 'data_inicio', 'dia_vencimento', 'ciclo_cobranca'];
    const CAMPOS_ATUALIZAR = [];
    const CAMPOS_STATUS = ['status'];

    public function __construct()
    {
        parent::__construct();
        $this->handler = new AssinaturasHandler();
    }

    /**
     * Renderiza a página de listagem de assinaturas
     * GET /assinaturas
     */
    public function index()
    {
        $this->render('assinaturas');
    }

    /**
     * Lista assinaturas com paginação e filtros
     * GET /api/assinaturas?limit=50&offset=0&status=ativa&idcliente=1&idsistema=1
     */
    public function listar()
    {
        try {
            $filtros = [
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0,
                'status' => $_GET['status'] ?? null,
                'idcliente' => $_GET['idcliente'] ?? null,
                'idsistema' => $_GET['idsistema'] ?? null
            ];

            // Remove filtros nulos
            $filtros = array_filter($filtros, fn($v) => $v !== null);

            $resultado = $this->handler->listarAssinaturas($filtros);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Busca assinatura por ID com add-ons
     * GET /api/assinaturas/{id}
     */
    public function buscar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID da assinatura é obrigatório');
            }

            $resultado = $this->handler->buscarAssinaturaCompleta($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Cria nova assinatura
     * POST /api/assinaturas
     */
    public function criar()
    {
        try {
            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_CRIAR);

            $resultado = $this->handler->criarAssinatura($dados);
            
            ctrl::response($resultado, 201);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza assinatura existente
     * PUT /api/assinaturas/{id}
     */
    public function atualizar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID da assinatura é obrigatório');
            }

            $dados = ctrl::getBody();

            $resultado = $this->handler->atualizarAssinatura($id, $dados);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Exclui assinatura
     * DELETE /api/assinaturas/{id}
     */
    public function excluir($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID da assinatura é obrigatório');
            }

            $resultado = $this->handler->excluirAssinatura($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza status da assinatura
     * PUT /api/assinaturas/{id}/status
     */
    public function atualizarStatus($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID da assinatura é obrigatório');
            }

            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_STATUS);

            $resultado = $this->handler->atualizarStatus($id, $dados['status']);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Lista add-ons da assinatura
     * GET /api/assinaturas/{id}/addons
     */
    public function listarAddons($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID da assinatura é obrigatório');
            }

            $resultado = $this->handler->listarAddons($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
