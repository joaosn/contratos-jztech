-- Verifica se addon pode ser deletado (sem assinaturas usando)
SELECT 
    COUNT(*) AS assinaturas_usando
FROM assinaturas_addons aa
WHERE aa.idempresa = :idempresa
  AND aa.idaddon = :idaddon 
  AND aa.ativo = 1;
