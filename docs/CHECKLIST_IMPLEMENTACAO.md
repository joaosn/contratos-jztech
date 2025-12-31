# 📋 Checklist de Implementação - OrganizaAI

**Sistema**: OrganizaAI - Gestão de Contratos e Assinaturas  
**Data Início**: 31/12/2025  
**Arquitetura**: PHP MVC com Views (HTML/PHP)

---

## Legenda
- ✅ Concluído
- 🔄 Em andamento
- ⏳ Pendente

---

## Fase 1: Organização ✅

- [x] Limpar arquivos MD desnecessários
- [x] Atualizar README.md
- [x] Atualizar copilot-instructions.md
- [x] Criar estrutura docs/

---

## Fase 2: DDL Multi-Tenancy ✅

### Novas Tabelas
- [x] CREATE TABLE empresa (tenant central)
- [x] CREATE TABLE usuarios (com 2FA)

### Adicionar idempresa + índices
- [x] clientes
- [x] sistemas
- [x] sistemas_planos
- [x] sistemas_addons
- [x] assinaturas
- [x] assinaturas_addons
- [x] precos_historico

### Atualizar Views
- [x] v_assinaturas_resumo
- [x] v_assinaturas_total_mensal

### Dados iniciais
- [x] Empresa JZTech + Usuário admin

---

## Fase 3: SQLs ✅

### Novas pastas
- [x] SQL/empresa/ (7 arquivos)
- [x] SQL/usuarios/ (12 arquivos)

### Ajustar SQLs existentes (adicionar idempresa)
- [x] SQL/clientes/ (9 arquivos)
- [x] SQL/clientes_enderecos/ (8 arquivos)
- [x] SQL/clientes_contatos/ (8 arquivos)
- [x] SQL/sistemas/ (9 arquivos)
- [x] SQL/sistemas_planos/ (6 arquivos)
- [x] SQL/sistemas_addons/ (6 arquivos)
- [x] SQL/assinaturas/ (9 arquivos)
- [x] SQL/assinaturas_addons/ (6 arquivos)
- [x] SQL/assinaturas_avancado/ (9 arquivos)
- [x] SQL/relatorios/ (6 arquivos)
- [x] SQL/precos_historico/ (6 arquivos)
- [x] SQL/indexes/ (1 arquivo)
- [x] SQL/optimization/ (2 arquivos)

---

## Fase 4: Autenticação ⏳

- [ ] Adicionar spomky-labs/otphp no composer
- [ ] Renomear Users.php → Usuarios.php (Model)
- [ ] Renomear User.php → Usuarios.php (Handler)
- [ ] Criar TwoFactorAuthService.php
- [ ] Atualizar Auth.php (validação tripla token)
- [ ] Criar Model Empresa.php
- [ ] Criar Handler EmpresaHandler.php

---

## Fase 5: Controllers e Rotas ⏳

### Novos Controllers
- [ ] EmpresaController.php
- [ ] UsuariosController.php

### Expandir Controllers (CRUD completo)
- [ ] ClientesController
- [ ] SistemasController
- [ ] AssinaturasController

### Rotas API em routes.php
- [ ] /api/empresa/* (~6 rotas)
- [ ] /api/usuarios/* (~8 rotas)
- [ ] /api/clientes/* (~14 rotas)
- [ ] /api/sistemas/* (~17 rotas)
- [ ] /api/assinaturas/* (~14 rotas)
- [ ] /api/relatorios/* (~5 rotas)

---

## Fase 6: Views (PHP/HTML) ⏳

### Revisar existentes
- [ ] login/index.php
- [ ] dashboard/index.php
- [ ] clientes/index.php
- [ ] sistemas/index.php
- [ ] assinaturas/index.php

### Criar novas
- [ ] empresa/index.php (config tenant)
- [ ] usuarios/index.php
- [ ] Modais/formulários CRUD

---

## Fase 7: Testes ⏳

- [ ] Login com 2FA
- [ ] CRUD empresa
- [ ] CRUD usuarios
- [ ] CRUD clientes
- [ ] CRUD sistemas
- [ ] CRUD assinaturas
- [ ] Validar multi-tenant

---

## 📊 Progresso

| Fase | Status |
|------|--------|
| 1. Organização | ✅ 100% |
| 2. DDL | ✅ 100% |
| 3. SQLs | ✅ 100% |
| 4. Autenticação | ⏳ 0% |
| 5. Controllers/Rotas | ⏳ 0% |
| 6. Views | ⏳ 0% |
| 7. Testes | ⏳ 0% |

---

**Última atualização**: 31/12/2025
