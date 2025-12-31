-- Calcula valor de reembolso no cancelamento (pro-rata retroativo)
-- Parâmetros: :idempresa, :idassinatura, :data_cancelamento
SELECT 
    a.idassinatura,
    a.idempresa,
    a.preco_negociado,
    a.data_inicio,
    a.data_vencimento,
    :data_cancelamento AS data_cancelamento,
    DATEDIFF(:data_cancelamento, a.data_inicio) AS dias_usados,
    DAY(LAST_DAY(a.data_vencimento)) AS dias_no_mes,
    ROUND(
        (DATEDIFF(:data_cancelamento, a.data_inicio) / DAY(LAST_DAY(a.data_vencimento))) * 100,
        2
    ) AS percentual_uso,
    ROUND(
        a.preco_negociado * (1 + a.aliquota_imposto / 100),
        2
    ) AS valor_com_impostos,
    ROUND(
        (a.preco_negociado * (DATEDIFF(:data_cancelamento, a.data_inicio) / DAY(LAST_DAY(a.data_vencimento)))) * (1 + a.aliquota_imposto / 100),
        2
    ) AS valor_cobrar_proporcional,
    ROUND(
        (a.preco_negociado * (1 + a.aliquota_imposto / 100)) - 
        (a.preco_negociado * (DATEDIFF(:data_cancelamento, a.data_inicio) / DAY(LAST_DAY(a.data_vencimento)))) * (1 + a.aliquota_imposto / 100),
        2
    ) AS valor_reembolso
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND a.idassinatura = :idassinatura;