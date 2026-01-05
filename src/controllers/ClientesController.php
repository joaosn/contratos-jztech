<?php

namespace src\controllers;

use \core\Controller as ctrl;
use src\handlers\ClientesHandler;
use Exception;

/**
 * ClientesController - API REST completa para Clientes
 * 
 * Rotas:
 * GET    /api/clientes                - Listar todos
 * GET    /api/clientes/{id}           - Buscar por ID
 * POST   /api/clientes                - Criar novo
 * PUT    /api/clientes/{id}           - Atualizar
 * DELETE /api/clientes/{id}           - Excluir
 * GET    /api/clientes/{id}/enderecos - Listar endereços
 * GET    /api/clientes/{id}/contatos  - Listar contatos
 */
class ClientesController extends ctrl
{
    private ClientesHandler $handler;

    const CAMPOS_CRIAR = ['nome', 'tipo_pessoa', 'cpf_cnpj'];
    const CAMPOS_ATUALIZAR = ['nome'];

    public function __construct()
    {
        parent::__construct();
        $this->handler = new ClientesHandler();
    }

    /**
     * Renderiza a página de listagem de clientes
     * GET /clientes
     */
    public function index()
    {
        $this->render('clientes');
    }

    /**
     * Lista clientes com paginação e filtros
     * GET /api/clientes?limit=50&offset=0&ativo=1&tipo_pessoa=pf
     */
    public function listar()
    {
        try {
            $filtros = [
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0,
                'ativo' => $_GET['ativo'] ?? null,
                'tipo_pessoa' => $_GET['tipo_pessoa'] ?? null
            ];

            // Remove filtros nulos
            $filtros = array_filter($filtros, fn($v) => $v !== null);

            $resultado = $this->handler->listarClientes($filtros);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Busca cliente por ID com endereços e contatos
     * GET /api/clientes/{id}
     */
    public function buscar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do cliente é obrigatório');
            }

            $resultado = $this->handler->buscarClienteCompleto($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Cria novo cliente
     * POST /api/clientes
     */
    public function criar()
    {
        try {
            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_CRIAR);

            $resultado = $this->handler->criarCliente($dados);
            
            ctrl::response($resultado, 201);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza cliente existente
     * PUT /api/clientes/{id}
     */
    public function atualizar($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do cliente é obrigatório');
            }

            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_ATUALIZAR);

            $resultado = $this->handler->atualizarCliente($id, $dados);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Exclui cliente
     * DELETE /api/clientes/{id}
     */
    public function excluir($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do cliente é obrigatório');
            }

            $resultado = $this->handler->excluirCliente($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Lista endereços do cliente
     * GET /api/clientes/{id}/enderecos
     */
    public function listarEnderecos($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do cliente é obrigatório');
            }

            $resultado = $this->handler->listarEnderecos($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Lista contatos do cliente
     * GET /api/clientes/{id}/contatos
     */
    public function listarContatos($id)
    {
        try {
            if (empty($id)) {
                throw new Exception('ID do cliente é obrigatório');
            }

            $resultado = $this->handler->listarContatos($id);
            
            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
