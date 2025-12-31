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
WHERE ativo = 1
ORDER BY nome;
