UPDATE usuarios SET
    nome = :nome
  , email = :email
  , ativo = :ativo
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idusuario = :idusuario;
