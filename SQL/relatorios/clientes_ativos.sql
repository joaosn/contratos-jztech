-- Relatório: Clientes ativos com resumo de assinaturas
-- Parâmetros: :idempresa
SELECT 
    c.idcliente,
    c.idempresa,
    c.nome,
    c.cpf_cnpj,
    c.tipo_pessoa,
    c.email,
    c.ativo,
    COUNT(DISTINCT a.idassinatura) AS total_assinaturas,
    COUNT(DISTINCT CASE WHEN a.status = 'ativa' THEN a.idassinatura END) AS assinaturas_ativas,
    ROUND(SUM(CASE WHEN a.status = 'ativa' 
        THEN a.preco_negociado * (1 + a.aliquota_imposto/100) ELSE 0 END), 2) AS gasto_mensal,
    c.criado_em AS data_cadastro,
    MAX(a.data_inicio) AS ultima_assinatura
FROM clientes c
LEFT JOIN assinaturas a 
    ON a.idempresa = c.idempresa 
   AND a.idcliente = c.idcliente
WHERE c.idempresa = :idempresa
  AND c.ativo = 1
GROUP BY c.idcliente, c.idempresa, c.nome, c.cpf_cnpj, c.tipo_pessoa, c.email, c.ativo, c.criado_em
ORDER BY gasto_mensal DESC;