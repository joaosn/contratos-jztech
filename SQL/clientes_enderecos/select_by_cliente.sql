SELECT 
    ce.idendereco,
    ce.idempresa,
    ce.idcliente,
    ce.tipo,
    ce.logradouro,
    ce.numero,
    ce.complemento,
    ce.bairro,
    ce.cidade,
    ce.uf,
    ce.cep,
    ce.pais,
    ce.principal
FROM clientes_enderecos ce
WHERE ce.idempresa = :idempresa
  AND ce.idcliente = :idcliente
ORDER BY ce.principal DESC, ce.tipo;