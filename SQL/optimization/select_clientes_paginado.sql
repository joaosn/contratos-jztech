-- Listagem paginada de clientes (query otimizada)
-- Parâmetros: :idempresa, :offset, :limit
SELECT 
    c.idcliente,
    c.idempresa,
    c.tipo_pessoa,
    c.nome,
    c.nome_fantasia,
    c.cpf_cnpj,
    c.email,
    c.ativo,
    c.criado_em,
    (
        SELECT COUNT(*) 
        FROM assinaturas a 
        WHERE a.idempresa = c.idempresa 
          AND a.idcliente = c.idcliente 
          AND a.status = 'ativa'
    ) AS assinaturas_ativas
FROM clientes c
WHERE c.idempresa = :idempresa
  AND c.ativo = 1
ORDER BY c.criado_em DESC
LIMIT :limit OFFSET :offset;
