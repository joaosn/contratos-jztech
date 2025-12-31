SELECT 
    c.idcliente
  , c.idempresa
  , c.tipo_pessoa
  , c.nome
  , c.nome_fantasia
  , c.cpf_cnpj
  , c.email
  , c.telefone
  , c.ativo
FROM clientes c
WHERE c.idempresa = :idempresa
  AND (
    c.nome LIKE CONCAT('%', :termo, '%')
    OR c.nome_fantasia LIKE CONCAT('%', :termo, '%')
    OR c.cpf_cnpj LIKE CONCAT('%', :termo, '%')
    OR c.email LIKE CONCAT('%', :termo, '%')
  )
  AND (:ativo IS NULL OR c.ativo = :ativo)
ORDER BY c.nome
LIMIT :limit OFFSET :offset;
