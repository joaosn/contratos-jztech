UPDATE assinaturas SET
    status = :status
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idassinatura = :idassinatura;
