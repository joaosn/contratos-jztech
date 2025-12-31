SELECT COUNT(*) AS total
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND (:status IS NULL OR a.status = :status)
  AND (:idcliente IS NULL OR a.idcliente = :idcliente)
  AND (:idsistema IS NULL OR a.idsistema = :idsistema);
