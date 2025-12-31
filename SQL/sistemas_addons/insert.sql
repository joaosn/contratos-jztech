INSERT INTO sistemas_addons (
    idempresa
  , idsistema
  , nome
  , descricao
  , preco_sem_imposto
  , aliquota_imposto_percent
  , ativo
) VALUES (
    :idempresa
  , :idsistema
  , :nome
  , :descricao
  , :preco_sem_imposto
  , :aliquota_imposto_percent
  , COALESCE(:ativo, 1)
);
