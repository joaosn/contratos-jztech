-- Listagem de assinaturas ativas por cliente (query otimizada)
-- Parâmetros: :idempresa, :idcliente
SELECT 
    a.idassinatura,
    a.idempresa,
    a.idcliente,
    a.idsistema_plano,
    a.preco_negociado,
    a.aliquota_imposto,
    a.data_inicio,
    a.data_vencimento,
    a.status,
    s.nome AS sistema_nome,
    sp.nome AS plano_nome,
    DATEDIFF(a.data_vencimento, CURDATE()) AS dias_para_vencer
FROM assinaturas a
INNER JOIN sistemas_planos sp 
    ON sp.idempresa = a.idempresa 
   AND sp.idsistema_plano = a.idsistema_plano
INNER JOIN sistemas s 
    ON s.idempresa = sp.idempresa 
   AND s.idsistema = sp.idsistema
WHERE a.idempresa = :idempresa
  AND a.idcliente = :idcliente
  AND a.status = 'ativa'
ORDER BY a.data_vencimento ASC;
