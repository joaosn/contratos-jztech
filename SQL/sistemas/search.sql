SELECT 
    s.idsistema
  , s.idempresa
  , s.nome
  , s.categoria
  , s.descricao
  , s.ativo
FROM sistemas s
WHERE s.idempresa = :idempresa
  AND (
    s.nome LIKE CONCAT('%', :termo, '%')
    OR s.categoria LIKE CONCAT('%', :termo, '%')
    OR s.descricao LIKE CONCAT('%', :termo, '%')
  )
ORDER BY s.nome
LIMIT :limit OFFSET :offset;
