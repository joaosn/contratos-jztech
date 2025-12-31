INSERT INTO clientes_enderecos (
    idempresa,
    idcliente,
    tipo,
    logradouro,
    numero,
    complemento,
    bairro,
    cidade,
    uf,
    cep,
    pais,
    principal
) VALUES (
    :idempresa,
    :idcliente,
    :tipo,
    :logradouro,
    :numero,
    :complemento,
    :bairro,
    :cidade,
    :uf,
    :cep,
    COALESCE(:pais, 'BR'),
    COALESCE(:principal, 0)
);