UPDATE usuarios SET
    totp_habilitado = :totp_habilitado
  , totp_secret = :totp_secret
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idusuario = :idusuario;
