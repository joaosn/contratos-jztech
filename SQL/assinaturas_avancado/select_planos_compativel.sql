-- Busca planos compatíveis para mudança (mesmo sistema, diferentes preços)
-- Parâmetros: :idempresa, :idsistema
SELECT 
    sp.idsistema_plano,
    sp.idempresa,
    sp.idsistema,
    sp.nome,
    sp.descricao,
    sp.preco,
    sp.limite_usuarios,
    sp.limite_dispositivos,
    sp.ativo,
    s.nome AS sistema_nome,
    COUNT(DISTINCT a.idassinatura) AS total_assinantes
FROM sistemas_planos sp
INNER JOIN sistemas s 
    ON s.idempresa = sp.idempresa 
   AND s.idsistema = sp.idsistema
LEFT JOIN assinaturas a 
    ON a.idempresa = sp.idempresa 
   AND a.idsistema_plano = sp.idsistema_plano 
   AND a.status = 'ativa'
WHERE sp.idempresa = :idempresa
  AND sp.idsistema = :idsistema
  AND sp.ativo = 1
GROUP BY sp.idsistema_plano, sp.idempresa, sp.idsistema, sp.nome, sp.descricao, 
         sp.preco, sp.limite_usuarios, sp.limite_dispositivos, sp.ativo, s.nome
ORDER BY sp.preco ASC;