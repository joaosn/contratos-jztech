SELECT 
    u.idusuario
  , u.idempresa
  , u.nome
  , u.email
  , u.token
  , u.tema
  , u.ativo
  , u.totp_habilitado
  , e.nome AS nome_empresa
  , e.cnpj AS cnpj_empresa
FROM usuarios u
  INNER JOIN empresa e ON e.idempresa = u.idempresa
WHERE u.token = :token
  AND u.ativo = 1;
