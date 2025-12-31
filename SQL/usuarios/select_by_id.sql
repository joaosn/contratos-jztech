SELECT 
    u.idusuario
  , u.idempresa
  , u.nome
  , u.email
  , u.tema
  , u.ativo
  , u.totp_habilitado
  , u.ultimo_login
  , u.criado_em
  , e.nome AS nome_empresa
FROM usuarios u
  INNER JOIN empresa e ON e.idempresa = u.idempresa
WHERE u.idempresa = :idempresa
  AND u.idusuario = :idusuario;
