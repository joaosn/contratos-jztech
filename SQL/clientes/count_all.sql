SELECT COUNT(*) AS total
FROM clientes c
WHERE c.idempresa = :idempresa
  AND (:ativo IS NULL OR c.ativo = :ativo)
  AND (:tipo_pessoa IS NULL OR c.tipo_pessoa = :tipo_pessoa);
