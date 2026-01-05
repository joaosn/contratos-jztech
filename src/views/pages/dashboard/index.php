<?php $render('header'); ?>

<h2 class="mb-4">
    <i class="fas fa-chart-line"></i> Dashboard
    <small class="text-muted" style="font-size: 0.6em;">
        <i class="fas fa-sync-alt"></i> Atualiza automaticamente a cada 30s
    </small>
</h2>

<!-- Estatísticas Principais -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h6 text-muted mb-1">Clientes Ativos</div>
                        <div class="h3 mb-0 text-primary" id="stat-clientes">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-primary opacity-50">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h6 text-muted mb-1">Assinaturas Ativas</div>
                        <div class="h3 mb-0 text-success" id="stat-assinaturas">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-success opacity-50">
                        <i class="fas fa-file-contract fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h6 text-muted mb-1">Sistemas Cadastrados</div>
                        <div class="h3 mb-0 text-info" id="stat-sistemas">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-info opacity-50">
                        <i class="fas fa-cogs fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h6 text-muted mb-1">Receita Mensal</div>
                        <div class="h3 mb-0 text-warning" id="stat-receita">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-warning opacity-50">
                        <i class="fas fa-dollar-sign fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos e Listas -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Assinaturas por Status
                </h5>
            </div>
            <div class="card-body">
                <canvas id="assinaturasChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy"></i> Top 5 Sistemas
                </h5>
            </div>
            <div class="card-body" id="top-sistemas">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas e Próximos Vencimentos -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Assinaturas Vencendo em 7 dias
                </h5>
            </div>
            <div class="card-body" id="lista-vencendo">
                <div class="text-center py-3">
                    <div class="spinner-border text-warning" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Últimas Assinaturas
                    </h5>
                    <a href="<?php echo $base; ?>/assinaturas" class="btn btn-sm btn-outline-primary">
                        Ver Todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaUltimasAssinaturas">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Sistema</th>
                            <th>Status</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary { border-left: 4px solid #667eea !important; }
    .border-left-success { border-left: 4px solid #4caf50 !important; }
    .border-left-info { border-left: 4px solid #17a2b8 !important; }
    .border-left-warning { border-left: 4px solid #ff9800 !important; }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
const BASE_URL = '<?php echo $base; ?>';

document.addEventListener('DOMContentLoaded', function() {
    carregarDashboard();
    setInterval(carregarDashboard, 30000); // Atualiza a cada 30s
});

async function carregarDashboard() {
    try {
        const response = await fetch(`${BASE_URL}/api/dashboard/stats`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const data = await response.json();
        
        if (data.success) {
            atualizarEstatisticas(data);
            atualizarTopSistemas(data.sistemas_top || []);
            atualizarGrafico(data.stats_gerais || {});
        }
        
        carregarAssinaturasVencendo();
        carregarUltimasAssinaturas();
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

function atualizarEstatisticas(data) {
    const stats = data.stats_gerais || {};
    document.getElementById('stat-clientes').textContent = data.total_clientes || 0;
    document.getElementById('stat-sistemas').textContent = data.total_sistemas || 0;
    document.getElementById('stat-assinaturas').textContent = stats.assinaturas_ativas || 0;
    document.getElementById('stat-receita').textContent = formatarMoeda(stats.receita_mensal || 0);
}

function atualizarTopSistemas(sistemas) {
    const container = document.getElementById('top-sistemas');
    
    if (sistemas.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Nenhum sistema encontrado</p>';
        return;
    }
    
    container.innerHTML = sistemas.map((s, i) => `
        <div class="d-flex justify-content-between align-items-center ${i < sistemas.length - 1 ? 'mb-3 pb-3 border-bottom' : ''}">
            <div>
                <strong>${s.nome}</strong>
                <small class="text-muted d-block">${s.total_assinaturas || 0} assinaturas</small>
            </div>
            <span class="badge bg-primary">#${i + 1}</span>
        </div>
    `).join('');
}

let chartInstance = null;
function atualizarGrafico(stats) {
    const ctx = document.getElementById('assinaturasChart').getContext('2d');
    
    if (chartInstance) chartInstance.destroy();
    
    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Ativas', 'Suspensas', 'Canceladas', 'Expiradas'],
            datasets: [{
                data: [
                    stats.assinaturas_ativas || 0,
                    stats.assinaturas_suspensas || 0,
                    stats.assinaturas_canceladas || 0,
                    stats.assinaturas_expiradas || 0
                ],
                backgroundColor: ['#4caf50', '#ff9800', '#f44336', '#9e9e9e']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

async function carregarAssinaturasVencendo() {
    try {
        const response = await fetch(`${BASE_URL}/api/assinaturas?status=ativa&limit=5`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const data = await response.json();
        const container = document.getElementById('lista-vencendo');
        
        const assinaturas = (data.data || []).filter(a => {
            if (!a.data_fim) return false;
            const dias = Math.ceil((new Date(a.data_fim) - new Date()) / (1000 * 60 * 60 * 24));
            return dias <= 7 && dias >= 0;
        });
        
        if (assinaturas.length === 0) {
            container.innerHTML = '<p class="text-success text-center mb-0"><i class="fas fa-check-circle"></i> Nenhuma assinatura vencendo em breve</p>';
            return;
        }
        
        container.innerHTML = '<ul class="list-group list-group-flush">' + assinaturas.map(a => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${a.cliente_nome || 'Cliente'}</strong>
                    <small class="text-muted d-block">${a.sistema_nome || 'Sistema'}</small>
                </div>
                <span class="badge bg-warning">${formatarData(a.data_fim)}</span>
            </li>
        `).join('') + '</ul>';
    } catch (error) {
        console.error('Erro:', error);
    }
}

async function carregarUltimasAssinaturas() {
    try {
        const response = await fetch(`${BASE_URL}/api/assinaturas?limit=5`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const data = await response.json();
        const tbody = document.querySelector('#tabelaUltimasAssinaturas tbody');
        const assinaturas = data.data || [];
        
        if (assinaturas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhuma assinatura encontrada</td></tr>';
            return;
        }
        
        tbody.innerHTML = assinaturas.map(a => `
            <tr>
                <td>${a.cliente_nome || '-'}</td>
                <td>${a.sistema_nome || '-'}</td>
                <td><span class="badge ${getBadgeClass(a.status)}">${a.status}</span></td>
                <td>${formatarMoeda(a.preco_com_imposto || a.preco_sem_imposto || 0)}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Erro:', error);
    }
}

function getBadgeClass(status) {
    const classes = {
        'ativa': 'bg-success',
        'suspensa': 'bg-warning',
        'cancelada': 'bg-danger',
        'expirada': 'bg-secondary'
    };
    return classes[status] || 'bg-secondary';
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data).toLocaleDateString('pt-BR');
}
</script>

<?php $render('footer'); ?>
