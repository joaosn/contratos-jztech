UPDATE usuarios SET
    tema = :tema
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idusuario = :idusuario;
