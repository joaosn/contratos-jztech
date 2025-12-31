UPDATE assinaturas_addons SET
    quantidade = :quantidade
  , preco_sem_imposto = :preco_sem_imposto
  , aliquota_imposto_percent = :aliquota_imposto_percent
  , ativo = :ativo
WHERE idempresa = :idempresa
  AND idassinatura_addon = :idassinatura_addon;
