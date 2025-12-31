SELECT 
    u.idusuario
  , u.idempresa
  , u.nome
  , u.email
  , u.senha_hash
  , u.token
  , u.tema
  , u.ativo
  , u.totp_habilitado
  , u.totp_secret
  , u.ultimo_login
  , u.criado_em
  , e.nome AS nome_empresa
  , e.cnpj AS cnpj_empresa
FROM usuarios u
  INNER JOIN empresa e ON e.idempresa = u.idempresa
WHERE u.email = :email;
