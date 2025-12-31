UPDATE sistemas SET
    nome = :nome
  , categoria = :categoria
  , descricao = :descricao
  , ativo = :ativo
WHERE idempresa = :idempresa
  AND idsistema = :idsistema;
