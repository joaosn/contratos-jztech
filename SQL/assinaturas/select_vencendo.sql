SELECT 
    a.idassinatura
  , a.idempresa
  , a.idcliente
  , a.idsistema
  , a.data_fim
  , a.preco_com_imposto
  , c.nome AS nome_cliente
  , c.email AS email_cliente
  , s.nome AS nome_sistema
  , DATEDIFF(a.data_fim, CURDATE()) AS dias_para_vencer
FROM assinaturas a
  INNER JOIN clientes c ON c.idcliente = a.idcliente
  INNER JOIN sistemas s ON s.idsistema = a.idsistema
WHERE a.idempresa = :idempresa
  AND a.status = 'ativa'
  AND a.data_fim IS NOT NULL
  AND a.data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
ORDER BY a.data_fim;
