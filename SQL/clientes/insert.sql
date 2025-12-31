INSERT INTO clientes (
    idempresa
  , tipo_pessoa
  , nome
  , nome_fantasia
  , cpf_cnpj
  , ie_rg
  , im
  , email
  , telefone
  , ativo
) VALUES (
    :idempresa
  , :tipo_pessoa
  , :nome
  , :nome_fantasia
  , :cpf_cnpj
  , :ie_rg
  , :im
  , :email
  , :telefone
  , COALESCE(:ativo, 1)
);
