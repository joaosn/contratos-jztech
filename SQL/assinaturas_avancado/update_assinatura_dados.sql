-- Atualiza dados de assinatura (preço, alíquota, dados de vencimento)
-- Parâmetros: :idempresa, :idassinatura, :preco_novo, :aliquota_nova, :data_vencimento_novo
UPDATE assinaturas
SET 
    preco_negociado = :preco_novo,
    aliquota_imposto = :aliquota_nova,
    data_vencimento = :data_vencimento_novo,
    atualizado_em = NOW()
WHERE idempresa = :idempresa
  AND idassinatura = :idassinatura;