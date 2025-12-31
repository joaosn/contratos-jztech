# 🎯 OrganizaAI - Sistema de Gestão de Contratos e Assinaturas

Sistema multi-tenant para gerenciamento de contratos, assinaturas de software e clientes.

## 📋 Visão Geral

O **OrganizaAI** é uma plataforma completa para gestão de:
- **Empresas** (multi-tenant)
- **Clientes** (PF/PJ com múltiplos endereços e contatos)
- **Sistemas/Softwares** (catálogo com planos e add-ons)
- **Assinaturas** (contratos com preços negociados, ciclos de cobrança)
- **Auditoria de Preços** (histórico de alterações)

## 🏗️ Arquitetura

```
├── core/                  # Framework base (MVC)
│   ├── Auth.php          # Autenticação com 2FA
│   ├── Controller.php    # Base controller
│   ├── Database.php      # Conexão e switchParams()
│   ├── Model.php         # Base model
│   └── Router.php        # Sistema de rotas
├── src/
│   ├── controllers/      # Controllers HTTP
│   ├── handlers/         # Lógica de negócio
│   ├── models/           # Acesso a dados
│   └── views/            # Templates PHP/HTML
├── SQL/                   # Queries parametrizadas
│   ├── empresa/          # CRUD empresa
│   ├── usuarios/         # CRUD usuarios + 2FA
│   ├── clientes/         # CRUD clientes
│   ├── sistemas/         # CRUD sistemas
│   ├── assinaturas/      # CRUD assinaturas
│   └── relatorios/       # Views e relatórios
└── docs/                  # Documentação
```

## 🚀 Início Rápido

### 1. Requisitos
- PHP 8.1+
- MySQL 8.0+
- Composer
- Docker (opcional)

### 2. Instalação

```bash
# Clonar repositório
git clone <repo-url>
cd contratos-jztech

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
# Editar .env com credenciais do banco

# Criar banco de dados
mysql -u root -p < SQL/DDL.SQL
```

### 3. Docker (Alternativa)

```bash
# Construir imagem
docker build -t api_mvc:latest .

# Subir containers
docker-compose up -d

# Instalar dependências
docker-compose run --rm composer composer install
```

### 4. Acessar

- **Aplicação**: http://localhost:8003
- **Login**: Criar usuário no banco

## 🔐 Autenticação

O sistema utiliza autenticação baseada em sessão com suporte a **2FA (Two-Factor Authentication)**:

1. Login com email/senha
2. Se 2FA habilitado: código TOTP (Google Authenticator, Authy, etc.)
3. Token armazenado em sessão e banco (validação tripla)

## 📊 Banco de Dados

### Entidades Principais

| Tabela | Descrição |
|--------|-----------|
| `empresa` | Tenant central (multi-tenancy) |
| `usuarios` | Usuários do sistema com 2FA |
| `clientes` | Clientes PF/PJ |
| `clientes_enderecos` | Endereços dos clientes |
| `clientes_contatos` | Contatos dos clientes |
| `sistemas` | Catálogo de softwares |
| `sistemas_planos` | Planos de cada sistema |
| `sistemas_addons` | Módulos opcionais |
| `assinaturas` | Contratos de assinatura |
| `assinaturas_addons` | Add-ons contratados |
| `precos_historico` | Auditoria de preços |

### Multi-Tenancy

Todas as tabelas possuem `idempresa` para isolamento de dados:
- Índices compostos `(idempresa, campo)` para queries rápidas
- FKs para integridade referencial
- Filtro automático por empresa logada

## 📖 Documentação

- [Quick Start](docs/QUICK_START.md)
- [Plano de Ação API](docs/api/PLANO_ACAO_API.md)
- [Plano de Ação Frontend](docs/frontend/PLANO_ACAO_FRONTEND.md)
- [Checklist de Implementação](docs/CHECKLIST_IMPLEMENTACAO.md)

## 🛠️ Desenvolvimento

### Padrão MVC + Handler

```
Request → Router → Controller → Handler → Model → SQL File → Database
                                    ↓
                              Response JSON
```

### Regra Fundamental: Database::switchParams()

```php
// ✅ CORRETO - Sempre usar switchParams
$resultado = Database::switchParams(
    ['idempresa' => $idempresa, 'idcliente' => $id],
    'clientes/select_by_id',
    true
);

// ❌ ERRADO - Nunca usar PDO direto
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
```

## 📝 Licença

Proprietário - JZTech © 2025
