<?php

namespace src\handlers;

use src\models\SistemasModel;
use src\models\SistemasplanosModel;
use src\models\SistemasAddonsModel;
use core\Database;
use Exception;

/**
 * Handler para lógica de negócio de sistemas
 * Responsabilidade: Validações e orquestração de models
 */
class SistemasHandler {
    
    private $sistemasModel;
    private $planosModel;
    private $addonsModel;

    public function __construct() {
        $this->sistemasModel = new SistemasModel();
        $this->planosModel = new SistemasplanosModel();
        $this->addonsModel = new SistemasAddonsModel();
    }

    /**
     * Lista sistemas com filtros e paginação
     */
    public function listarSistemas($filtros = []) {
        // Validações de filtros
        if (isset($filtros['ativo']) && !in_array($filtros['ativo'], [0, 1])) {
            throw new Exception("Status ativo inválido. Use 0 ou 1");
        }

        // Paginação padrão
        $filtros['limit'] = min($filtros['limit'] ?? 50, 100);
        $filtros['offset'] = max($filtros['offset'] ?? 0, 0);

        $sistemas = $this->sistemasModel->listarTodos($filtros);
        $total = $this->sistemasModel->contarTodos($filtros);

        return [
            'success' => true,
            'data' => $sistemas,
            'total' => $total,
            'pagination' => [
                'limit' => $filtros['limit'],
                'offset' => $filtros['offset'],
                'has_more' => ($filtros['offset'] + $filtros['limit']) < $total
            ]
        ];
    }

    /**
     * Lista apenas sistemas ativos
     */
    public function listarSistemasAtivos() {
        $sistemas = $this->sistemasModel->listarAtivos();

        return [
            'success' => true,
            'data' => $sistemas
        ];
    }

    /**
     * Busca sistema por ID com planos e add-ons
     */
    public function buscarSistemaCompleto($idsistema) {
        $sistema = $this->sistemasModel->buscarPorId($idsistema);
        
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        // Busca planos e add-ons
        $sistema['planos'] = $this->planosModel->listarPorSistema($idsistema);
        $sistema['addons'] = $this->addonsModel->listarPorSistema($idsistema);

        return [
            'success' => true,
            'data' => $sistema
        ];
    }

    /**
     * Pesquisa sistemas por termo
     */
    public function pesquisarSistemas($termo, $filtros = []) {
        if (strlen(trim($termo)) < 2) {
            throw new Exception("Termo de pesquisa deve ter pelo menos 2 caracteres");
        }

        $filtros['limit'] = min($filtros['limit'] ?? 50, 100);
        $filtros['offset'] = max($filtros['offset'] ?? 0, 0);

        $sistemas = $this->sistemasModel->pesquisar($termo, $filtros);

        return [
            'success' => true,
            'data' => $sistemas,
            'termo' => $termo
        ];
    }

    /**
     * Cria novo sistema
     */
    public function criarSistema($dados) {
        // Validações obrigatórias
        $this->validarDadosSistema($dados);

        $idsistema = $this->sistemasModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Sistema criado com sucesso',
            'idsistema' => $idsistema
        ];
    }

    /**
     * Atualiza sistema existente
     */
    public function atualizarSistema($idsistema, $dados) {
        // Verifica se sistema existe
        $sistemaExistente = $this->sistemasModel->buscarPorId($idsistema);
        if (!$sistemaExistente) {
            throw new Exception("Sistema não encontrado");
        }

        // Validações
        $this->validarDadosSistema($dados);

        $this->sistemasModel->atualizar($idsistema, $dados);

        return [
            'success' => true,
            'message' => 'Sistema atualizado com sucesso'
        ];
    }

    /**
     * Exclui sistema (apenas se não tiver assinaturas ativas)
     */
    public function excluirSistema($idsistema) {
        $sistema = $this->sistemasModel->buscarPorId($idsistema);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        // Verifica se pode excluir
        if (!$this->sistemasModel->podeExcluir($idsistema)) {
            throw new Exception("Não é possível excluir sistema com assinaturas ativas");
        }

        $this->sistemasModel->excluir($idsistema);

        return [
            'success' => true,
            'message' => 'Sistema excluído com sucesso'
        ];
    }

    /**
     * Gerencia planos do sistema
     */
    public function listarPlanosSistema($idsistema, $filtros = []) {
        // Verifica se sistema existe
        $sistema = $this->sistemasModel->buscarPorId($idsistema);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        $planos = $this->planosModel->listarPorSistema($idsistema, $filtros);

        return [
            'success' => true,
            'data' => $planos,
            'sistema' => $sistema['nome']
        ];
    }

    /**
     * Cria plano para sistema
     */
    public function criarPlano($dados) {
        // Validações
        $this->validarDadosPlano($dados);

        // Verifica se sistema existe e está ativo
        $sistema = $this->sistemasModel->buscarPorId($dados['idsistema']);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }
        if (!$sistema['ativo']) {
            throw new Exception("Não é possível criar plano para sistema inativo");
        }

        $idplano = $this->planosModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Plano criado com sucesso',
            'idplano' => $idplano
        ];
    }

    /**
     * Atualiza plano existente
     */
    public function atualizarPlano($idplano, $dados) {
        // Verifica se plano existe
        $planoExistente = $this->planosModel->buscarPorId($idplano);
        if (!$planoExistente) {
            throw new Exception("Plano não encontrado");
        }

        // Validações
        $this->validarDadosPlano($dados);

        // Verifica se sistema existe
        $sistema = $this->sistemasModel->buscarPorId($dados['idsistema']);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        $this->planosModel->atualizar($idplano, $dados);

        return [
            'success' => true,
            'message' => 'Plano atualizado com sucesso'
        ];
    }

    /**
     * Exclui plano
     */
    public function excluirPlano($idplano) {
        $plano = $this->planosModel->buscarPorId($idplano);
        if (!$plano) {
            throw new Exception("Plano não encontrado");
        }

        // Verifica se pode excluir
        if (!$this->planosModel->podeExcluir($idplano)) {
            throw new Exception("Não é possível excluir plano com assinaturas ativas");
        }

        $this->planosModel->excluir($idplano);

        return [
            'success' => true,
            'message' => 'Plano excluído com sucesso'
        ];
    }

    /**
     * Gerencia add-ons do sistema
     */
    public function listarAddonsSistema($idsistema, $filtros = []) {
        // Verifica se sistema existe
        $sistema = $this->sistemasModel->buscarPorId($idsistema);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        $addons = $this->addonsModel->listarPorSistema($idsistema, $filtros);

        return [
            'success' => true,
            'data' => $addons,
            'sistema' => $sistema['nome']
        ];
    }

    /**
     * Lista planos do sistema (alias para compatibilidade com Controller)
     */
    public function listarPlanos($idsistema, $filtros = []) {
        return $this->listarPlanosSistema($idsistema, $filtros);
    }

    /**
     * Lista add-ons do sistema (alias para compatibilidade com Controller)
     */
    public function listarAddons($idsistema, $filtros = []) {
        return $this->listarAddonsSistema($idsistema, $filtros);
    }

    /**
     * Cria add-on para sistema
     */
    public function criarAddon($dados) {
        // Validações
        $this->validarDadosAddon($dados);

        // Verifica se sistema existe e está ativo
        $sistema = $this->sistemasModel->buscarPorId($dados['idsistema']);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }
        if (!$sistema['ativo']) {
            throw new Exception("Não é possível criar add-on para sistema inativo");
        }

        $idaddon = $this->addonsModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Add-on criado com sucesso',
            'idaddon' => $idaddon
        ];
    }

    /**
     * Atualiza add-on existente
     */
    public function atualizarAddon($idaddon, $dados) {
        // Verifica se add-on existe
        $addonExistente = $this->addonsModel->buscarPorId($idaddon);
        if (!$addonExistente) {
            throw new Exception("Add-on não encontrado");
        }

        // Validações
        $this->validarDadosAddon($dados);

        // Verifica se sistema existe
        $sistema = $this->sistemasModel->buscarPorId($dados['idsistema']);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        $this->addonsModel->atualizar($idaddon, $dados);

        return [
            'success' => true,
            'message' => 'Add-on atualizado com sucesso'
        ];
    }

    /**
     * Exclui add-on
     */
    public function excluirAddon($idaddon) {
        $addon = $this->addonsModel->buscarPorId($idaddon);
        if (!$addon) {
            throw new Exception("Add-on não encontrado");
        }

        // Verifica se pode excluir
        if (!$this->addonsModel->podeExcluir($idaddon)) {
            throw new Exception("Não é possível excluir add-on com assinaturas ativas");
        }

        $this->addonsModel->excluir($idaddon);

        return [
            'success' => true,
            'message' => 'Add-on excluído com sucesso'
        ];
    }

    /**
     * Validações privadas
     */
    private function validarDadosSistema($dados) {
        if (empty($dados['nome'])) {
            throw new Exception("Nome do sistema é obrigatório");
        }

        if (strlen($dados['nome']) > 120) {
            throw new Exception("Nome do sistema não pode ter mais de 120 caracteres");
        }

        if (isset($dados['categoria']) && strlen($dados['categoria']) > 60) {
            throw new Exception("Categoria não pode ter mais de 60 caracteres");
        }
    }

    private function validarDadosPlano($dados) {
        if (empty($dados['idsistema'])) {
            throw new Exception("ID do sistema é obrigatório");
        }

        if (empty($dados['nome'])) {
            throw new Exception("Nome do plano é obrigatório");
        }

        if (strlen($dados['nome']) > 120) {
            throw new Exception("Nome do plano não pode ter mais de 120 caracteres");
        }

        if (empty($dados['ciclo_cobranca'])) {
            throw new Exception("Ciclo de cobrança é obrigatório");
        }

        $ciclosValidos = ['mensal', 'trimestral', 'semestral', 'anual'];
        if (!in_array($dados['ciclo_cobranca'], $ciclosValidos)) {
            throw new Exception("Ciclo de cobrança inválido. Use: " . implode(', ', $ciclosValidos));
        }

        if (!isset($dados['preco_base_sem_imposto']) || $dados['preco_base_sem_imposto'] < 0) {
            throw new Exception("Preço base deve ser maior ou igual a zero");
        }

        if (!isset($dados['aliquota_imposto_percent']) || $dados['aliquota_imposto_percent'] < 0 || $dados['aliquota_imposto_percent'] > 100) {
            throw new Exception("Alíquota de imposto deve estar entre 0 e 100");
        }
    }

    private function validarDadosAddon($dados) {
        if (empty($dados['idsistema'])) {
            throw new Exception("ID do sistema é obrigatório");
        }

        if (empty($dados['nome'])) {
            throw new Exception("Nome do add-on é obrigatório");
        }

        if (strlen($dados['nome']) > 120) {
            throw new Exception("Nome do add-on não pode ter mais de 120 caracteres");
        }

        if (!isset($dados['preco_sem_imposto']) || $dados['preco_sem_imposto'] < 0) {
            throw new Exception("Preço deve ser maior ou igual a zero");
        }

        if (!isset($dados['aliquota_imposto_percent']) || $dados['aliquota_imposto_percent'] < 0 || $dados['aliquota_imposto_percent'] > 100) {
            throw new Exception("Alíquota de imposto deve estar entre 0 e 100");
        }
    }
}