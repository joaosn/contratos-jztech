<?php
use core\Router;
$router = new Router();

// ==========================================
// OrganizaAI-API - Rotas
// ==========================================

// ==========================================
// AUTENTICAÇÃO (Views - GET)
// ==========================================
$router->get('/', 'LoginController@index');
$router->get('/login', 'LoginController@index');

// ==========================================
// AUTENTICAÇÃO (API - POST)
// ==========================================
$router->get('/sair', 'LoginController@logout', true);
$router->post('/login', 'LoginController@verificarLogin');
$router->get('/validaToken', 'LoginController@validaToken', true);

// ==========================================
// DASHBOARD (Views - GET)
// ==========================================
$router->get('/dashboard', 'DashboardController@index', true);

// ==========================================
// DASHBOARD (API - GET)
// ==========================================
$router->get('/api/dashboard/stats', 'DashboardController@obterEstatisticas', true);

// ==========================================
// EMPRESA (Views - GET)
// ==========================================
$router->get('/empresa', 'EmpresaController@index', true);

// ==========================================
// EMPRESA (API)
// ==========================================
$router->get('/api/empresa/atual', 'EmpresaController@atual', true);
$router->get('/api/empresas', 'EmpresaController@listar', true);
$router->get('/api/empresas/count', 'EmpresaController@contar', true);
$router->get('/api/empresas/{id}', 'EmpresaController@buscar', true);
$router->post('/api/empresas', 'EmpresaController@criar', true);
$router->put('/api/empresas/{id}', 'EmpresaController@atualizar', true);
$router->delete('/api/empresas/{id}', 'EmpresaController@excluir', true);

// ==========================================
// USUÁRIOS (Views - GET)
// ==========================================
$router->get('/usuarios', 'UsuariosController@index', true);

// ==========================================
// USUÁRIOS (API)
// ==========================================
$router->get('/api/usuarios', 'UsuariosController@listar', true);
$router->get('/api/usuarios/{id}', 'UsuariosController@buscar', true);
$router->post('/api/usuarios', 'UsuariosController@criar', true);
$router->put('/api/usuarios/{id}', 'UsuariosController@atualizar', true);
$router->delete('/api/usuarios/{id}', 'UsuariosController@excluir', true);
$router->put('/api/usuarios/{id}/senha', 'UsuariosController@alterarSenha', true);
$router->put('/api/usuarios/{id}/tema', 'UsuariosController@atualizarTema', true);
$router->post('/api/usuarios/{id}/2fa/habilitar', 'UsuariosController@habilitar2FA', true);
$router->post('/api/usuarios/{id}/2fa/confirmar', 'UsuariosController@confirmar2FA', true);
$router->post('/api/usuarios/{id}/2fa/desabilitar', 'UsuariosController@desabilitar2FA', true);

// ==========================================
// CLIENTES (Views - GET)
// ==========================================
$router->get('/clientes', 'ClientesController@index', true);

// ==========================================
// CLIENTES (API)
// ==========================================
$router->get('/api/clientes', 'ClientesController@listar', true);
$router->get('/api/clientes/{id}', 'ClientesController@buscar', true);
$router->post('/api/clientes', 'ClientesController@criar', true);
$router->put('/api/clientes/{id}', 'ClientesController@atualizar', true);
$router->delete('/api/clientes/{id}', 'ClientesController@excluir', true);
$router->get('/api/clientes/{id}/enderecos', 'ClientesController@listarEnderecos', true);
$router->get('/api/clientes/{id}/contatos', 'ClientesController@listarContatos', true);

// ==========================================
// SISTEMAS (Views - GET)
// ==========================================
$router->get('/sistemas', 'SistemasController@index', true);

// ==========================================
// SISTEMAS (API)
// ==========================================
$router->get('/api/sistemas', 'SistemasController@listar', true);
$router->get('/api/sistemas/{id}', 'SistemasController@buscar', true);
$router->post('/api/sistemas', 'SistemasController@criar', true);
$router->put('/api/sistemas/{id}', 'SistemasController@atualizar', true);
$router->delete('/api/sistemas/{id}', 'SistemasController@excluir', true);
$router->get('/api/sistemas/{id}/planos', 'SistemasController@listarPlanos', true);
$router->get('/api/sistemas/{id}/addons', 'SistemasController@listarAddons', true);

// ==========================================
// ASSINATURAS (Views - GET)
// ==========================================
$router->get('/assinaturas', 'AssinaturasController@index', true);

// ==========================================
// ASSINATURAS (API)
// ==========================================
$router->get('/api/assinaturas', 'AssinaturasController@listar', true);
$router->get('/api/assinaturas/{id}', 'AssinaturasController@buscar', true);
$router->post('/api/assinaturas', 'AssinaturasController@criar', true);
$router->put('/api/assinaturas/{id}', 'AssinaturasController@atualizar', true);
$router->delete('/api/assinaturas/{id}', 'AssinaturasController@excluir', true);
$router->put('/api/assinaturas/{id}/status', 'AssinaturasController@atualizarStatus', true);
$router->get('/api/assinaturas/{id}/addons', 'AssinaturasController@listarAddons', true);

// ==========================================
// RELATÓRIOS (API)
// ==========================================
$router->get('/api/relatorios/clientes-ativos', 'DashboardController@clientesAtivos', true);
$router->get('/api/relatorios/sistemas-vendidos', 'DashboardController@sistemasVendidos', true);
$router->get('/api/relatorios/receita-periodo', 'DashboardController@receitaPeriodo', true);

// ==========================================
// FIM DAS ROTAS
// ==========================================
