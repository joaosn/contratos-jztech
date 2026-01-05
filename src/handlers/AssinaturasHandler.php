<?php

namespace src\handlers;

use src\models\AssinaturasModel;
use src\models\AssinaturasAddonsModel;
use src\models\PrecosHistoricoModel;
use src\models\ClientesModel;
use src\models\SistemasModel;
use src\models\SistemasplanosModel;
use core\Database;
use Exception;

/**
 * Handler para lógica de negócios de assinaturas
 * Responsabilidade: Validações e orquestração de models
 */
class AssinaturasHandler {

    private $assinaturasModel;
    private $assinaturasAddonsModel;
    private $clientesModel;
    private $sistemasModel;
    private $sistemasplanosModel;

    public function __construct() {
        $this->assinaturasModel = new AssinaturasModel();
        $this->assinaturasAddonsModel = new AssinaturasAddonsModel();
        $this->clientesModel = new ClientesModel();
        $this->sistemasModel = new SistemasModel();
        $this->sistemasplanosModel = new SistemasplanosModel();
    }

    /**
     * Lista assinaturas com filtros e paginação
     */
    public function listarAssinaturas($filtros = []) {
        // Validações de filtros
        if (isset($filtros['status']) && !$this->validarStatus($filtros['status'])) {
            throw new Exception("Status inválido. Valores permitidos: ativa, suspensa, cancelada, expirada");
        }

        // Paginação padrão
        $filtros['limit'] = min($filtros['limit'] ?? 50, 100);
        $filtros['offset'] = max($filtros['offset'] ?? 0, 0);

        $assinaturas = $this->assinaturasModel->listarTodas($filtros);
        $total = $this->assinaturasModel->contarTodas($filtros);

        return [
            'success' => true,
            'data' => $assinaturas,
            'total' => $total,
            'pagination' => [
                'limit' => $filtros['limit'],
                'offset' => $filtros['offset'],
                'has_more' => ($filtros['offset'] + $filtros['limit']) < $total
            ]
        ];
    }

    /**
     * Busca assinatura por ID com add-ons
     */
    public function buscarAssinaturaCompleta($idassinatura) {
        $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
        
        if (!$assinatura) {
            throw new Exception("Assinatura não encontrada");
        }

        // Busca add-ons
        $assinatura['addons'] = $this->assinaturasAddonsModel->listarPorAssinatura($idassinatura);

        // Busca dados do cliente
        $cliente = $this->clientesModel->buscarPorId($assinatura['idcliente']);
        $assinatura['cliente'] = $cliente ? [
            'idcliente' => $cliente['idcliente'],
            'nome' => $cliente['nome'],
            'cpf_cnpj' => $cliente['cpf_cnpj']
        ] : null;

        // Busca dados do sistema
        $sistema = $this->sistemasModel->buscarPorId($assinatura['idsistema']);
        $assinatura['sistema'] = $sistema ? [
            'idsistema' => $sistema['idsistema'],
            'nome' => $sistema['nome']
        ] : null;

        return [
            'success' => true,
            'data' => $assinatura
        ];
    }

    /**
     * Atualiza status da assinatura (público)
     */
    public function atualizarStatus($idassinatura, $novoStatus) {
        return $this->alterarStatus($idassinatura, $novoStatus);
    }

    /**
     * Lista add-ons da assinatura
     */
    public function listarAddons($idassinatura) {
        // Verifica se assinatura existe
        $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
        if (!$assinatura) {
            throw new Exception("Assinatura não encontrada");
        }

        $addons = $this->assinaturasAddonsModel->listarPorAssinatura($idassinatura);

        return [
            'success' => true,
            'data' => $addons
        ];
    }

    /**
     * Validações gerais de assinatura
     */
    private function validarAssinatura($dados, $isEdicao = false) {
        // Cliente existe?
        $cliente = $this->clientesModel->buscarPorId($dados['idcliente']);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        // Cliente ativo?
        if (!$cliente['ativo']) {
            throw new Exception("Cliente inativo não pode contratar assinaturas");
        }

        // Sistema existe?
        $sistema = $this->sistemasModel->buscarPorId($dados['idsistema']);
        if (!$sistema) {
            throw new Exception("Sistema não encontrado");
        }

        // Sistema ativo?
        if (!$sistema['ativo']) {
            throw new Exception("Sistema inativo não pode ser contratado");
        }

        // Datas válidas?
        if (!$this->validarDatas($dados['data_inicio'], $dados['data_fim'] ?? null)) {
            throw new Exception("Data de início deve ser antes de data de fim");
        }

        // Dia vencimento válido?
        if (!$this->validarDiaVencimento($dados['dia_vencimento'])) {
            throw new Exception("Dia de vencimento deve estar entre 1 e 28");
        }

        // Ciclo cobrança válido?
        if (!$this->validarCicloCobranca($dados['ciclo_cobranca'])) {
            throw new Exception("Ciclo de cobrança inválido. Valores permitidos: mensal, trimestral, semestral, anual");
        }

        // Status válido?
        if (!$this->validarStatus($dados['status'] ?? 'ativa')) {
            throw new Exception("Status inválido. Valores permitidos: ativa, suspensa, cancelada, expirada");
        }

        // Plano existe (se informado)?
        if (!empty($dados['idplano'])) {
            $plano = $this->sistemasplanosModel->buscarPorId($dados['idplano']);
            if (!$plano) {
                throw new Exception("Plano não encontrado");
            }

            // Plano pertence ao sistema?
            if ($plano['idsistema'] != $dados['idsistema']) {
                throw new Exception("Plano não pertence ao sistema selecionado");
            }
        }

        // Preço válido?
        if (isset($dados['preco_sem_imposto']) && $dados['preco_sem_imposto'] < 0) {
            throw new Exception("Preço não pode ser negativo");
        }

        // Alíquota válida?
        if (isset($dados['aliquota_imposto_percent'])) {
            if ($dados['aliquota_imposto_percent'] < 0 || $dados['aliquota_imposto_percent'] > 100) {
                throw new Exception("Alíquota de imposto deve estar entre 0 e 100%");
            }
        }

        return true;
    }

    /**
     * Valida datas (data_inicio < data_fim)
     */
    private function validarDatas($data_inicio, $data_fim = null) {
        $inicio = strtotime($data_inicio);
        
        if (!$inicio) {
            throw new Exception("Data de início inválida");
        }

        // Se data_fim não é informada, é válido (assinatura aberta)
        if ($data_fim === null || $data_fim === '') {
            return true;
        }

        $fim = strtotime($data_fim);
        
        if (!$fim) {
            throw new Exception("Data de fim inválida");
        }

        if ($fim <= $inicio) {
            throw new Exception("Data de fim deve ser posterior à data de início");
        }

        return true;
    }

    /**
     * Valida dia de vencimento (1-28)
     */
    private function validarDiaVencimento($dia) {
        return is_numeric($dia) && $dia >= 1 && $dia <= 28;
    }

    /**
     * Valida ciclo de cobrança
     */
    private function validarCicloCobranca($ciclo) {
        $ciclosValidos = ['mensal', 'trimestral', 'semestral', 'anual'];
        return in_array($ciclo, $ciclosValidos);
    }

    /**
     * Valida status da assinatura
     */
    private function validarStatus($status) {
        $statusValidos = ['ativa', 'suspensa', 'cancelada', 'expirada'];
        return in_array($status, $statusValidos);
    }

    /**
     * Cria nova assinatura
     */
    public function criarAssinatura($dados) {
        try {
            // Validações
            $this->validarAssinatura($dados);

            // Insere assinatura
            $idassinatura = $this->assinaturasModel->inserir($dados);

            return [
                'success' => true,
                'message' => 'Assinatura criada com sucesso',
                'idassinatura' => $idassinatura
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Atualiza assinatura existente
     */
    public function atualizarAssinatura($idassinatura, $dados) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Validações
            $this->validarAssinatura($dados, true);

            // Atualiza
            $this->assinaturasModel->atualizar($idassinatura, $dados);

            return [
                'success' => true,
                'message' => 'Assinatura atualizada com sucesso'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Altera status da assinatura
     */
    public function alterarStatus($idassinatura, $novoStatus) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Status válido?
            if (!$this->validarStatus($novoStatus)) {
                throw new Exception("Status inválido");
            }

            // Status atual é igual ao novo?
            if ($assinatura['status'] === $novoStatus) {
                throw new Exception("Assinatura já possui este status");
            }

            // Validações de transição
            $this->validarTransicaoStatus($assinatura['status'], $novoStatus);

            // Atualiza status
            $this->assinaturasModel->atualizarStatus($idassinatura, $novoStatus);

            return [
                'success' => true,
                'message' => 'Status alterado para: ' . $novoStatus
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Valida transições de status permitidas
     */
    private function validarTransicaoStatus($statusAtual, $statusNovo) {
        // Transições permitidas
        $transicoesPermitidas = [
            'ativa' => ['suspensa', 'cancelada'],
            'suspensa' => ['ativa', 'cancelada'],
            'cancelada' => [], // Cancelada não pode mudar
            'expirada' => ['ativa'] // Apenas renovar
        ];

        if (!isset($transicoesPermitidas[$statusAtual]) || 
            !in_array($statusNovo, $transicoesPermitidas[$statusAtual])) {
            throw new Exception("Transição de status '$statusAtual' para '$statusNovo' não permitida");
        }

        return true;
    }

    /**
     * Adiciona add-on a uma assinatura
     */
    public function adicionarAddon($idassinatura, $idaddon, $quantidade = 1, $preco_unitario = null) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Add-on existe e é válido?
            $addon = $this->verificarAddon($idaddon);

            // Já possui este add-on?
            $addonExistente = $this->assinaturasAddonsModel->buscarPorId($idassinatura, $idaddon);
            if ($addonExistente) {
                throw new Exception("Este add-on já foi adicionado à assinatura");
            }

            // Quantidade válida?
            if (!is_numeric($quantidade) || $quantidade <= 0) {
                throw new Exception("Quantidade deve ser um número positivo");
            }

            // Insere add-on
            $this->assinaturasAddonsModel->inserir([
                'idassinatura' => $idassinatura,
                'idaddon' => $idaddon,
                'quantidade' => $quantidade,
                'preco_unitario' => $preco_unitario
            ]);

            return [
                'success' => true,
                'message' => 'Add-on adicionado à assinatura com sucesso'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove add-on de uma assinatura
     */
    public function removerAddon($idassinatura, $idaddon) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Add-on existe na assinatura?
            $addon = $this->assinaturasAddonsModel->buscarPorId($idassinatura, $idaddon);
            if (!$addon) {
                throw new Exception("Add-on não encontrado nesta assinatura");
            }

            // Remove
            $this->assinaturasAddonsModel->excluir($idassinatura, $idaddon);

            return [
                'success' => true,
                'message' => 'Add-on removido da assinatura com sucesso'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Atualiza quantidade/preço de add-on
     */
    public function atualizarAddon($idassinatura, $idaddon, $quantidade = null, $preco_unitario = null) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Add-on existe na assinatura?
            $addon = $this->assinaturasAddonsModel->buscarPorId($idassinatura, $idaddon);
            if (!$addon) {
                throw new Exception("Add-on não encontrado nesta assinatura");
            }

            // Quantidade válida?
            if ($quantidade !== null) {
                if (!is_numeric($quantidade) || $quantidade <= 0) {
                    throw new Exception("Quantidade deve ser um número positivo");
                }
            }

            // Preço válido?
            if ($preco_unitario !== null) {
                if ($preco_unitario < 0) {
                    throw new Exception("Preço não pode ser negativo");
                }
            }

            // Monta dados para atualização
            $dados = [];
            if ($quantidade !== null) $dados['quantidade'] = $quantidade;
            if ($preco_unitario !== null) $dados['preco_unitario'] = $preco_unitario;

            // Atualiza
            $this->assinaturasAddonsModel->atualizar($idassinatura, $idaddon, $dados);

            return [
                'success' => true,
                'message' => 'Add-on atualizado com sucesso'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Valida add-on pertence ao sistema da assinatura
     */
    private function verificarAddon($idaddon) {
        // Aqui você faria uma query para buscar o add-on
        // Por enquanto apenas valida que existe
        // Implemente conforme sua lógica
        return true;
    }

    /**
     * Calcula custo total da assinatura (base + add-ons)
     */
    public function calcularCustoTotal($idassinatura) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Calcula total
            $total = $this->assinaturasAddonsModel->calcularTotal($idassinatura);

            return [
                'success' => true,
                'idassinatura' => $idassinatura,
                'total' => $total
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Exclui assinatura com validações
     */
    public function excluirAssinatura($idassinatura) {
        try {
            // Assinatura existe?
            $assinatura = $this->assinaturasModel->buscarPorId($idassinatura);
            if (!$assinatura) {
                throw new Exception("Assinatura não encontrada");
            }

            // Não permite excluir assinatura ativa
            if ($assinatura['status'] === 'ativa') {
                throw new Exception("Não é possível excluir assinatura ativa. Suspenda ou cancele primeiro.");
            }

            // Remove add-ons primeiro
            $addons = $this->assinaturasAddonsModel->listarPorAssinatura($idassinatura);
            foreach ($addons as $addon) {
                $this->assinaturasAddonsModel->excluir($idassinatura, $addon['idaddon']);
            }

            // Remove assinatura
            $this->assinaturasModel->excluir($idassinatura);

            return [
                'success' => true,
                'message' => 'Assinatura excluída com sucesso'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}