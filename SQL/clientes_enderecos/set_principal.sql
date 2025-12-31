-- Define endereço como principal
UPDATE clientes_enderecos 
SET principal = 1 
WHERE idempresa = :idempresa
  AND idendereco = :idendereco;