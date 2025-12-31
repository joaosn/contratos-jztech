-- Registra cancelamento de assinatura
-- Parâmetros: :idempresa, :idassinatura, :motivo_cancelamento, :data_cancelamento
-- Nota: Executar ambos os comandos em sequência ou usar transação

-- 1. Cancela a assinatura
UPDATE assinaturas
SET 
    status = 'cancelada',
    data_vencimento = :data_cancelamento,
    observacoes = CONCAT(COALESCE(observacoes, ''), '\n[CANCELAMENTO] ', :motivo_cancelamento),
    atualizado_em = NOW()
WHERE idempresa = :idempresa
  AND idassinatura = :idassinatura;