INSERT INTO sistemas (
    idempresa
  , nome
  , categoria
  , descricao
  , ativo
) VALUES (
    :idempresa
  , :nome
  , :categoria
  , :descricao
  , COALESCE(:ativo, 1)
);
