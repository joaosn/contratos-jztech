-- Verifica se sistema pode ser deletado (sem assinaturas ativas)
SELECT 
    COUNT(*) AS assinaturas_ativas
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND a.idsistema = :idsistema 
  AND a.status IN ('ativa', 'trial');
