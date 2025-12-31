SELECT 
    s.idsistema
  , s.idempresa
  , s.nome
  , s.categoria
  , s.descricao
  , s.ativo
  , COUNT(DISTINCT sp.idplano) AS total_planos
  , COUNT(DISTINCT sa.idaddon) AS total_addons
  , COUNT(DISTINCT a.idassinatura) AS total_assinaturas
FROM sistemas s
  LEFT JOIN sistemas_planos sp ON sp.idsistema = s.idsistema AND sp.ativo = 1
  LEFT JOIN sistemas_addons sa ON sa.idsistema = s.idsistema AND sa.ativo = 1
  LEFT JOIN assinaturas a ON a.idsistema = s.idsistema AND a.status = 'ativa'
WHERE s.idempresa = :idempresa
  AND (:ativo IS NULL OR s.ativo = :ativo)
  AND (:categoria IS NULL OR s.categoria = :categoria)
GROUP BY s.idsistema
ORDER BY s.nome
LIMIT :limit OFFSET :offset;
