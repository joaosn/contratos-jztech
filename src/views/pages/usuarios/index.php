<?php $render('header'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-users-cog"></i> Gestão de Usuários
    </h2>
    <button class="btn btn-primary" onclick="novoUsuario()">
        <i class="fas fa-plus"></i> Novo Usuário
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" id="filtro-busca" placeholder="Buscar por nome ou email...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filtro-status">
                    <option value="">Todos os status</option>
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" onclick="carregarUsuarios()">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Usuários -->
<div class="card">
    <div class="card-body">
        <div id="loading-usuarios" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <div class="table-responsive" id="tabela-usuarios" style="display: none;">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>2FA</th>
                        <th>Status</th>
                        <th>Último Login</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbody-usuarios">
                </tbody>
            </table>
        </div>

        <div id="sem-usuarios" class="text-center py-4" style="display: none;">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">Nenhum usuário encontrado</p>
        </div>
    </div>
</div>

<!-- Modal Criar/Editar Usuário -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioTitulo">
                    <i class="fas fa-user-plus"></i> Novo Usuário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario">
                <input type="hidden" id="form-id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="form-nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="form-email" name="email" required>
                    </div>
                    <div class="mb-3" id="campo-senha">
                        <label class="form-label">Senha *</label>
                        <input type="password" class="form-control" id="form-senha" name="senha" minlength="6">
                        <small class="text-muted">Mínimo de 6 caracteres</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="form-ativo" name="ativo">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
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

<!-- Modal Alterar Senha -->
<div class="modal fade" id="modalSenha" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i> Alterar Senha
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSenha">
                <input type="hidden" id="senha-id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nova Senha *</label>
                        <input type="password" class="form-control" id="nova-senha" name="nova_senha" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar Senha *</label>
                        <input type="password" class="form-control" id="confirmar-senha" name="confirmar_senha" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2FA -->
<div class="modal fade" id="modal2FA" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt"></i> Autenticação de Dois Fatores
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="2fa-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Gerando QR Code...</p>
                </div>
                <div id="2fa-qrcode" style="display: none;">
                    <p class="mb-3">Escaneie o QR Code com seu aplicativo autenticador:</p>
                    <img id="2fa-qrcode-img" src="" alt="QR Code" class="mb-3" style="max-width: 200px;">
                    <p class="small text-muted mb-3">Ou insira manualmente o código:</p>
                    <code id="2fa-secret" class="d-block mb-3 p-2 bg-light"></code>
                    <div class="mb-3">
                        <label class="form-label">Código de Confirmação</label>
                        <input type="text" class="form-control text-center" id="2fa-codigo" maxlength="6" pattern="[0-9]{6}">
                    </div>
                    <button class="btn btn-success" onclick="confirmar2FA()">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo $base; ?>';
let usuarios = [];

document.addEventListener('DOMContentLoaded', function() {
    carregarUsuarios();
});

async function carregarUsuarios() {
    try {
        document.getElementById('loading-usuarios').style.display = 'block';
        document.getElementById('tabela-usuarios').style.display = 'none';
        document.getElementById('sem-usuarios').style.display = 'none';

        const busca = document.getElementById('filtro-busca').value;
        const status = document.getElementById('filtro-status').value;
        
        let url = `${BASE_URL}/api/usuarios`;
        const params = new URLSearchParams();
        if (busca) params.append('busca', busca);
        if (status !== '') params.append('ativo', status);
        if (params.toString()) url += '?' + params.toString();

        const response = await fetch(url, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const data = await response.json();
        usuarios = data.data || [];
        
        document.getElementById('loading-usuarios').style.display = 'none';
        
        if (usuarios.length === 0) {
            document.getElementById('sem-usuarios').style.display = 'block';
        } else {
            renderizarTabela();
            document.getElementById('tabela-usuarios').style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
    }
}

function renderizarTabela() {
    const tbody = document.getElementById('tbody-usuarios');
    tbody.innerHTML = usuarios.map(u => `
        <tr>
            <td>
                <strong>${u.nome}</strong>
            </td>
            <td>${u.email}</td>
            <td>
                ${u.totp_habilitado 
                    ? '<span class="badge bg-success"><i class="fas fa-shield-alt"></i> Ativo</span>' 
                    : '<span class="badge bg-secondary">Desativado</span>'}
            </td>
            <td>
                <span class="badge ${u.ativo ? 'bg-success' : 'bg-danger'}">
                    ${u.ativo ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td>${formatarData(u.ultimo_login)}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editarUsuario(${u.idusuario})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="alterarSenha(${u.idusuario})" title="Alterar Senha">
                        <i class="fas fa-key"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="gerenciar2FA(${u.idusuario}, ${u.totp_habilitado})" title="2FA">
                        <i class="fas fa-shield-alt"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="excluirUsuario(${u.idusuario})" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function novoUsuario() {
    document.getElementById('form-id').value = '';
    document.getElementById('form-nome').value = '';
    document.getElementById('form-email').value = '';
    document.getElementById('form-senha').value = '';
    document.getElementById('form-ativo').value = '1';
    document.getElementById('campo-senha').style.display = 'block';
    document.getElementById('form-senha').required = true;
    document.getElementById('modalUsuarioTitulo').innerHTML = '<i class="fas fa-user-plus"></i> Novo Usuário';
    
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function editarUsuario(id) {
    const usuario = usuarios.find(u => u.idusuario === id);
    if (!usuario) return;
    
    document.getElementById('form-id').value = usuario.idusuario;
    document.getElementById('form-nome').value = usuario.nome;
    document.getElementById('form-email').value = usuario.email;
    document.getElementById('form-senha').value = '';
    document.getElementById('form-ativo').value = usuario.ativo ? '1' : '0';
    document.getElementById('campo-senha').style.display = 'none';
    document.getElementById('form-senha').required = false;
    document.getElementById('modalUsuarioTitulo').innerHTML = '<i class="fas fa-user-edit"></i> Editar Usuário';
    
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

document.getElementById('formUsuario').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('form-id').value;
    const dados = {
        nome: document.getElementById('form-nome').value,
        email: document.getElementById('form-email').value,
        ativo: document.getElementById('form-ativo').value
    };
    
    if (!id) {
        dados.senha = document.getElementById('form-senha').value;
    }
    
    const url = id 
        ? `${BASE_URL}/api/usuarios/${id}` 
        : `${BASE_URL}/api/usuarios`;
    
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            carregarUsuarios();
            alert(id ? 'Usuário atualizado!' : 'Usuário criado!');
        } else {
            alert(result.error || 'Erro ao salvar usuário');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar usuário');
    }
});

function alterarSenha(id) {
    document.getElementById('senha-id').value = id;
    document.getElementById('nova-senha').value = '';
    document.getElementById('confirmar-senha').value = '';
    
    new bootstrap.Modal(document.getElementById('modalSenha')).show();
}

document.getElementById('formSenha').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('senha-id').value;
    const novaSenha = document.getElementById('nova-senha').value;
    const confirmar = document.getElementById('confirmar-senha').value;
    
    if (novaSenha !== confirmar) {
        alert('As senhas não coincidem!');
        return;
    }
    
    try {
        const response = await fetch(`${BASE_URL}/api/usuarios/${id}/senha`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ nova_senha: novaSenha })
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalSenha')).hide();
            alert('Senha alterada com sucesso!');
        } else {
            alert(result.error || 'Erro ao alterar senha');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao alterar senha');
    }
});

let usuario2FA = null;

async function gerenciar2FA(id, habilitado) {
    usuario2FA = id;
    
    if (habilitado) {
        if (confirm('Deseja desativar a autenticação de dois fatores para este usuário?')) {
            desativar2FA(id);
        }
    } else {
        document.getElementById('2fa-loading').style.display = 'block';
        document.getElementById('2fa-qrcode').style.display = 'none';
        new bootstrap.Modal(document.getElementById('modal2FA')).show();
        
        try {
            const response = await fetch(`${BASE_URL}/api/usuarios/${id}/2fa/habilitar`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('2fa-qrcode-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(result.uri)}`;
                document.getElementById('2fa-secret').textContent = result.secret;
                document.getElementById('2fa-loading').style.display = 'none';
                document.getElementById('2fa-qrcode').style.display = 'block';
            } else {
                alert(result.error || 'Erro ao gerar 2FA');
                bootstrap.Modal.getInstance(document.getElementById('modal2FA')).hide();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao gerar 2FA');
            bootstrap.Modal.getInstance(document.getElementById('modal2FA')).hide();
        }
    }
}

async function confirmar2FA() {
    const codigo = document.getElementById('2fa-codigo').value;
    
    if (!codigo || codigo.length !== 6) {
        alert('Insira um código de 6 dígitos');
        return;
    }
    
    try {
        const response = await fetch(`${BASE_URL}/api/usuarios/${usuario2FA}/2fa/confirmar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ codigo: codigo })
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modal2FA')).hide();
            carregarUsuarios();
            alert('2FA habilitado com sucesso!');
        } else {
            alert(result.error || 'Código inválido');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao confirmar 2FA');
    }
}

async function desativar2FA(id) {
    try {
        const response = await fetch(`${BASE_URL}/api/usuarios/${id}/2fa/desabilitar`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            carregarUsuarios();
            alert('2FA desativado com sucesso!');
        } else {
            alert(result.error || 'Erro ao desativar 2FA');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao desativar 2FA');
    }
}

async function excluirUsuario(id) {
    if (!confirm('Tem certeza que deseja excluir este usuário?')) return;
    
    try {
        const response = await fetch(`${BASE_URL}/api/usuarios/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            carregarUsuarios();
            alert('Usuário excluído com sucesso!');
        } else {
            alert(result.error || 'Erro ao excluir usuário');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir usuário');
    }
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data).toLocaleString('pt-BR');
}
</script>

<?php $render('footer'); ?>
