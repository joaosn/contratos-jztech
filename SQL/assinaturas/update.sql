UPDATE assinaturas SET
    idplano = :idplano
  , ciclo_cobranca = :ciclo_cobranca
  , dia_vencimento = :dia_vencimento
  , data_inicio = :data_inicio
  , data_fim = :data_fim
  , preco_sem_imposto = :preco_sem_imposto
  , aliquota_imposto_percent = :aliquota_imposto_percent
  , observacoes = :observacoes
  , atualizado_em = CURRENT_TIMESTAMP
WHERE idempresa = :idempresa
  AND idassinatura = :idassinatura;
