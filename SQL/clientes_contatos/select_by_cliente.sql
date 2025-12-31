SELECT 
    cc.idcontato,
    cc.idempresa,
    cc.idcliente,
    cc.nome,
    cc.email,
    cc.telefone,
    cc.cargo,
    cc.principal
FROM clientes_contatos cc
WHERE cc.idempresa = :idempresa
  AND cc.idcliente = :idcliente
ORDER BY cc.principal DESC, cc.nome;