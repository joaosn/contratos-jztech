<?php

/**
 * Controller para gerenciamento de usuários
 * API REST completa com suporte a 2FA
 */

namespace src\controllers;

use \core\Controller as ctrl;
use \src\handlers\UsuariosHandler;
use Exception;

class UsuariosController extends ctrl
{
    const CAMPOS_CRIAR = ['nome', 'email', 'senha'];
    const CAMPOS_ATUALIZAR = ['nome', 'email'];

    /**
     * Renderiza a página de gestão de usuários
     * GET /usuarios
     */
    public function index()
    {
        $this->render('usuarios');
    }

    /**
     * Lista todos os usuários da empresa
     * GET /api/usuarios
     */
    public function listar()
    {
        try {
            $idempresa = ctrl::empresa();
            $usuarios = UsuariosHandler::listar($idempresa);
            ctrl::response($usuarios, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Busca usuário por ID
     * GET /api/usuarios/{id}
     */
    public function buscar($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $usuario = UsuariosHandler::buscar($idempresa, $idusuario);
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }

            ctrl::response($usuario, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Cria novo usuário
     * POST /api/usuarios
     */
    public function criar()
    {
        try {
            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_CRIAR);

            $dados['idempresa'] = ctrl::empresa();
            $resultado = UsuariosHandler::criar($dados);

            ctrl::response(['success' => true, 'id' => $resultado], 201);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza usuário
     * PUT /api/usuarios/{id}
     */
    public function atualizar($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados = ctrl::getBody();
            ctrl::verificarCamposVazios($dados, self::CAMPOS_ATUALIZAR);

            $resultado = UsuariosHandler::atualizar($idempresa, $idusuario, $dados);

            ctrl::response(['success' => true], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Exclui usuário
     * DELETE /api/usuarios/{id}
     */
    public function excluir($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            // Não permite excluir o próprio usuário
            if ($idusuario == ctrl::usuario()) {
                throw new Exception('Não é possível excluir o próprio usuário');
            }

            UsuariosHandler::excluir($idempresa, $idusuario);

            ctrl::response(['success' => true], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Altera senha do usuário
     * PUT /api/usuarios/{id}/senha
     */
    public function alterarSenha($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados = ctrl::getBody();
            
            if (empty($dados['senha_atual']) || empty($dados['nova_senha'])) {
                throw new Exception('Senha atual e nova senha são obrigatórios');
            }

            UsuariosHandler::alterarSenha($idempresa, $idusuario, $dados['senha_atual'], $dados['nova_senha']);

            ctrl::response(['success' => true, 'message' => 'Senha alterada com sucesso'], 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Atualiza tema do usuário
     * PUT /api/usuarios/{id}/tema
     */
    public function atualizarTema($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados = ctrl::getBody();
            
            if (empty($dados['tema'])) {
                throw new Exception('Tema é obrigatório');
            }

            $usuario = UsuariosHandler::atualizarTema($idempresa, $idusuario, $dados['tema']);

            ctrl::response($usuario, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Inicia habilitação do 2FA
     * POST /api/usuarios/{id}/2fa/habilitar
     */
    public function habilitar2FA($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados2fa = UsuariosHandler::habilitar2FA($idempresa, $idusuario);

            ctrl::response($dados2fa, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Confirma habilitação do 2FA
     * POST /api/usuarios/{id}/2fa/confirmar
     */
    public function confirmar2FA($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados = ctrl::getBody();
            
            if (empty($dados['codigo'])) {
                throw new Exception('Código é obrigatório');
            }

            $resultado = UsuariosHandler::confirmar2FA($idempresa, $idusuario, $dados['codigo']);

            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Desabilita 2FA
     * POST /api/usuarios/{id}/2fa/desabilitar
     */
    public function desabilitar2FA($args)
    {
        try {
            $idempresa = ctrl::empresa();
            $idusuario = $args['id'] ?? null;

            if (empty($idusuario)) {
                throw new Exception('ID do usuário é obrigatório');
            }

            $dados = ctrl::getBody();
            
            if (empty($dados['codigo'])) {
                throw new Exception('Código é obrigatório para desabilitar 2FA');
            }

            $resultado = UsuariosHandler::desabilitar2FA($idempresa, $idusuario, $dados['codigo']);

            ctrl::response($resultado, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
