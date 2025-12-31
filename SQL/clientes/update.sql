UPDATE clientes SET
    tipo_pessoa = :tipo_pessoa
  , nome = :nome
  , nome_fantasia = :nome_fantasia
  , cpf_cnpj = :cpf_cnpj
  , ie_rg = :ie_rg
  , im = :im
  , email = :email
  , telefone = :telefone
  , ativo = :ativo
WHERE idempresa = :idempresa
  AND idcliente = :idcliente;
