SELECT 
    s.idsistema
  , s.idempresa
  , s.nome
  , s.categoria
  , s.descricao
  , s.ativo
FROM sistemas s
WHERE s.idempresa = :idempresa
  AND s.ativo = 1
ORDER BY s.nome;
