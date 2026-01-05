<?php

namespace src\controllers;

use core\Controller as ctrl;
use Exception;
use src\handlers\SistemasHandler;

/**
 * SistemasController - API REST completa para Sistemas
 * 
 * Rotas:
 * GET    /api/sistemas              - Listar todos
 * GET    /api/sistemas/{id}         - Buscar por ID
 * POST   /api/sistemas              - Criar novo
 * PUT    /api/sistemas/{id}         - Atualizar
 * DELETE /api/sistemas/{id}         - Excluir
 * GET    /api/sistemas/{id}/planos  - Listar planos
 * GET    /api/sistemas/{id}/addons  - Listar add-ons
 */
class SistemasController extends ctrl
{
    private SistemasHandler $handler;

    const CAMPOS_CRIAR = ['nome'];
    const CAMPOS_ATUALIZAR = ['nome'];

    public function __construct()
    {
        parent::__construct();
        $this->handler = new SistemasHandler();
    }

    /**
     * Renderiza a página de listagem de sistemas
     * GET /sistemas
     */
    public function index()
    {
        $this->render('sistemas');
    }

    /**
     * Lista sistemas com paginação e filtros
     * GET /api/sistemas?limit=50&offset=0&ativo=1
     */
    public function listar()
    {
        try {
            $filtros = [
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0,
                'ativo' => $_GET['ativo'] ?? null
            ];

            // Remove filtros nulos
            $filtros = array_filter($filtros, fn($v) => $v !== null);

            $resultado = $this->handler->listarSistemas($filtros);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Busca sistema por ID com planos e add-ons
     * GET /api/sistemas/{id}
     */
    public function buscar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do sistema é obrigatório');
            }

            $resultado = $this->handler->buscarSistemaCompleto($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Cria novo sistema
     * POST /api/sistemas
     */
    public function criar()
    {
        try {
            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_CRIAR);

            $resultado = $this->handler->criarSistema($dados);
            
            ctrl::response($resultado, 201);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza sistema existente
     * PUT /api/sistemas/{id}
     */
    public function atualizar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do sistema é obrigatório');
            }

            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_ATUALIZAR);

            $resultado = $this->handler->atualizarSistema($id, $dados);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Exclui sistema
     * DELETE /api/sistemas/{id}
     */
    public function excluir($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do sistema é obrigatório');
            }

            $resultado = $this->handler->excluirSistema($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Lista planos do sistema
     * GET /api/sistemas/{id}/planos
     */
    public function listarPlanos($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do sistema é obrigatório');
            }

            $resultado = $this->handler->listarPlanos($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Lista add-ons do sistema
     * GET /api/sistemas/{id}/addons
     */
    public function listarAddons($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do sistema é obrigatório');
            }

            $resultado = $this->handler->listarAddons($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
