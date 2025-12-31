-- Define contato como principal
UPDATE clientes_contatos 
SET principal = 1 
WHERE idempresa = :idempresa
  AND idcontato = :idcontato;