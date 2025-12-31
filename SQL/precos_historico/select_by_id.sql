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
  AND ph.idpreco_historico = :idpreco_historico;