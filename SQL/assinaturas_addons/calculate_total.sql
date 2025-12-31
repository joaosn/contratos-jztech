SELECT 
    SUM(aa.preco_negociado * aa.quantidade) AS total_addons
FROM assinaturas_addons aa
WHERE aa.idempresa = :idempresa
  AND aa.idassinatura = :idassinatura
  AND aa.ativo = 1;
