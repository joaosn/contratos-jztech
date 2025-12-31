UPDATE usuarios SET
    senha_hash = :senha_hash
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idusuario = :idusuario;
