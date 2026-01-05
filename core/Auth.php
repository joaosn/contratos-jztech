<?php

namespace core;

use \core\Controller as ctrl;
use \src\models\UsuariosModel;
use \src\handlers\UsuariosHandler;

/**
 * Classe de autenticação com validação tripla de token
 * 
 * Validação Tripla:
 * 1. Token da sessão PHP ($_SESSION['token'])
 * 2. Token no banco (usuarios.token)
 * 3. Token do header (Authorization: Bearer xxx) - para APIs
 * 
 * Os três devem coincidir para acesso autorizado.
 */
class Auth extends ctrl
{
    /**
     * Valida token de acesso
     * 
     * @param string|null $token Token do header Authorization
     * @param array $args Argumentos da rota
     */
    public function validaToken($token, $args)
    {
        // VALIDAÇÃO PARA VIEWS (navegação normal via sessão)
        if (!empty($_SESSION['token'])) {
            $tokenSessao = $_SESSION['token'];
            
            // Busca usuário pelo token da sessão
            $usuario = UsuariosModel::buscarPorToken($tokenSessao);
            
            if ($usuario && $usuario['ativo'] == 1) {
                // Verifica se idempresa da sessão coincide (se existir)
                if (!empty($_SESSION['idempresa']) && $_SESSION['idempresa'] == $usuario['idempresa']) {
                    return; // Autorizado via sessão
                }
                
                // Se não tem idempresa na sessão, permite (primeira vez)
                if (empty($_SESSION['idempresa'])) {
                    $_SESSION['idempresa'] = $usuario['idempresa'];
                    $_SESSION['idusuario'] = $usuario['idusuario'];
                    return; // Autorizado
                }
            }
        }

        // VALIDAÇÃO PARA APIs (Bearer token)
        if ($token) {
            $authHeaderParts = explode(' ', $token);
            
            if (count($authHeaderParts) === 2 && strtolower($authHeaderParts[0]) === 'bearer') {
                $tokenBearer = $authHeaderParts[1];
                
                // Busca usuário pelo token Bearer
                $usuario = UsuariosModel::buscarPorToken($tokenBearer);
                
                if ($usuario && $usuario['ativo'] == 1) {
                    // VALIDAÇÃO TRIPLA: Se tem sessão, tokens devem coincidir
                    if (!empty($_SESSION['token']) && $_SESSION['token'] !== $tokenBearer) {
                        self::VALIDATION_API('Tokens não coincidem');
                        return;
                    }
                    
                    // Verifica idempresa da requisição (se fornecido)
                    $idempresaReq = $this->verificarEmpresa($args);
                    if ($idempresaReq !== null && $idempresaReq != $usuario['idempresa']) {
                        self::VALIDATION_API('Acesso negado a esta empresa');
                        return;
                    }
                    
                    // Atualiza sessão com dados do usuário
                    $_SESSION['token'] = $tokenBearer;
                    $_SESSION['idempresa'] = $usuario['idempresa'];
                    $_SESSION['idusuario'] = $usuario['idusuario'];
                    
                    return; // Autorizado via API
                }
            }
        }

        // Se chegou aqui, não autorizado
        self::VALIDATION();
    }

    /**
     * Extrai idempresa dos argumentos ou body da requisição
     */
    public function verificarEmpresa($args)
    {
        $idempresa = null;
        
        // Primeiro tenta pegar da URL
        if (isset($args['idempresa'])) {
            $idempresa = $args['idempresa'];
        }

        // Depois do body (sobrescreve se existir)
        $payload = ctrl::getBody(false);
        if (isset($payload['idempresa'])) {
            $idempresa = $payload['idempresa'];
        }

        return $idempresa;
    }

    /**
     * Retorna empresa do usuário logado
     */
    public static function getEmpresa()
    {
        return $_SESSION['idempresa'] ?? null;
    }

    /**
     * Retorna ID do usuário logado
     */
    public static function getUsuario()
    {
        return $_SESSION['idusuario'] ?? null;
    }

    /**
     * Verifica se usuário está logado
     */
    public static function isLogado()
    {
        return UsuariosHandler::checkLogin();
    }

    /**
     * Redireciona para login (views)
     */
    public static function VALIDATION()
    {
        // Limpa sessão inválida
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        }
        
        ctrl::redirect('login');
    }

    /**
     * Retorna erro JSON (APIs)
     */
    public static function VALIDATION_API($mensagem = 'Não autorizado')
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $mensagem
        ]);
        exit;
    }
}
