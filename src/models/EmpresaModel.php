<?php

namespace src\models;

use core\Database;
use core\Model;

/**
 * Model para a tabela 'empresa' do banco de dados.
 * Tenant central do sistema multi-tenant.
 */
class EmpresaModel extends Model
{
    /**
     * Busca empresa por ID
     */
    public static function buscarPorId($idempresa)
    {
        $resultado = Database::switchParams(
            ['idempresa' => $idempresa],
            'empresa/select_by_id',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0] ?? null;
    }

    /**
     * Busca empresa por CNPJ
     */
    public static function buscarPorCnpj($cnpj)
    {
        $resultado = Database::switchParams(
            ['cnpj' => $cnpj],
            'empresa/select_by_cnpj',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0] ?? null;
    }

    /**
     * Lista todas as empresas
     */
    public static function listar()
    {
        $resultado = Database::switchParams(
            [],
            'empresa/select_all',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'] ?? [];
    }

    /**
     * Conta total de empresas
     */
    public static function contar()
    {
        $resultado = Database::switchParams(
            [],
            'empresa/count_all',
            true, false, false
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'][0]['total'] ?? 0;
    }

    /**
     * Insere nova empresa
     */
    public static function inserir($dados)
    {
        $resultado = Database::switchParams(
            [
                'nome' => $dados['nome'],
                'nome_fantasia' => $dados['nome_fantasia'] ?? null,
                'cnpj' => $dados['cnpj'],
                'email' => $dados['email'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => $dados['ativo'] ?? 1
            ],
            'empresa/insert',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Atualiza empresa
     */
    public static function atualizar($idempresa, $dados)
    {
        $resultado = Database::switchParams(
            [
                'idempresa' => $idempresa,
                'nome' => $dados['nome'],
                'nome_fantasia' => $dados['nome_fantasia'] ?? null,
                'cnpj' => $dados['cnpj'],
                'email' => $dados['email'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => $dados['ativo'] ?? 1
            ],
            'empresa/update',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }

    /**
     * Exclui empresa
     */
    public static function excluir($idempresa)
    {
        $resultado = Database::switchParams(
            ['idempresa' => $idempresa],
            'empresa/delete',
            true, false, true
        );

        if ($resultado['error']) {
            throw new \Exception($resultado['error']);
        }

        return $resultado['retorno'];
    }
}
