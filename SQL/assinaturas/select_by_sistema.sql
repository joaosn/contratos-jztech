SELECT 
    a.idassinatura
  , a.idempresa
  , a.idcliente
  , a.idsistema
  , a.idplano
  , a.ciclo_cobranca
  , a.status
  , a.preco_com_imposto
  , c.nome AS nome_cliente
  , sp.nome AS nome_plano
FROM assinaturas a
  INNER JOIN clientes c ON c.idcliente = a.idcliente
  LEFT JOIN sistemas_planos sp ON sp.idplano = a.idplano
WHERE a.idempresa = :idempresa
  AND a.idsistema = :idsistema
ORDER BY a.status, a.data_inicio DESC;
