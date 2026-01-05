<?php

/**
 * Handler de manipulação de Usuarios
 * Padrão multi-tenant com 2FA
 */

namespace src\handlers;

use src\models\UsuariosModel;
use src\handlers\service\TwoFactorAuthService;

class UsuariosHandler
{
    /**
     * Verifica se o usuário está logado com base no token de sessão.
     */
    public static function checkLogin()
    {
        if (!empty($_SESSION['token'])) {
            $token = $_SESSION['token'];
            $usuario = UsuariosModel::buscarPorToken($token);
            
            if ($usuario && $usuario['ativo'] == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retorna dados do usuário logado
     */
    public static function getUsuarioLogado()
    {
        if (!empty($_SESSION['token'])) {
            return UsuariosModel::buscarPorToken($_SESSION['token']);
        }
        return null;
    }

    /**
     * Verifica credenciais de login (email + senha)
     * Retorna usuário se válido, ou array com erro
     */
    public static function verificarCredenciais($email, $senha)
    {
        $usuario = UsuariosModel::buscarPorEmail($email);
        
        if (empty($usuario)) {
            return ['error' => 'Usuário não encontrado'];
        }

        if ($usuario['ativo'] != 1) {
            return ['error' => 'Usuário inativo'];
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            return ['error' => 'Senha incorreta'];
        }

        return $usuario;
    }

    /**
     * Realiza login completo (sem 2FA ou após validação 2FA)
     */
    public static function realizarLogin($usuario)
    {
        // Gera novo token
        $token = bin2hex(random_bytes(32));
        
        // Salva token no banco
        UsuariosModel::atualizarToken(
            $usuario['idempresa'], 
            $usuario['idusuario'], 
            $token
        );

        // Salva token na sessão
        $_SESSION['token'] = $token;
        $_SESSION['idusuario'] = $usuario['idusuario'];
        $_SESSION['idempresa'] = $usuario['idempresa'];

        // Retorna usuário com token
        $usuario['token'] = $token;
        unset($usuario['senha_hash']);
        unset($usuario['totp_secret']);
        
        return $usuario;
    }

    /**
     * Login completo: verifica credenciais + 2FA se habilitado
     */
    public static function login($email, $senha, $codigo2fa = null)
    {
        // Verifica credenciais
        $usuario = self::verificarCredenciais($email, $senha);
        
        if (isset($usuario['error'])) {
            return $usuario;
        }

        // Verifica se 2FA está habilitado
        if ($usuario['totp_habilitado'] == 1) {
            // Se código 2FA não foi fornecido, retorna indicando necessidade
            if (empty($codigo2fa)) {
                return [
                    'requer_2fa' => true,
                    'message' => 'Código 2FA necessário'
                ];
            }

            // Valida código 2FA
            if (!TwoFactorAuthService::verificarCodigo($usuario['totp_secret'], $codigo2fa)) {
                return ['error' => 'Código 2FA inválido'];
            }
        }

        // Login bem-sucedido
        return self::realizarLogin($usuario);
    }

    /**
     * Logout do usuário
     */
    public static function logout()
    {
        if (!empty($_SESSION['token'])) {
            UsuariosModel::logout($_SESSION['token']);
        }
        
        unset($_SESSION['token']);
        unset($_SESSION['idusuario']);
        unset($_SESSION['idempresa']);
        
        session_destroy();
    }

    /**
     * Atualiza tema do usuário
     */
    public static function atualizarTema($idempresa, $idusuario, $tema)
    {
        UsuariosModel::atualizarTema($idempresa, $idusuario, $tema);
        return UsuariosModel::buscarPorId($idempresa, $idusuario);
    }

    /**
     * Lista usuários da empresa
     */
    public static function listar($idempresa)
    {
        $usuarios = UsuariosModel::listar($idempresa);
        
        // Remove campos sensíveis
        foreach ($usuarios as &$usuario) {
            unset($usuario['senha_hash']);
            unset($usuario['totp_secret']);
        }
        
        return $usuarios;
    }

    /**
     * Busca usuário por ID
     */
    public static function buscar($idempresa, $idusuario)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if ($usuario) {
            unset($usuario['senha_hash']);
            unset($usuario['totp_secret']);
        }
        
        return $usuario;
    }

    /**
     * Cria novo usuário
     */
    public static function criar($dados)
    {
        // Verifica se email já existe
        $existente = UsuariosModel::buscarPorEmail($dados['email']);
        if ($existente) {
            throw new \Exception('Email já cadastrado');
        }

        // Hash da senha
        $dados['senha_hash'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        unset($dados['senha']);

        return UsuariosModel::inserir($dados);
    }

    /**
     * Atualiza usuário
     */
    public static function atualizar($idempresa, $idusuario, $dados)
    {
        // Verifica se usuário existe
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        // Se está alterando email, verifica duplicidade
        if (isset($dados['email']) && $dados['email'] !== $usuario['email']) {
            $existente = UsuariosModel::buscarPorEmail($dados['email']);
            if ($existente && $existente['idusuario'] != $idusuario) {
                throw new \Exception('Email já cadastrado');
            }
        }

        return UsuariosModel::atualizar($idempresa, $idusuario, $dados);
    }

    /**
     * Altera senha do usuário
     */
    public static function alterarSenha($idempresa, $idusuario, $senhaAtual, $novaSenha)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        if (!password_verify($senhaAtual, $usuario['senha_hash'])) {
            throw new \Exception('Senha atual incorreta');
        }

        $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        return UsuariosModel::atualizarSenha($idempresa, $idusuario, $novoHash);
    }

    /**
     * Exclui usuário
     */
    public static function excluir($idempresa, $idusuario)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        return UsuariosModel::excluir($idempresa, $idusuario);
    }

    /**
     * Habilita 2FA para o usuário
     */
    public static function habilitar2FA($idempresa, $idusuario)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        // Gera secret e QR Code
        $dados2fa = TwoFactorAuthService::gerarSecret($usuario['email']);

        // Salva secret no banco (ainda não habilitado)
        UsuariosModel::atualizar2FA($idempresa, $idusuario, false, $dados2fa['secret']);

        return $dados2fa;
    }

    /**
     * Confirma habilitação do 2FA após validar código
     */
    public static function confirmar2FA($idempresa, $idusuario, $codigo)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        if (empty($usuario['totp_secret'])) {
            throw new \Exception('2FA não foi iniciado');
        }

        // Valida código
        if (!TwoFactorAuthService::verificarCodigo($usuario['totp_secret'], $codigo)) {
            throw new \Exception('Código inválido');
        }

        // Habilita 2FA
        UsuariosModel::atualizar2FA($idempresa, $idusuario, true, $usuario['totp_secret']);

        return ['success' => true, 'message' => '2FA habilitado com sucesso'];
    }

    /**
     * Desabilita 2FA
     */
    public static function desabilitar2FA($idempresa, $idusuario, $codigo)
    {
        $usuario = UsuariosModel::buscarPorId($idempresa, $idusuario);
        
        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        if ($usuario['totp_habilitado'] != 1) {
            throw new \Exception('2FA não está habilitado');
        }

        // Valida código antes de desabilitar
        if (!TwoFactorAuthService::verificarCodigo($usuario['totp_secret'], $codigo)) {
            throw new \Exception('Código inválido');
        }

        // Desabilita 2FA
        UsuariosModel::atualizar2FA($idempresa, $idusuario, false, null);

        return ['success' => true, 'message' => '2FA desabilitado com sucesso'];
    }
}
