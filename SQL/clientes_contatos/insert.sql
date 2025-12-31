INSERT INTO clientes_contatos (
    idempresa,
    idcliente,
    nome,
    email,
    telefone,
    cargo,
    principal
) VALUES (
    :idempresa,
    :idcliente,
    :nome,
    :email,
    :telefone,
    :cargo,
    COALESCE(:principal, 0)
);