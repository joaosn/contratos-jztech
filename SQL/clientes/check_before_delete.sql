-- Verifica se cliente pode ser deletado (sem assinaturas ativas)
SELECT 
    COUNT(*) AS assinaturas_ativas
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND a.idcliente = :idcliente 
  AND a.status IN ('ativa', 'trial');
