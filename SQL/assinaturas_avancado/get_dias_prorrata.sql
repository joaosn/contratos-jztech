-- Calcula dias de pro-rata entre data_inicio e data_fim
-- Parâmetros: :data_inicio, :data_fim
-- Nota: Query utilitária, não precisa de idempresa (cálculo puro)
SELECT 
    DATEDIFF(:data_fim, :data_inicio) AS dias_decorridos,
    DAY(LAST_DAY(:data_fim)) AS dias_mes_atual,
    ROUND(
        (DATEDIFF(:data_fim, :data_inicio) / DAY(LAST_DAY(:data_fim))) * 100,
        2
    ) AS percentual_mes;