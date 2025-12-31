INSERT INTO assinaturas_addons (
    idempresa
  , idassinatura
  , idaddon
  , quantidade
  , preco_sem_imposto
  , aliquota_imposto_percent
  , ativo
) VALUES (
    :idempresa
  , :idassinatura
  , :idaddon
  , :quantidade
  , :preco_sem_imposto
  , :aliquota_imposto_percent
  , COALESCE(:ativo, 1)
);
