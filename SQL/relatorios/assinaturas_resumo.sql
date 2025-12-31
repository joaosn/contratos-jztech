-- Relatório: Resumo de assinaturas
-- Parâmetros: :idempresa, :status (opcional), :idcliente (opcional), :idsistema (opcional)
SELECT 
    a.idassinatura,
    a.idempresa,
    a.idcliente,
    c.nome AS cliente_nome,
    c.cpf_cnpj AS cliente_documento,
    s.idsistema,
    s.nome AS sistema_nome,
    sp.nome AS plano_nome,
    a.ciclo_cobranca,
    a.dia_vencimento,
    a.data_inicio,
    a.data_vencimento,
    a.status,
    a.preco_negociado,
    a.aliquota_imposto,
    ROUND(a.preco_negociado * (1 + a.aliquota_imposto/100), 2) AS preco_com_imposto,
    COALESCE(addons.total_addons, 0) AS total_addons,
    COALESCE(addons.custo_addons, 0) AS custo_addons,
    ROUND(a.preco_negociado * (1 + a.aliquota_imposto/100), 2) + COALESCE(addons.custo_addons, 0) AS valor_total
FROM assinaturas a
INNER JOIN clientes c 
    ON c.idempresa = a.idempresa 
   AND c.idcliente = a.idcliente
INNER JOIN sistemas_planos sp 
    ON sp.idempresa = a.idempresa 
   AND sp.idsistema_plano = a.idsistema_plano
INNER JOIN sistemas s 
    ON s.idempresa = sp.idempresa 
   AND s.idsistema = sp.idsistema
LEFT JOIN (
    SELECT 
        aa.idempresa,
        aa.idassinatura,
        COUNT(*) AS total_addons,
        ROUND(SUM(aa.preco_negociado * aa.quantidade), 2) AS custo_addons
    FROM assinaturas_addons aa
    WHERE aa.ativo = 1
    GROUP BY aa.idempresa, aa.idassinatura
) addons ON addons.idempresa = a.idempresa AND addons.idassinatura = a.idassinatura
WHERE a.idempresa = :idempresa
  AND (:status IS NULL OR a.status = :status)
  AND (:idcliente IS NULL OR a.idcliente = :idcliente)
  AND (:idsistema IS NULL OR s.idsistema = :idsistema)
ORDER BY a.criado_em DESC;