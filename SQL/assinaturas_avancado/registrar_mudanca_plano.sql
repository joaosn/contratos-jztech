-- Registra mudança de plano com histórico de preços
-- Parâmetros: :idempresa, :idassinatura, :idplano_novo, :preco_anterior, :preco_novo, :idusuario, :tipo_mudanca
INSERT INTO precos_historico (
    idempresa,
    tipo_referencia,
    id_referencia,
    campo_alterado,
    valor_anterior,
    valor_novo,
    aliquota_anterior,
    aliquota_nova,
    idusuario_alteracao,
    motivo
)
SELECT 
    :idempresa AS idempresa,
    'assinatura' AS tipo_referencia,
    :idassinatura AS id_referencia,
    'preco_negociado' AS campo_alterado,
    :preco_anterior AS valor_anterior,
    :preco_novo AS valor_novo,
    a.aliquota_imposto AS aliquota_anterior,
    a.aliquota_imposto AS aliquota_nova,
    :idusuario AS idusuario_alteracao,
    CONCAT(:tipo_mudanca, ' - Plano alterado para ID ', :idplano_novo) AS motivo
FROM assinaturas a
WHERE a.idempresa = :idempresa
  AND a.idassinatura = :idassinatura;