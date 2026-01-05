<?php

namespace src\models;

use core\Database;
use core\Model;

/**
 * Model para a tabela 'usuarios' do banco de dados.
 * Segue padrão multi-tenant com idempresa.
 */
class UsuariosModel extends Model
{
    /**
     * Busca usuário por ID
     */
    public static function buscarPorId($idempresa, $idusuario)
    {
        $resultado = Database::switchParams(
            ['idempresa' => $idempresa, 'idusuario' => $idusuario],
            'usuarios/select_by_id',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0] ?? null;
    }

    /**
     * Busca usuário por email
     */
    public static function buscarPorEmail($email)
    {
        $resultado = Database::switchParams(
            ['email' => $email],
            'usuarios/select_by_email',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0] ?? null;
    }

    /**
     * Busca usuário por token
     */
    public static function buscarPorToken($token)
    {
        $resultado = Database::switchParams(
            ['token' => $token],
            'usuarios/select_by_token',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0] ?? null;
    }

    /**
     * Lista todos os usuários de uma empresa
     */
    public static function listar($idempresa)
    {
        $resultado = Database::switchParams(
            ['idempresa' => $idempresa],
            'usuarios/select_all',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'] ?? [];
    }

    /**
     * Insere novo usuário
     */
    public static function inserir($dados)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $dados['idempresa'],
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha_hash' => $dados['senha_hash'],
                'tema' => $dados['tema'] ?? 'dark',
                'ativo' => $dados['ativo'] ?? 1
            ],
            'usuarios/insert',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza usuário
     */
    public static function atualizar($idempresa, $idusuario, $dados)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'idusuario' => $idusuario,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'tema' => $dados['tema'] ?? 'dark',
                'ativo' => $dados['ativo'] ?? 1
            ],
            'usuarios/update',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza token do usuário
     */
    public static function atualizarToken($idempresa, $idusuario, $token)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'idusuario' => $idusuario,
                'token' => $token
            ],
            'usuarios/update_token',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza configurações de 2FA
     */
    public static function atualizar2FA($idempresa, $idusuario, $totpHabilitado, $totpSecret = null)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'idusuario' => $idusuario,
                'totp_habilitado' => $totpHabilitado ? 1 : 0,
                'totp_secret' => $totpSecret
            ],
            'usuarios/update_2fa',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza tema do usuário
     */
    public static function atualizarTema($idempresa, $idusuario, $tema)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'idusuario' => $idusuario,
                'tema' => $tema
            ],
            'usuarios/update_tema',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza senha do usuário
     */
    public static function atualizarSenha($idempresa, $idusuario, $senhaHash)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'idusuario' => $idusuario,
                'senha_hash' => $senhaHash
            ],
            'usuarios/update_senha',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Logout - limpa token
     */
    public static function logout($token)
    {
        $resultado = Database::switchParams(
            ['token' => $token],
            'usuarios/logout',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Exclui usuário
     */
    public static function excluir($idempresa, $idusuario)
    {
        $resultado = Database::switchParams(
            ['idempresa' => $idempresa, 'idusuario' => $idusuario],
            'usuarios/delete',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }
}
