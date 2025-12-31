-- Relatório: Sistemas vendidos com métricas
-- Parâmetros: :idempresa
SELECT 
    s.idsistema,
    s.idempresa,
    s.nome AS sistema_nome,
    s.descricao,
    COUNT(DISTINCT a.idassinatura) AS total_assinaturas,
    COUNT(DISTINCT CASE WHEN a.status = 'ativa' THEN a.idassinatura END) AS assinaturas_ativas,
    COUNT(DISTINCT CASE WHEN a.status = 'suspensa' THEN a.idassinatura END) AS assinaturas_suspensas,
    COUNT(DISTINCT CASE WHEN a.status = 'cancelada' THEN a.idassinatura END) AS assinaturas_canceladas,
    ROUND(SUM(CASE WHEN a.status = 'ativa' 
        THEN a.preco_negociado * (1 + a.aliquota_imposto/100) ELSE 0 END), 2) AS receita_mensal
FROM sistemas s
LEFT JOIN sistemas_planos sp 
    ON sp.idempresa = s.idempresa 
   AND sp.idsistema = s.idsistema
LEFT JOIN assinaturas a 
    ON a.idempresa = sp.idempresa 
   AND a.idsistema_plano = sp.idsistema_plano
WHERE s.idempresa = :idempresa
  AND s.ativo = 1
GROUP BY s.idsistema, s.idempresa, s.nome, s.descricao
ORDER BY receita_mensal DESC;