SELECT 
    sa.idaddon
  , sa.idempresa
  , sa.idsistema
  , sa.nome
  , sa.descricao
  , sa.preco_sem_imposto
  , sa.aliquota_imposto_percent
  , sa.preco_com_imposto
  , sa.ativo
  , s.nome AS nome_sistema
FROM sistemas_addons sa
  INNER JOIN sistemas s ON s.idsistema = sa.idsistema
WHERE sa.idempresa = :idempresa
  AND sa.idaddon = :idaddon;
