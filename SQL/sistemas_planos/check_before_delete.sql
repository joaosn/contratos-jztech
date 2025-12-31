-- Verifica se plano pode ser deletado (sem assinaturas usando)
SELECT 
    COUNT(*) AS assinaturas_usando
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND a.idplano = :idplano 
  AND a.status IN ('ativa', 'trial');
