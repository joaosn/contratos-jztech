-- ============================================
-- ÍNDICES ADICIONAIS - ORGANIZAAI API
-- Nota: Os índices principais já estão no DDL.SQL
-- Estes são índices extras para otimização avançada
-- ============================================

-- ============================================
-- ÍNDICES COVERING (SELECT sem acesso ao table)
-- Melhoram performance em queries específicas
-- ============================================

-- Listagem de clientes (nome, documento, status)
CREATE INDEX ix_clientes_covering_lista 
ON clientes(idempresa, ativo, nome, cpf_cnpj);

-- Listagem de sistemas com planos
CREATE INDEX ix_sistemas_covering_lista 
ON sistemas(idempresa, ativo, nome);

-- Listagem de planos com preços
CREATE INDEX ix_planos_covering_lista 
ON sistemas_planos(idempresa, idsistema, ativo, nome, preco);

-- Listagem de add-ons com preços
CREATE INDEX ix_addons_covering_lista 
ON sistemas_addons(idempresa, idsistema, ativo, nome, preco);

-- Listagem de assinaturas ativas por cliente
CREATE INDEX ix_assinaturas_covering_cliente 
ON assinaturas(idempresa, idcliente, status, data_vencimento, preco_negociado);

-- ============================================
-- ÍNDICES PARA RELATÓRIOS FREQUENTES
-- ============================================

-- Dashboard: contagem por status
CREATE INDEX ix_assinaturas_dashboard 
ON assinaturas(idempresa, status, criado_em);

-- Relatório de receita mensal
CREATE INDEX ix_assinaturas_receita 
ON assinaturas(idempresa, status, data_inicio);

-- Histórico de preços: auditoria por período
CREATE INDEX ix_historico_auditoria 
ON precos_historico(idempresa, tipo_referencia, criado_em);

-- ============================================
-- COMANDOS DE MANUTENÇÃO (executar periodicamente)
-- ============================================

-- Atualizar estatísticas após grandes volumes de dados:
-- ANALYZE TABLE empresa;
-- ANALYZE TABLE usuarios;
-- ANALYZE TABLE clientes;
-- ANALYZE TABLE sistemas;
-- ANALYZE TABLE sistemas_planos;
-- ANALYZE TABLE sistemas_addons;
-- ANALYZE TABLE assinaturas;
-- ANALYZE TABLE assinaturas_addons;
-- ANALYZE TABLE precos_historico;

-- Verificar índices:
-- SHOW INDEXES FROM clientes;
-- SHOW INDEXES FROM assinaturas;

-- Verificar uso de índices em query:
-- EXPLAIN SELECT * FROM clientes WHERE idempresa = 1 AND cpf_cnpj = '12345678901';

-- Otimizar tabelas após muitos DELETEs:
-- OPTIMIZE TABLE clientes;
-- OPTIMIZE TABLE assinaturas;
-- OPTIMIZE TABLE precos_historico;
