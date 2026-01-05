<?php

/**
 * Controller para gerenciamento de empresa (tenant)
 * API REST para configurações da empresa
 */

namespace src\controllers;

use \core\Controller as ctrl;
use \src\handlers\EmpresaHandler;
use Exception;

class EmpresaController extends ctrl
{
    const CAMPOS_CRIAR = ['nome', 'cnpj'];
    const CAMPOS_ATUALIZAR = ['nome', 'cnpj'];

    /**
     * Renderiza a página de configuração da empresa
     * GET /empresa
     */
    public function index()
    {
        $this->render('empresa');
    }

    /**
     * Lista todas as empresas (admin only)
     * GET /api/empresas
     */
    public function listar()
    {
        try {
            $empresas = EmpresaHandler::listar();
            ctrl::response($empresas, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Busca empresa por ID
     * GET /api/empresas/{id}
     */
    public function buscar($args)
    {
        try {
            $idempresa = $args['id'] ?? ctrl::empresa();

            if (empty($idempresa)) {
                throw new Exception('ID da empresa é obrigatório');
            }

            $empresa = EmpresaHandler::buscar($idempresa);
            
            if (!$empresa) {
                throw new Exception('Empresa não encontrada');
            }

            ctrl::response($empresa, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Retorna dados da empresa atual (logada)
     * GET /api/empresa/atual
     */
    public function atual()
    {
        try {
            $idempresa = ctrl::empresa();

            if (empty($idempresa)) {
                throw new Exception('Usuário não está logado');
            }

            $empresa = EmpresaHandler::buscar($idempresa);
            
            if (!$empresa) {
                throw new Exception('Empresa não encontrada');
            }

            ctrl::response($empresa, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Cria nova empresa (admin only)
     * POST /api/empresas
     */
    public function criar()
    {
        try {
            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_CRIAR);

            $resultado = EmpresaHandler::criar($dados);

            ctrl::response(['success' => true, 'id' => $resultado], 201);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza empresa
     * PUT /api/empresas/{id}
     */
    public function atualizar($args)
    {
        try {
            $idempresa = $args['id'] ?? ctrl::empresa();

            if (empty($idempresa)) {
                throw new Exception('ID da empresa é obrigatório');
            }

            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_ATUALIZAR);

            $resultado = EmpresaHandler::atualizar($idempresa, $dados);

            ctrl::response(['success' => true], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Exclui empresa (admin only)
     * DELETE /api/empresas/{id}
     */
    public function excluir($args)
    {
        try {
            $idempresa = $args['id'] ?? null;

            if (empty($idempresa)) {
                throw new Exception('ID da empresa é obrigatório');
            }

            // Não permite excluir a própria empresa
            if ($idempresa == ctrl::empresa()) {
                throw new Exception('Não é possível excluir a própria empresa');
            }

            EmpresaHandler::excluir($idempresa);

            ctrl::response(['success' => true], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Conta total de empresas
     * GET /api/empresas/count
     */
    public function contar()
    {
        try {
            $total = EmpresaHandler::contar();
            ctrl::response(['total' => $total], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
