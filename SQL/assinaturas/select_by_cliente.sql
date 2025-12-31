SELECT 
    a.idassinatura
  , a.idempresa
  , a.idcliente
  , a.idsistema
  , a.idplano
  , a.ciclo_cobranca
  , a.dia_vencimento
  , a.data_inicio
  , a.data_fim
  , a.status
  , a.preco_com_imposto
  , s.nome AS nome_sistema
  , sp.nome AS nome_plano
FROM assinaturas a
  INNER JOIN sistemas s ON s.idsistema = a.idsistema
  LEFT JOIN sistemas_planos sp ON sp.idplano = a.idplano
WHERE a.idempresa = :idempresa
  AND a.idcliente = :idcliente
ORDER BY a.status, a.data_inicio DESC;
