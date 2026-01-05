<?php

namespace src\controllers;

use \core\Controller as ctrl;
use src\handlers\RelatoriosHandler;
use Exception;

/**
 * DashboardController - Responsável por exibir estatísticas e relatórios
 * ✅ ARQUITETURA CORRETA: Controller → Handler → Model
 */
class DashboardController extends ctrl
{
    private RelatoriosHandler $handler;

    public function __construct()
    {
        parent::__construct();
        $this->handler = new RelatoriosHandler();
    }

    /**
     * Renderiza o dashboard
     * GET /dashboard (privado = true)
     */
    public function index()
    {
        $this->render('dashboard');
    }

    /**
     * Obtém estatísticas do dashboard (API)
     * GET /api/dashboard/stats
     */
    public function obterEstatisticas()
    {
        try {
            $dados = $this->handler->obterEstatisticasDashboard();
            ctrl::response($dados, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Relatório de clientes ativos
     * GET /api/relatorios/clientes-ativos
     */
    public function clientesAtivos()
    {
        try {
            $filtros = [
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0
            ];

            $dados = $this->handler->clientesAtivos($filtros);
            ctrl::response($dados, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Relatório de sistemas vendidos
     * GET /api/relatorios/sistemas-vendidos
     */
    public function sistemasVendidos()
    {
        try {
            $dados = $this->handler->sistemasVendidos();
            ctrl::response($dados, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * Relatório de receita por período
     * GET /api/relatorios/receita-periodo?inicio=2024-01-01&fim=2024-12-31
     */
    public function receitaPeriodo()
    {
        try {
            $inicio = $_GET['inicio'] ?? date('Y-m-01');
            $fim = $_GET['fim'] ?? date('Y-m-t');

            $dados = $this->handler->receitaPeriodo($inicio, $fim);
            ctrl::response($dados, 200);
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
