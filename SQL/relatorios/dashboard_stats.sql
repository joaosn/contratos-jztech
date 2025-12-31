-- Relatório: Estatísticas do Dashboard
-- Parâmetros: :idempresa
SELECT 
    (SELECT COUNT(*) FROM clientes WHERE idempresa = :idempresa) AS total_clientes,
    (SELECT COUNT(*) FROM clientes WHERE idempresa = :idempresa AND ativo = 1) AS clientes_ativos,
    (SELECT COUNT(*) FROM sistemas WHERE idempresa = :idempresa) AS total_sistemas,
    (SELECT COUNT(*) FROM sistemas WHERE idempresa = :idempresa AND ativo = 1) AS sistemas_ativos,
    (SELECT COUNT(*) FROM assinaturas WHERE idempresa = :idempresa) AS total_assinaturas,
    (SELECT COUNT(*) FROM assinaturas WHERE idempresa = :idempresa AND status = 'ativa') AS assinaturas_ativas,
    (SELECT COUNT(*) FROM assinaturas WHERE idempresa = :idempresa AND status = 'suspensa') AS assinaturas_suspensas,
    (SELECT COUNT(*) FROM assinaturas WHERE idempresa = :idempresa AND status = 'cancelada') AS assinaturas_canceladas,
    (
        SELECT ROUND(COALESCE(SUM(a.preco_negociado * (1 + a.aliquota_imposto/100)), 0), 2)
        FROM assinaturas a
        WHERE a.idempresa = :idempresa AND a.status = 'ativa'
    ) AS receita_mensal_base,
    (
        SELECT ROUND(COALESCE(SUM(aa.preco_negociado * aa.quantidade), 0), 2)
        FROM assinaturas_addons aa
        INNER JOIN assinaturas a 
            ON a.idempresa = aa.idempresa 
           AND a.idassinatura = aa.idassinatura
        WHERE aa.idempresa = :idempresa 
          AND aa.ativo = 1 
          AND a.status = 'ativa'
    ) AS receita_addons_total,
    (
        SELECT ROUND(AVG(a.preco_negociado * (1 + a.aliquota_imposto/100)), 2)
        FROM assinaturas a
        WHERE a.idempresa = :idempresa AND a.status = 'ativa'
    ) AS ticket_medio;