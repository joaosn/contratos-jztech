SELECT 
    ph.idpreco_historico,
    ph.idempresa,
    ph.tipo_referencia,
    ph.id_referencia,
    ph.campo_alterado,
    ph.valor_anterior,
    ph.valor_novo,
    ph.aliquota_anterior,
    ph.aliquota_nova,
    ph.idusuario_alteracao,
    u.nome AS usuario_nome,
    ph.motivo,
    ph.criado_em
FROM precos_historico ph
LEFT JOIN usuarios u 
    ON u.idempresa = ph.idempresa 
   AND u.idusuario = ph.idusuario_alteracao
WHERE ph.idempresa = :idempresa
  AND (:tipo_referencia IS NULL OR ph.tipo_referencia = :tipo_referencia)
  AND (:id_referencia IS NULL OR ph.id_referencia = :id_referencia)
  AND (:data_inicio IS NULL OR ph.criado_em >= :data_inicio)
  AND (:data_fim IS NULL OR ph.criado_em <= :data_fim)
ORDER BY ph.criado_em DESC
LIMIT :limit OFFSET :offset;