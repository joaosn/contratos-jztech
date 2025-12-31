DELETE FROM precos_historico
WHERE idempresa = :idempresa
  AND criado_em < DATE_SUB(NOW(), INTERVAL :dias_retencao DAY);