UPDATE usuarios SET
    token = :token
  , ultimo_login = CURRENT_TIMESTAMP
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idusuario = :idusuario;
