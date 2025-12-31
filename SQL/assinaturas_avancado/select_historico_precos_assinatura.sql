-- Busca histórico de alterações de preço de uma assinatura para cálculo de pro-rata
-- Parâmetros: :idempresa, :idassinatura, :data_inicio, :data_fim
SELECT 
    ph.idpreco_historico,
    ph.idempresa,
    ph.criado_em,
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
    CASE 
        WHEN ph.tipo_referencia = 'assinatura' THEN 'Assinatura'
        WHEN ph.tipo_referencia = 'assinatura_addon' THEN 'Add-on'
        ELSE 'Outro'
    END AS tipo_alteracao
FROM precos_historico ph
LEFT JOIN usuarios u 
    ON u.idempresa = ph.idempresa 
   AND u.idusuario = ph.idusuario_alteracao
WHERE ph.idempresa = :idempresa
  AND (
    (ph.tipo_referencia = 'assinatura' AND ph.id_referencia = :idassinatura) 
    OR
    (ph.tipo_referencia = 'assinatura_addon' AND ph.id_referencia IN (
        SELECT aa.idassinatura_addon 
        FROM assinaturas_addons aa 
        WHERE aa.idempresa = :idempresa 
          AND aa.idassinatura = :idassinatura
    ))
  )
  AND ph.criado_em BETWEEN :data_inicio AND :data_fim
ORDER BY ph.criado_em DESC;