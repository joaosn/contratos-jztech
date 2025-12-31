INSERT INTO usuarios (
    idempresa
  , nome
  , email
  , senha_hash
  , tema
  , ativo
) VALUES (
    :idempresa
  , :nome
  , :email
  , :senha_hash
  , :tema
  , :ativo
);
