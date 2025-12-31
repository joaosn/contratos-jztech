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
  , a.preco_sem_imposto
  , a.aliquota_imposto_percent
  , a.preco_com_imposto
  , a.observacoes
  , a.criado_em
  , a.atualizado_em
  , c.nome AS nome_cliente
  , c.cpf_cnpj
  , s.nome AS nome_sistema
  , sp.nome AS nome_plano
FROM assinaturas a
  INNER JOIN clientes c ON c.idcliente = a.idcliente
  INNER JOIN sistemas s ON s.idsistema = a.idsistema
  LEFT JOIN sistemas_planos sp ON sp.idplano = a.idplano
WHERE a.idempresa = :idempresa
  AND (:status IS NULL OR a.status = :status)
  AND (:idcliente IS NULL OR a.idcliente = :idcliente)
  AND (:idsistema IS NULL OR a.idsistema = :idsistema)
ORDER BY a.criado_em DESC
LIMIT :limit OFFSET :offset;
