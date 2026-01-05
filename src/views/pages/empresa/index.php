<?php $render('header'); ?>

<h2 class="mb-4">
    <i class="fas fa-building"></i> Configurações da Empresa
</h2>

<!-- Dados da Empresa -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Informações Gerais
                </h5>
                <button class="btn btn-primary btn-sm" onclick="editarEmpresa()">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
            <div class="card-body">
                <div id="loading-empresa" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                
                <div id="dados-empresa" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nome da Empresa</label>
                            <p class="h5" id="empresa-nome">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">CNPJ</label>
                            <p class="h5" id="empresa-cnpj">-</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p id="empresa-email">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Telefone</label>
                            <p id="empresa-telefone">-</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Endereço</label>
                            <p id="empresa-endereco">-</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Status</label>
                            <p><span id="empresa-status" class="badge">-</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Criada em</label>
                            <p id="empresa-criado">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie"></i> Estatísticas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Usuários</span>
                    <span class="badge bg-primary" id="stats-usuarios">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Clientes</span>
                    <span class="badge bg-success" id="stats-clientes">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Sistemas</span>
                    <span class="badge bg-info" id="stats-sistemas">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Assinaturas Ativas</span>
                    <span class="badge bg-warning" id="stats-assinaturas">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modalEmpresa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Empresa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEmpresa">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome da Empresa *</label>
                            <input type="text" class="form-control" id="form-nome" name="nome" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">CNPJ *</label>
                            <input type="text" class="form-control" id="form-cnpj" name="cnpj" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="form-email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="form-telefone" name="telefone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" class="form-control" id="form-endereco" name="endereco">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo $base; ?>';
let empresaData = null;

document.addEventListener('DOMContentLoaded', function() {
    carregarEmpresa();
});

async function carregarEmpresa() {
    try {
        const response = await fetch(`${BASE_URL}/api/empresa/atual`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const data = await response.json();
        
        if (data.success) {
            empresaData = data.data;
            exibirDadosEmpresa(empresaData);
        }
        
        document.getElementById('loading-empresa').style.display = 'none';
        document.getElementById('dados-empresa').style.display = 'block';
    } catch (error) {
        console.error('Erro ao carregar empresa:', error);
    }
}

function exibirDadosEmpresa(empresa) {
    document.getElementById('empresa-nome').textContent = empresa.nome || '-';
    document.getElementById('empresa-cnpj').textContent = formatarCNPJ(empresa.cnpj) || '-';
    document.getElementById('empresa-email').textContent = empresa.email || '-';
    document.getElementById('empresa-telefone').textContent = empresa.telefone || '-';
    document.getElementById('empresa-endereco').textContent = empresa.endereco || '-';
    document.getElementById('empresa-criado').textContent = formatarData(empresa.criado_em);
    
    const statusEl = document.getElementById('empresa-status');
    statusEl.textContent = empresa.ativo ? 'Ativa' : 'Inativa';
    statusEl.className = `badge ${empresa.ativo ? 'bg-success' : 'bg-danger'}`;
}

function editarEmpresa() {
    if (!empresaData) return;
    
    document.getElementById('form-nome').value = empresaData.nome || '';
    document.getElementById('form-cnpj').value = empresaData.cnpj || '';
    document.getElementById('form-email').value = empresaData.email || '';
    document.getElementById('form-telefone').value = empresaData.telefone || '';
    document.getElementById('form-endereco').value = empresaData.endereco || '';
    
    new bootstrap.Modal(document.getElementById('modalEmpresa')).show();
}

document.getElementById('formEmpresa').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const dados = {
        nome: document.getElementById('form-nome').value,
        cnpj: document.getElementById('form-cnpj').value,
        email: document.getElementById('form-email').value,
        telefone: document.getElementById('form-telefone').value,
        endereco: document.getElementById('form-endereco').value
    };
    
    try {
        const response = await fetch(`${BASE_URL}/api/empresas/${empresaData.idempresa}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEmpresa')).hide();
            carregarEmpresa();
            alert('Empresa atualizada com sucesso!');
        } else {
            alert(result.error || 'Erro ao atualizar empresa');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao atualizar empresa');
    }
});

function formatarCNPJ(cnpj) {
    if (!cnpj) return '';
    cnpj = cnpj.replace(/\D/g, '');
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data).toLocaleDateString('pt-BR');
}
</script>

<?php $render('footer'); ?>
