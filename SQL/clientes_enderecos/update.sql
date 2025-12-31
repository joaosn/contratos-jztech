UPDATE clientes_enderecos SET
    tipo = :tipo,
    logradouro = :logradouro,
    numero = :numero,
    complemento = :complemento,
    bairro = :bairro,
    cidade = :cidade,
    uf = :uf,
    cep = :cep,
    pais = :pais,
    principal = :principal
WHERE idempresa = :idempresa
  AND idendereco = :idendereco;