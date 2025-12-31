SELECT 
    idempresa
  , nome
  , nome_fantasia
  , cnpj
  , email
  , telefone
  , ativo
  , data_cadastro
FROM empresa
WHERE idempresa = :idempresa;
