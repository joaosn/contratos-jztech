-- Desativa add-ons ao cancelar assinatura
-- Parâmetros: :idempresa, :idassinatura
-- Nota: Executar após update_cancelamento.sql
UPDATE assinaturas_addons
SET 
    ativo = 0,
    atualizado_em = NOW()
WHERE idempresa = :idempresa
  AND idassinatura = :idassinatura
  AND ativo = 1;
