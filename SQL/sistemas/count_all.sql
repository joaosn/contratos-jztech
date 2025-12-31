SELECT COUNT(*) AS total
FROM sistemas s
WHERE s.idempresa = :idempresa
  AND (:ativo IS NULL OR s.ativo = :ativo)
  AND (:categoria IS NULL OR s.categoria = :categoria);
