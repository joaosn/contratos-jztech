SELECT 
    aa.idassinatura_addon,
    aa.idempresa,
    aa.idassinatura,
    aa.idsistema_addon,
    sa.nome AS addon_nome,
    sa.descricao AS addon_descricao,
    aa.preco_negociado,
    aa.quantidade,
    (aa.preco_negociado * aa.quantidade) AS valor_total,
    aa.ativo,
    aa.criado_em,
    aa.atualizado_em
FROM assinaturas_addons aa
INNER JOIN sistemas_addons sa 
    ON sa.idempresa = aa.idempresa 
   AND sa.idsistema_addon = aa.idsistema_addon
WHERE aa.idempresa = :idempresa
  AND aa.idassinatura_addon = :idassinatura_addon;
