<?php

namespace src\handlers;

use src\models\RelatoriosModel;
use core\Database;
use Exception;

/**
 * Handler para lógica de negócios de relatórios
 * Responsabilidade: Validações e orquestração de models
 */
class RelatoriosHandler {

    private $relatoriosModel;

    public function __construct() {
        $this->relatoriosModel = new RelatoriosModel();
    }

    /**
     * Obtém relatório resumido de assinaturas
     */
    public function obterResumoAssinaturas($filtros = []) {
        try {
            $assinaturas = $this->relatoriosModel->assinaturasResumo($filtros);

            $stats = $this->calcularStatsResumo($assinaturas);

            return [
                'success' => true,
                'total' => count($assinaturas),
                'stats' => $stats,
                'assinaturas' => $assinaturas
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Calcula estatísticas do resumo
     */
    private function calcularStatsResumo($assinaturas) {
        $stats = [
            'total' => count($assinaturas),
            'ativas' => 0,
            'suspensas' => 0,
            'canceladas' => 0,
            'receita_total' => 0,
            'receita_media' => 0,
            'receita_maxima' => 0,
            'receita_minima' => 0,
            'total_addons' => 0
        ];

        foreach ($assinaturas as $ass) {
            if ($ass['status'] === 'ativa') $stats['ativas']++;
            if ($ass['status'] === 'suspensa') $stats['suspensas']++;
            if ($ass['status'] === 'cancelada') $stats['canceladas']++;

            $receita = $ass['preco_com_imposto'] + ($ass['custo_addons'] ?? 0);
            $stats['receita_total'] += $receita;
            $stats['receita_maxima'] = max($stats['receita_maxima'], $receita);
            $stats['receita_minima'] = min($stats['receita_minima'] ?? PHP_INT_MAX, $receita);
            $stats['total_addons'] += $ass['total_addons'] ?? 0;
        }

        if (count($assinaturas) > 0) {
            $stats['receita_media'] = round($stats['receita_total'] / count($assinaturas), 2);
        }

        $stats['receita_total'] = round($stats['receita_total'], 2);
        $stats['receita_maxima'] = round($stats['receita_maxima'], 2);
        $stats['receita_minima'] = round($stats['receita_minima'], 2);

        return $stats;
    }

    /**
     * Obtém relatório de receita mensal
     */
    public function obterReceitaMensal($filtros = []) {
        try {
            // Validar datas se informadas
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                if (strtotime($filtros['data_fim']) <= strtotime($filtros['data_inicio'])) {
                    throw new Exception("Data fim deve ser posterior à data início");
                }
            }

            $dados = $this->relatoriosModel->totalMensal($filtros);

            $stats = $this->calcularStatsReceitaMensal($dados);

            return [
                'success' => true,
                'total_meses' => count($dados),
                'stats' => $stats,
                'dados' => $dados
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Calcula estatísticas de receita mensal
     */
    private function calcularStatsReceitaMensal($dados) {
        $stats = [
            'receita_total' => 0,
            'receita_base_total' => 0,
            'receita_addons_total' => 0,
            'receita_media_mensal' => 0,
            'mes_melhor' => null,
            'mes_pior' => null
        ];

        foreach ($dados as $mes) {
            $stats['receita_total'] += $mes['receita_total'] ?? 0;
            $stats['receita_base_total'] += $mes['receita_base'] ?? 0;
            $stats['receita_addons_total'] += $mes['receita_addons'] ?? 0;

            if (!$stats['mes_melhor'] || ($mes['receita_total'] ?? 0) > $stats['mes_melhor']['receita']) {
                $stats['mes_melhor'] = [
                    'mes' => $mes['mes'],
                    'ano' => $mes['ano'],
                    'receita' => $mes['receita_total'] ?? 0
                ];
            }
            if (!$stats['mes_pior'] || ($mes['receita_total'] ?? 0) < $stats['mes_pior']['receita']) {
                $stats['mes_pior'] = [
                    'mes' => $mes['mes'],
                    'ano' => $mes['ano'],
                    'receita' => $mes['receita_total'] ?? 0
                ];
            }
        }

        if (count($dados) > 0) {
            $stats['receita_media_mensal'] = round($stats['receita_total'] / count($dados), 2);
        }

        $stats['receita_total'] = round($stats['receita_total'], 2);
        $stats['receita_base_total'] = round($stats['receita_base_total'], 2);
        $stats['receita_addons_total'] = round($stats['receita_addons_total'], 2);

        return $stats;
    }

    /**
     * Obtém ranking de sistemas
     */
    public function obterSistemasVendidos() {
        try {
            $sistemas = $this->relatoriosModel->sistemasMaisVendidos();

            $stats = $this->calcularStatsSistemas($sistemas);

            return [
                'success' => true,
                'total_sistemas' => count($sistemas),
                'stats' => $stats,
                'sistemas' => $sistemas
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Calcula estatísticas de sistemas
     */
    private function calcularStatsSistemas($sistemas) {
        $stats = [
            'total_sistemas' => count($sistemas),
            'total_assinaturas' => 0,
            'receita_total' => 0,
            'receita_media' => 0,
            'sistema_top' => null
        ];

        foreach ($sistemas as $sistema) {
            $stats['total_assinaturas'] += $sistema['total_assinaturas'] ?? 0;
            $stats['receita_total'] += $sistema['receita_mensal'] ?? 0;

            if (!$stats['sistema_top'] || ($sistema['receita_mensal'] ?? 0) > $stats['sistema_top']['receita']) {
                $stats['sistema_top'] = [
                    'nome' => $sistema['sistema_nome'],
                    'receita' => $sistema['receita_mensal'] ?? 0,
                    'assinaturas' => $sistema['total_assinaturas'] ?? 0
                ];
            }
        }

        if (count($sistemas) > 0) {
            $stats['receita_media'] = round($stats['receita_total'] / count($sistemas), 2);
        }

        $stats['receita_total'] = round($stats['receita_total'], 2);

        return $stats;
    }

    /**
     * Obtém dashboard com todas as estatísticas
     */
    public function obterEstatisticasDashboard() {
        try {
            $stats = $this->relatoriosModel->dashboardStats();

            $clientes = $this->relatoriosModel->clientesAtivos();
            $sistemas = $this->relatoriosModel->sistemasMaisVendidos();

            return [
                'success' => true,
                'stats_gerais' => $stats,
                'total_clientes' => count($clientes),
                'total_sistemas' => count($sistemas),
                'clientes_top' => array_slice($clientes, 0, 5),
                'sistemas_top' => array_slice($sistemas, 0, 5)
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Relatório de clientes ativos
     */
    public function clientesAtivos($filtros = []) {
        try {
            $clientes = $this->relatoriosModel->clientesAtivos($filtros);
            
            return [
                'success' => true,
                'total' => count($clientes),
                'data' => $clientes
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Relatório de sistemas vendidos
     */
    public function sistemasVendidos() {
        try {
            $sistemas = $this->relatoriosModel->sistemasMaisVendidos();
            
            return [
                'success' => true,
                'total' => count($sistemas),
                'data' => $sistemas
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Relatório de receita por período
     */
    public function receitaPeriodo($dataInicio, $dataFim) {
        try {
            // Validar datas
            if (strtotime($dataFim) <= strtotime($dataInicio)) {
                throw new Exception("Data fim deve ser posterior à data início");
            }

            $dados = $this->relatoriosModel->totalMensal([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);

            $totalReceita = array_sum(array_column($dados, 'total_receita'));

            return [
                'success' => true,
                'periodo' => [
                    'inicio' => $dataInicio,
                    'fim' => $dataFim
                ],
                'total_receita' => round($totalReceita, 2),
                'detalhamento' => $dados
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}