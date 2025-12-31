SELECT COUNT(*) AS total
FROM empresa
WHERE ativo = COALESCE(:ativo, ativo);
