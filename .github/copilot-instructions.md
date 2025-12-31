# GitHub Copilot - Instruções do Projeto OrganizaAI

## 📋 Visão Geral

**Sistema**: OrganizaAI - Plataforma multi-tenant de gestão de contratos e assinaturas  
**Domínio**: Empresas, Usuários, Clientes, Sistemas, Assinaturas, Auditoria  
**Banco**: MySQL com estrutura relacional multi-tenant  
**Autenticação**: Sessão + Token + 2FA (TOTP)

---

## 🏢 Multi-Tenancy

**REGRA FUNDAMENTAL**: Todas as tabelas possuem `idempresa` para isolamento de dados.

```sql
-- TODAS as queries devem filtrar por idempresa
SELECT * FROM clientes WHERE idempresa = :idempresa AND idcliente = :idcliente;
INSERT INTO clientes (idempresa, nome, ...) VALUES (:idempresa, :nome, ...);
UPDATE clientes SET nome = :nome WHERE idempresa = :idempresa AND idcliente = :idcliente;
DELETE FROM clientes WHERE idempresa = :idempresa AND idcliente = :idcliente;
```

---

## 🏗️ Entidades do Sistema

### 🏢 Módulo Empresa (Tenant)
- **empresa**: Tenant central (idempresa, nome, cnpj, ativo)
- **usuarios**: Usuários do sistema com 2FA

### 👥 Módulo Clientes
- **clientes**: PF/PJ com CPF/CNPJ (pertence a empresa)
- **clientes_enderecos**: Múltiplos endereços, um principal  
- **clientes_contatos**: Múltiplos contatos, um principal

### 💻 Módulo Sistemas
- **sistemas**: Catálogo de softwares (por empresa)
- **sistemas_planos**: Planos de cada sistema (preços base)
- **sistemas_addons**: Módulos opcionais (complementos)

### 📋 Módulo Assinaturas  
- **assinaturas**: Contratos dos clientes (com preços negociados)
- **assinaturas_addons**: Add-ons contratados por assinatura

### 📊 Módulo Auditoria
- **precos_historico**: Log de alterações de preços/alíquotas

### 📈 Views e Relatórios
- **v_assinaturas_resumo**: Valores atuais com impostos
- **v_assinaturas_total_mensal**: Total mensal incluindo add-ons

---

## 🔐 Autenticação e 2FA

### Fluxo de Login

```
1. POST /login { email, senha }
   ↓
2. Valida credenciais → Gera token
   ↓
3. Se 2FA habilitado:
   - Retorna { requer_2fa: true }
   - POST /login { email, senha, codigo_2fa }
   ↓
4. Valida TOTP → Salva token na sessão e banco
   ↓
5. Retorna { success: true, token, usuario }
```

### Validação Tripla de Token

```php
// Em TODA requisição autenticada:
// 1. Token da sessão PHP ($_SESSION['token'])
// 2. Token no banco (usuarios.token)
// 3. Token do header (Authorization: Bearer xxx)
// Os três devem coincidir!
```

### Tabela usuarios

```sql
CREATE TABLE usuarios (
    idusuario           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    idempresa           BIGINT UNSIGNED NOT NULL,
    nome                VARCHAR(160) NOT NULL,
    email               VARCHAR(160) NOT NULL UNIQUE,
    senha_hash          VARCHAR(255) NOT NULL,
    token               VARCHAR(64) NULL,
    tema                VARCHAR(20) DEFAULT 'dark',
    ativo               TINYINT(1) DEFAULT 1,
    totp_habilitado     TINYINT(1) DEFAULT 0,
    totp_secret         VARCHAR(100) NULL,
    ultimo_login        DATETIME NULL,
    criado_em           DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_usuarios_empresa 
        FOREIGN KEY (idempresa) REFERENCES empresa(idempresa)
);
```

### Biblioteca TOTP

Usar `spomky-labs/otphp` para geração e validação de códigos:

```php
use OTPHP\TOTP;

// Gerar secret
$totp = TOTP::create();
$secret = $totp->getSecret();

// Gerar URI para QR Code
$totp->setLabel($email);
$totp->setIssuer('OrganizaAI');
$uri = $totp->getProvisioningUri();

// Validar código
$valido = $totp->verify($codigo);
```

---

## 🏗️ Arquitetura do Backend (PHP)

### Estrutura de Pastas

```
├── core/                  # Classes fundamentais do framework
│   ├── Auth.php          # Autenticação com 2FA
│   ├── Controller.php    # Base para todos os controllers
│   ├── Database.php      # ⭐ Classe de acesso ao banco
│   ├── Model.php         # Base para todos os models
│   └── Router.php        # Sistema de rotas
├── src/
│   ├── controllers/      # Camada de controle HTTP
│   ├── handlers/         # Lógica de negócio
│   │   └── service/      # Serviços (TwoFactorAuthService, etc)
│   ├── models/           # Camada de acesso a dados
│   ├── views/            # Templates PHP/HTML
│   └── routes.php        # Definição de rotas
└── SQL/                   # Queries SQL parametrizadas
    ├── empresa/          # CRUD empresa
    ├── usuarios/         # CRUD usuarios + 2FA
    ├── clientes/         # CRUD clientes
    ├── clientes_enderecos/
    ├── clientes_contatos/
    ├── sistemas/         # CRUD sistemas
    ├── sistemas_planos/
    ├── sistemas_addons/
    ├── assinaturas/      # CRUD assinaturas
    ├── assinaturas_addons/
    ├── precos_historico/ # Auditoria
    └── relatorios/       # Views e relatórios
```

---

## ⚠️ REGRA FUNDAMENTAL: Database::switchParams()

### ❌ NUNCA FAÇA ISSO

```php
// ❌ ERRADO - NUNCA usar PDO direto
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
$stmt->execute([':id' => $id]);
```

### ✅ SEMPRE FAÇA ISSO

```php
// ✅ CORRETO - SEMPRE usar switchParams
$resultado = Database::switchParams(
    ['idempresa' => $idempresa, 'idcliente' => $id],
    'clientes/select_by_id',
    true,   // Executar
    false,  // Sem log
    false   // Sem transação (SELECT)
);

if ($resultado['error']) {
    throw new Exception($resultado['error']);
}

$cliente = $resultado['retorno'][0] ?? null;
```

### Parâmetros

| # | Parâmetro | Tipo | Descrição |
|---|-----------|------|-----------|
| 1 | `$params` | array | Parâmetros nomeados (SEM `:` na chave) |
| 2 | `$sqlnome` | string | Caminho do arquivo SQL (sem `.sql`) |
| 3 | `$exec` | bool | `true` = executa, `false` = retorna SQL |
| 4 | `$log` | bool | Salvar log de execução |
| 5 | `$transaction` | bool | Usar transação (rollback em erro) |

### Quando usar transação?

| Operação | Transação? |
|----------|-----------|
| SELECT | `false` |
| INSERT | `true` |
| UPDATE | `true` |
| DELETE | `true` |

---

## 📁 Padrão de Arquivos SQL

### Estrutura

```
SQL/clientes/
├── insert.sql
├── update.sql
├── delete.sql
├── select_all.sql
├── select_by_id.sql
├── select_by_cpf_cnpj.sql
├── search.sql
└── count_all.sql
```

### Exemplo de Query

**Arquivo**: `SQL/clientes/select_by_id.sql`

```sql
SELECT 
    c.idcliente,
    c.idempresa,
    c.tipo_pessoa,
    c.nome,
    c.cpf_cnpj,
    c.email,
    c.ativo
FROM clientes c
WHERE c.idempresa = :idempresa
  AND c.idcliente = :idcliente;
```

**Uso no Model**:

```php
public function buscarPorId($idempresa, $idcliente) {
    $resultado = Database::switchParams(
        ['idempresa' => $idempresa, 'idcliente' => $idcliente],
        'clientes/select_by_id',
        true, false, false
    );

    if ($resultado['error']) {
        throw new Exception($resultado['error']);
    }

    return $resultado['retorno'][0] ?? null;
}
```

---

## 🎯 Padrão MVC + Handler

### Fluxo

```
Request → Router → Controller → Handler → Model → SQL → Database
                        ↓
                   Response JSON
```

### Model (Acesso a Dados)

```php
class ClientesModel extends Model {
    public function listar($idempresa, $pagina = 1, $limite = 20) {
        return Database::switchParams(
            ['idempresa' => $idempresa, 'offset' => ($pagina - 1) * $limite, 'limit' => $limite],
            'clientes/select_all',
            true, false, false
        );
    }
}
```

### Handler (Lógica de Negócio)

```php
class ClientesHandler {
    private $model;

    public function __construct() {
        $this->model = new ClientesModel();
    }

    public function criar($idempresa, $dados) {
        // Validações
        if (empty($dados['nome'])) {
            throw new Exception('Nome é obrigatório');
        }

        // Verifica duplicidade CPF/CNPJ
        $existente = $this->model->buscarPorCpfCnpj($idempresa, $dados['cpf_cnpj']);
        if ($existente) {
            throw new Exception('CPF/CNPJ já cadastrado');
        }

        // Insere
        return $this->model->inserir($idempresa, $dados);
    }
}
```

### Controller (HTTP)

```php
class ClientesController extends Controller {
    private $handler;

    const CAMPOS_CRIAR = ['nome', 'tipo_pessoa', 'cpf_cnpj'];

    public function __construct() {
        parent::__construct();
        $this->handler = new ClientesHandler();
    }

    public function criar() {
        try {
            $dados = Controller::getBody();
            Controller::verificarCamposVazios($dados, self::CAMPOS_CRIAR);
            
            $idempresa = Controller::empresa();
            $resultado = $this->handler->criar($idempresa, $dados);
            
            Controller::response($resultado, 201);
        } catch (Exception $e) {
            Controller::rejectResponse($e);
        }
    }
}
```

---

## 🛣️ Rotas

### Padrão

```php
$router->metodo('/rota', 'Controller@metodo', autenticado);
```

### Exemplos

```php
// Públicas
$router->get('/', 'LoginController@index');
$router->post('/login', 'LoginController@verificarLogin');

// Privadas (requer autenticação)
$router->get('/dashboard', 'DashboardController@index', true);

// API REST
$router->get('/api/clientes', 'ClientesController@listar', true);
$router->get('/api/clientes/{id}', 'ClientesController@buscar', true);
$router->post('/api/clientes', 'ClientesController@criar', true);
$router->put('/api/clientes/{id}', 'ClientesController@atualizar', true);
$router->delete('/api/clientes/{id}', 'ClientesController@excluir', true);
```

---

## 🔧 Métodos do Controller

| Método | Descrição |
|--------|-----------|
| `Controller::getBody()` | Obtém JSON do body |
| `Controller::verificarCamposVazios($dados, $campos)` | Valida campos obrigatórios |
| `Controller::response($data, $status)` | Retorna JSON de sucesso |
| `Controller::rejectResponse($exception)` | Retorna JSON de erro (400) |
| `Controller::empresa()` | ID da empresa logada |
| `Controller::usuario()` | ID do usuário logado |
| `Controller::redirect($url)` | Redireciona |
| `Controller::render($view)` | Renderiza view PHP |

---

## 📊 Índices Compostos

Todas as tabelas com `idempresa` devem ter índices compostos para performance:

```sql
-- Padrão: (idempresa, campo_filtro)
CREATE INDEX ix_clientes_empresa_cpf ON clientes(idempresa, cpf_cnpj);
CREATE INDEX ix_clientes_empresa_ativo ON clientes(idempresa, ativo);
CREATE INDEX ix_assinaturas_empresa_status ON assinaturas(idempresa, status);
CREATE INDEX ix_assinaturas_empresa_cliente ON assinaturas(idempresa, idcliente);
```

---

## 🚫 Anti-Patterns

### ❌ NÃO usar PDO diretamente
### ❌ NÃO colocar SQL inline no código PHP
### ❌ NÃO esquecer filtro `idempresa` nas queries
### ❌ NÃO colocar lógica de negócio no Controller
### ❌ NÃO misturar acesso a dados no Handler

---

## ✅ Checklist de Implementação

### Nova Entidade
- [ ] Criar tabela no DDL com `idempresa` + FKs + índices
- [ ] Criar pasta `SQL/entidade/` com queries CRUD
- [ ] Criar Model em `src/models/`
- [ ] Criar Handler em `src/handlers/`
- [ ] Criar Controller em `src/controllers/`
- [ ] Adicionar rotas em `src/routes.php`
- [ ] Criar view em `src/views/pages/` (se necessário)

### Nova Query
- [ ] Criar arquivo `.sql` na pasta correta
- [ ] Incluir filtro `WHERE idempresa = :idempresa`
- [ ] Usar parâmetros nomeados (`:param`)
- [ ] Testar com `Database::switchParams()`

---

## 📝 Versão

**Documento**: v2.0.0  
**Data**: 31/12/2025  
**Mantido por**: GitHub Copilot & Equipe JZTech
