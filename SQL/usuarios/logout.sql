UPDATE usuarios SET
    token = NULL
  , atualizado_em = CURRENT_TIMESTAMP
WHERE token = :token;
