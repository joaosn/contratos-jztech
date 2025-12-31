UPDATE empresa SET
    nome = :nome
  , nome_fantasia = :nome_fantasia
  , cnpj = :cnpj
  , email = :email
  , telefone = :telefone
  , ativo = :ativo
WHERE idempresa = :idempresa;
