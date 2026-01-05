<?php

/**
 * Handler de manipulação de Empresa (Tenant)
 */

namespace src\handlers;

use src\models\EmpresaModel;

class EmpresaHandler
{
    /**
     * Lista todas as empresas
     */
    public static function listar()
    {
        return EmpresaModel::listar();
    }

    /**
     * Busca empresa por ID
     */
    public static function buscar($idempresa)
    {
        return EmpresaModel::buscarPorId($idempresa);
    }

    /**
     * Cria nova empresa
     */
    public static function criar($dados)
    {
        // Valida campos obrigatórios
        if (empty($dados['nome'])) {
            throw new \Exception('Nome é obrigatório');
        }

        if (empty($dados['cnpj'])) {
            throw new \Exception('CNPJ é obrigatório');
        }

        // Verifica se CNPJ já existe
        $existente = EmpresaModel::buscarPorCnpj($dados['cnpj']);
        if ($existente) {
            throw new \Exception('CNPJ já cadastrado');
        }

        return EmpresaModel::inserir($dados);
    }

    /**
     * Atualiza empresa
     */
    public static function atualizar($idempresa, $dados)
    {
        // Verifica se empresa existe
        $empresa = EmpresaModel::buscarPorId($idempresa);
        if (!$empresa) {
            throw new \Exception('Empresa não encontrada');
        }

        // Se está alterando CNPJ, verifica duplicidade
        if (isset($dados['cnpj']) && $dados['cnpj'] !== $empresa['cnpj']) {
            $existente = EmpresaModel::buscarPorCnpj($dados['cnpj']);
            if ($existente && $existente['idempresa'] != $idempresa) {
                throw new \Exception('CNPJ já cadastrado');
            }
        }

        return EmpresaModel::atualizar($idempresa, $dados);
    }

    /**
     * Exclui empresa
     */
    public static function excluir($idempresa)
    {
        $empresa = EmpresaModel::buscarPorId($idempresa);
        
        if (!$empresa) {
            throw new \Exception('Empresa não encontrada');
        }

        // TODO: Verificar se há dados vinculados antes de excluir
        
        return EmpresaModel::excluir($idempresa);
    }

    /**
     * Conta total de empresas
     */
    public static function contar()
    {
        return EmpresaModel::contar();
    }
}
