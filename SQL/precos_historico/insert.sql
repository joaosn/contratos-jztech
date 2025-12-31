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
) VALUES (
    :idempresa,
    :tipo_referencia,
    :id_referencia,
    :campo_alterado,
    :valor_anterior,
    :valor_novo,
    :aliquota_anterior,
    :aliquota_nova,
    :idusuario_alteracao,
    :motivo
);