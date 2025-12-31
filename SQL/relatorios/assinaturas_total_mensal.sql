-- Relatório: Total mensal de assinaturas por cliente
-- Parâmetros: :idempresa, :data_inicio (opcional), :data_fim (opcional)
SELECT 
    a.idcliente,
    c.nome AS cliente_nome,
    c.cpf_cnpj AS cliente_documento,
    MONTH(a.data_inicio) AS mes,
    YEAR(a.data_inicio) AS ano,
    COUNT(DISTINCT a.idassinatura) AS total_assinaturas,
    ROUND(SUM(a.preco_negociado * (1 + a.aliquota_imposto/100)), 2) AS receita_base,
    COALESCE(SUM(addons.custo_addons), 0) AS receita_addons,
    ROUND(SUM(a.preco_negociado * (1 + a.aliquota_imposto/100)), 2) + 
    COALESCE(SUM(addons.custo_addons), 0) AS receita_total
FROM assinaturas a
INNER JOIN clientes c 
    ON c.idempresa = a.idempresa 
   AND c.idcliente = a.idcliente
LEFT JOIN (
    SELECT 
        aa.idempresa,
        aa.idassinatura,
        ROUND(SUM(aa.preco_negociado * aa.quantidade), 2) AS custo_addons
    FROM assinaturas_addons aa
    WHERE aa.ativo = 1
    GROUP BY aa.idempresa, aa.idassinatura
) addons ON addons.idempresa = a.idempresa AND addons.idassinatura = a.idassinatura
WHERE a.idempresa = :idempresa
  AND a.status = 'ativa'
  AND (:data_inicio IS NULL OR a.data_inicio >= :data_inicio)
  AND (:data_fim IS NULL OR a.data_inicio <= :data_fim)
GROUP BY a.idcliente, c.nome, c.cpf_cnpj, MONTH(a.data_inicio), YEAR(a.data_inicio)
ORDER BY ano DESC, mes DESC, cliente_nome ASC;