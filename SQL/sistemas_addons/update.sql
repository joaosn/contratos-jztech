UPDATE sistemas_addons SET
    nome = :nome
  , descricao = :descricao
  , preco_sem_imposto = :preco_sem_imposto
  , aliquota_imposto_percent = :aliquota_imposto_percent
  , ativo = :ativo
WHERE idempresa = :idempresa
  AND idaddon = :idaddon;
