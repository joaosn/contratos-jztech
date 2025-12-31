-- Busca assinaturas vencidas ou próximas do vencimento para renovação
-- Parâmetros: :idempresa, :dias_antecedencia (ex: 5 dias antes do vencimento)
SELECT 
    a.idassinatura,
    a.idempresa,
    a.idcliente,
    a.idsistema_plano,
    a.preco_negociado,
    a.data_inicio,
    a.data_vencimento,
    a.status,
    a.aliquota_imposto,
    c.nome AS cliente_nome,
    sp.nome AS plano_nome,
    sp.preco AS preco_plano_base,
    s.nome AS sistema_nome,
    DATEDIFF(a.data_vencimento, CURDATE()) AS dias_restantes,
    (
        SELECT COALESCE(SUM(aa.preco_negociado * aa.quantidade), 0) 
        FROM assinaturas_addons aa 
        WHERE aa.idempresa = a.idempresa 
          AND aa.idassinatura = a.idassinatura 
          AND aa.ativo = 1
    ) AS total_addons
FROM assinaturas a
INNER JOIN clientes c 
    ON c.idempresa = a.idempresa 
   AND c.idcliente = a.idcliente
INNER JOIN sistemas_planos sp 
    ON sp.idempresa = a.idempresa 
   AND sp.idsistema_plano = a.idsistema_plano
INNER JOIN sistemas s 
    ON s.idempresa = sp.idempresa 
   AND s.idsistema = sp.idsistema
WHERE a.idempresa = :idempresa
  AND a.status IN ('ativa', 'suspensa')
  AND DATEDIFF(a.data_vencimento, CURDATE()) <= :dias_antecedencia
  AND DATEDIFF(a.data_vencimento, CURDATE()) >= 0
ORDER BY a.data_vencimento ASC;