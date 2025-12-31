-- Relatório: Receita por período (mensal)
-- Parâmetros: :idempresa
SELECT 
    MONTH(a.data_inicio) AS mes,
    YEAR(a.data_inicio) AS ano,
    COUNT(DISTINCT a.idassinatura) AS total_assinaturas,
    COUNT(DISTINCT CASE WHEN a.status = 'ativa' THEN a.idassinatura END) AS ativas,
    COUNT(DISTINCT CASE WHEN a.status = 'suspensa' THEN a.idassinatura END) AS suspensas,
    COUNT(DISTINCT CASE WHEN a.status = 'cancelada' THEN a.idassinatura END) AS canceladas,
    ROUND(SUM(CASE WHEN a.status = 'ativa' 
        THEN a.preco_negociado * (1 + a.aliquota_imposto/100) ELSE 0 END), 2) AS receita_base,
    COALESCE(SUM(CASE WHEN a.status = 'ativa' THEN addons.custo_addons ELSE 0 END), 0) AS receita_addons,
    ROUND(SUM(CASE WHEN a.status = 'ativa' 
        THEN a.preco_negociado * (1 + a.aliquota_imposto/100) ELSE 0 END), 2) +
    COALESCE(SUM(CASE WHEN a.status = 'ativa' THEN addons.custo_addons ELSE 0 END), 0) AS receita_total
FROM assinaturas a
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
  AND a.data_inicio IS NOT NULL
GROUP BY MONTH(a.data_inicio), YEAR(a.data_inicio)
ORDER BY ano DESC, mes DESC;