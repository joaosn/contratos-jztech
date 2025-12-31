-- =====================================================================
-- OrganizaAI - DDL Multi-Tenant
-- Sistema de Gestão de Contratos e Assinaturas
-- Data: 31/12/2025
-- =====================================================================

CREATE DATABASE IF NOT EXISTS organizaai
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE organizaai;

-- =====================================================================
-- TABELA: empresa (tenant central)
-- =====================================================================
CREATE TABLE empresa (
  idempresa           BIGINT UNSIGNED AUTO_INCREMENT
, nome                VARCHAR(160) NOT NULL
, nome_fantasia       VARCHAR(160) NULL
, cnpj                VARCHAR(20) NOT NULL
, email               VARCHAR(160) NULL
, telefone            VARCHAR(30) NULL
, ativo               TINYINT(1) NOT NULL DEFAULT 1
, data_cadastro       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

, CONSTRAINT pk_empresa PRIMARY KEY (idempresa)
, CONSTRAINT uq_empresa_cnpj UNIQUE (cnpj)
) ENGINE=InnoDB COMMENT='Tenant central - cada empresa é um tenant isolado';

CREATE INDEX ix_empresa_ativo ON empresa (ativo);

-- =====================================================================
-- TABELA: usuarios (com 2FA)
-- =====================================================================
CREATE TABLE usuarios (
  idusuario           BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, nome                VARCHAR(160) NOT NULL
, email               VARCHAR(160) NOT NULL
, senha_hash          VARCHAR(255) NOT NULL
, token               VARCHAR(64) NULL
, tema                VARCHAR(20) NOT NULL DEFAULT 'dark'
, ativo               TINYINT(1) NOT NULL DEFAULT 1
, totp_habilitado     TINYINT(1) NOT NULL DEFAULT 0
, totp_secret         VARCHAR(100) NULL
, ultimo_login        DATETIME NULL
, criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
, atualizado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

, CONSTRAINT pk_usuarios PRIMARY KEY (idusuario)
, CONSTRAINT uq_usuarios_email UNIQUE (email)
, CONSTRAINT fk_usuarios_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Usuários do sistema com suporte a 2FA';

CREATE INDEX ix_usuarios_empresa ON usuarios (idempresa, ativo);
CREATE INDEX ix_usuarios_token ON usuarios (token);
CREATE INDEX ix_usuarios_email ON usuarios (email);

-- =====================================================================
-- TABELA: clientes
-- =====================================================================
CREATE TABLE clientes (
  idcliente           BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, tipo_pessoa         VARCHAR(2) NOT NULL
, nome                VARCHAR(160) NOT NULL
, nome_fantasia       VARCHAR(160) NULL
, cpf_cnpj            VARCHAR(20) NOT NULL
, ie_rg               VARCHAR(30) NULL
, im                  VARCHAR(30) NULL
, email               VARCHAR(160) NULL
, telefone            VARCHAR(30) NULL
, data_cadastro       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
, ativo               TINYINT(1) NOT NULL DEFAULT 1

, CONSTRAINT pk_clientes PRIMARY KEY (idcliente)
, CONSTRAINT uq_clientes_empresa_cpf UNIQUE (idempresa, cpf_cnpj)
, CONSTRAINT fk_clientes_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT ck_clientes_tipo CHECK (tipo_pessoa IN ('pf','pj'))
) ENGINE=InnoDB COMMENT='Cadastro de clientes (PF/PJ) por empresa';

CREATE INDEX ix_clientes_empresa_nome ON clientes (idempresa, nome);
CREATE INDEX ix_clientes_empresa_ativo ON clientes (idempresa, ativo);
CREATE INDEX ix_clientes_empresa_cpf ON clientes (idempresa, cpf_cnpj);

-- =====================================================================
-- TABELA: clientes_enderecos
-- =====================================================================
CREATE TABLE clientes_enderecos (
  idendereco          BIGINT UNSIGNED AUTO_INCREMENT
, idcliente           BIGINT UNSIGNED NOT NULL
, tipo                VARCHAR(20) NOT NULL
, logradouro          VARCHAR(180) NOT NULL
, numero              VARCHAR(20) NULL
, complemento         VARCHAR(100) NULL
, bairro              VARCHAR(100) NULL
, cidade              VARCHAR(120) NOT NULL
, uf                  CHAR(2) NOT NULL
, cep                 VARCHAR(15) NULL
, pais                VARCHAR(60) NOT NULL DEFAULT 'BR'
, principal           TINYINT(1) NOT NULL DEFAULT 0

, CONSTRAINT pk_clientes_enderecos PRIMARY KEY (idendereco)
, CONSTRAINT fk_enderecos_clientes
    FOREIGN KEY (idcliente) REFERENCES clientes (idcliente)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Endereços do cliente';

CREATE INDEX ix_enderecos_cliente ON clientes_enderecos (idcliente, principal DESC);

-- =====================================================================
-- TABELA: clientes_contatos
-- =====================================================================
CREATE TABLE clientes_contatos (
  idcontato           BIGINT UNSIGNED AUTO_INCREMENT
, idcliente           BIGINT UNSIGNED NOT NULL
, nome                VARCHAR(120) NOT NULL
, email               VARCHAR(160) NULL
, telefone            VARCHAR(30) NULL
, cargo               VARCHAR(80) NULL
, principal           TINYINT(1) NOT NULL DEFAULT 0

, CONSTRAINT pk_clientes_contatos PRIMARY KEY (idcontato)
, CONSTRAINT fk_contatos_clientes
    FOREIGN KEY (idcliente) REFERENCES clientes (idcliente)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Contatos do cliente';

CREATE INDEX ix_contatos_cliente ON clientes_contatos (idcliente, principal DESC);

-- =====================================================================
-- TABELA: sistemas (catálogo por empresa)
-- =====================================================================
CREATE TABLE sistemas (
  idsistema           BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, nome                VARCHAR(120) NOT NULL
, categoria           VARCHAR(60) NULL
, descricao           TEXT NULL
, ativo               TINYINT(1) NOT NULL DEFAULT 1

, CONSTRAINT pk_sistemas PRIMARY KEY (idsistema)
, CONSTRAINT uq_sistemas_empresa_nome UNIQUE (idempresa, nome)
, CONSTRAINT fk_sistemas_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Catálogo de sistemas/produtos de software por empresa';

CREATE INDEX ix_sistemas_empresa_ativo ON sistemas (idempresa, ativo);
CREATE INDEX ix_sistemas_empresa_categoria ON sistemas (idempresa, categoria);

-- =====================================================================
-- TABELA: sistemas_planos
-- =====================================================================
CREATE TABLE sistemas_planos (
  idplano             BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, idsistema           BIGINT UNSIGNED NOT NULL
, nome                VARCHAR(120) NOT NULL
, descricao           TEXT NULL
, ciclo_cobranca      VARCHAR(12) NOT NULL
, preco_base_sem_imposto DECIMAL(12,2) NOT NULL DEFAULT 0.00
, aliquota_imposto_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00
, preco_base_com_imposto DECIMAL(12,2) AS
    (ROUND(preco_base_sem_imposto * (1 + (aliquota_imposto_percent/100)), 2)) STORED
, ativo               TINYINT(1) NOT NULL DEFAULT 1

, CONSTRAINT pk_sistemas_planos PRIMARY KEY (idplano)
, CONSTRAINT fk_planos_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_planos_sistemas
    FOREIGN KEY (idsistema) REFERENCES sistemas (idsistema)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT ck_planos_ciclo CHECK (ciclo_cobranca IN ('mensal','trimestral','semestral','anual'))
) ENGINE=InnoDB COMMENT='Planos de cada sistema (preço base e alíquota padrão)';

CREATE INDEX ix_planos_empresa_sistema ON sistemas_planos (idempresa, idsistema, ativo);
CREATE INDEX ix_planos_empresa_ativo ON sistemas_planos (idempresa, ativo);

-- =====================================================================
-- TABELA: sistemas_addons
-- =====================================================================
CREATE TABLE sistemas_addons (
  idaddon             BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, idsistema           BIGINT UNSIGNED NOT NULL
, nome                VARCHAR(120) NOT NULL
, descricao           TEXT NULL
, preco_sem_imposto   DECIMAL(12,2) NOT NULL DEFAULT 0.00
, aliquota_imposto_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00
, preco_com_imposto   DECIMAL(12,2) AS
    (ROUND(preco_sem_imposto * (1 + (aliquota_imposto_percent/100)), 2)) STORED
, ativo               TINYINT(1) NOT NULL DEFAULT 1

, CONSTRAINT pk_sistemas_addons PRIMARY KEY (idaddon)
, CONSTRAINT fk_addons_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_addons_sistemas
    FOREIGN KEY (idsistema) REFERENCES sistemas (idsistema)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Add-ons opcionais cobrados à parte';

CREATE INDEX ix_addons_empresa_sistema ON sistemas_addons (idempresa, idsistema, ativo);
CREATE INDEX ix_addons_empresa_ativo ON sistemas_addons (idempresa, ativo);

-- =====================================================================
-- TABELA: assinaturas
-- =====================================================================
CREATE TABLE assinaturas (
  idassinatura        BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, idcliente           BIGINT UNSIGNED NOT NULL
, idsistema           BIGINT UNSIGNED NOT NULL
, idplano             BIGINT UNSIGNED NULL
, ciclo_cobranca      VARCHAR(12) NOT NULL
, dia_vencimento      TINYINT UNSIGNED NOT NULL DEFAULT 5
, data_inicio         DATE NOT NULL
, data_fim            DATE NULL
, status              VARCHAR(12) NOT NULL DEFAULT 'ativa'
, preco_sem_imposto   DECIMAL(12,2) NULL
, aliquota_imposto_percent DECIMAL(5,2) NULL
, preco_com_imposto   DECIMAL(12,2) AS (
    ROUND(
      COALESCE(preco_sem_imposto, (SELECT p.preco_base_sem_imposto FROM sistemas_planos p WHERE p.idplano = idplano))
      * (1 + (COALESCE(aliquota_imposto_percent, (SELECT p.aliquota_imposto_percent FROM sistemas_planos p WHERE p.idplano = idplano))/100))
    , 2)
  ) STORED
, observacoes         VARCHAR(400) NULL
, criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
, atualizado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

, CONSTRAINT pk_assinaturas PRIMARY KEY (idassinatura)
, CONSTRAINT fk_assinaturas_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_assinaturas_clientes
    FOREIGN KEY (idcliente) REFERENCES clientes (idcliente)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_assinaturas_sistemas
    FOREIGN KEY (idsistema) REFERENCES sistemas (idsistema)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_assinaturas_plano
    FOREIGN KEY (idplano) REFERENCES sistemas_planos (idplano)
    ON UPDATE CASCADE ON DELETE SET NULL
, CONSTRAINT ck_assinaturas_status CHECK (status IN ('ativa','suspensa','cancelada','trial'))
, CONSTRAINT ck_assinaturas_ciclo CHECK (ciclo_cobranca IN ('mensal','trimestral','semestral','anual'))
, CONSTRAINT ck_assinaturas_dia CHECK (dia_vencimento BETWEEN 1 AND 28)
) ENGINE=InnoDB COMMENT='Contratos de assinatura por cliente';

CREATE INDEX ix_assinaturas_empresa_cliente ON assinaturas (idempresa, idcliente, status);
CREATE INDEX ix_assinaturas_empresa_sistema ON assinaturas (idempresa, idsistema, status);
CREATE INDEX ix_assinaturas_empresa_status ON assinaturas (idempresa, status);
CREATE INDEX ix_assinaturas_empresa_vencimento ON assinaturas (idempresa, data_fim);

-- =====================================================================
-- TABELA: assinaturas_addons
-- =====================================================================
CREATE TABLE assinaturas_addons (
  idassinatura_addon  BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, idassinatura        BIGINT UNSIGNED NOT NULL
, idaddon             BIGINT UNSIGNED NOT NULL
, quantidade          INT UNSIGNED NOT NULL DEFAULT 1
, preco_sem_imposto   DECIMAL(12,2) NULL
, aliquota_imposto_percent DECIMAL(5,2) NULL
, preco_com_imposto   DECIMAL(12,2) AS (
    ROUND(
      COALESCE(preco_sem_imposto, (SELECT a.preco_sem_imposto FROM sistemas_addons a WHERE a.idaddon = idaddon))
      * (1 + (COALESCE(aliquota_imposto_percent, (SELECT a.aliquota_imposto_percent FROM sistemas_addons a WHERE a.idaddon = idaddon))/100))
    , 2)
  ) STORED
, ativo               TINYINT(1) NOT NULL DEFAULT 1

, CONSTRAINT pk_assinaturas_addons PRIMARY KEY (idassinatura_addon)
, CONSTRAINT fk_ass_addons_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT fk_assinaturas_addons_assinatura
    FOREIGN KEY (idassinatura) REFERENCES assinaturas (idassinatura)
    ON UPDATE CASCADE ON DELETE CASCADE
, CONSTRAINT fk_assinaturas_addons_addon
    FOREIGN KEY (idaddon) REFERENCES sistemas_addons (idaddon)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Add-ons contratados em cada assinatura';

CREATE UNIQUE INDEX uq_ass_addons_empresa ON assinaturas_addons (idempresa, idassinatura, idaddon);
CREATE INDEX ix_ass_addons_empresa ON assinaturas_addons (idempresa, idassinatura);

-- =====================================================================
-- TABELA: precos_historico (auditoria)
-- =====================================================================
CREATE TABLE precos_historico (
  idpreco_hist        BIGINT UNSIGNED AUTO_INCREMENT
, idempresa           BIGINT UNSIGNED NOT NULL
, referencia_tipo     VARCHAR(20) NOT NULL
, referencia_id       BIGINT UNSIGNED NOT NULL
, campo               VARCHAR(40) NOT NULL
, valor_antigo        DECIMAL(12,2) NULL
, valor_novo          DECIMAL(12,2) NULL
, alterado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
, alterado_por        VARCHAR(80) NULL

, CONSTRAINT pk_precos_historico PRIMARY KEY (idpreco_hist)
, CONSTRAINT fk_historico_empresa
    FOREIGN KEY (idempresa) REFERENCES empresa (idempresa)
    ON UPDATE CASCADE ON DELETE RESTRICT
, CONSTRAINT ck_hist_referencia CHECK (referencia_tipo IN ('plano','assinatura','addon','assinatura_addon'))
) ENGINE=InnoDB COMMENT='Auditoria de mudanças de preço/alíquota';

CREATE INDEX ix_hist_empresa_ref ON precos_historico (idempresa, referencia_tipo, referencia_id, alterado_em DESC);
CREATE INDEX ix_hist_empresa_data ON precos_historico (idempresa, alterado_em DESC);

-- =====================================================================
-- VIEW: v_assinaturas_resumo
-- =====================================================================
CREATE OR REPLACE VIEW v_assinaturas_resumo AS
SELECT
  a.idassinatura
, a.idempresa
, a.idcliente
, a.idsistema
, a.idplano
, a.status
, a.ciclo_cobranca
, a.dia_vencimento
, a.data_inicio
, a.data_fim
, COALESCE(a.preco_sem_imposto, p.preco_base_sem_imposto) AS preco_sem_imposto
, COALESCE(a.aliquota_imposto_percent, p.aliquota_imposto_percent) AS aliquota_imposto_percent
, a.preco_com_imposto
FROM assinaturas a
LEFT JOIN sistemas_planos p ON p.idplano = a.idplano;

-- =====================================================================
-- VIEW: v_assinaturas_total_mensal
-- =====================================================================
CREATE OR REPLACE VIEW v_assinaturas_total_mensal AS
SELECT
  a.idassinatura
, a.idempresa
, a.idcliente
, a.idsistema
, a.status
, a.ciclo_cobranca
, ROUND(
    (SELECT COALESCE(v.preco_com_imposto, 0)
       FROM v_assinaturas_resumo v
      WHERE v.idassinatura = a.idassinatura)
    +
    COALESCE((
      SELECT SUM(aa.preco_com_imposto * aa.quantidade)
        FROM assinaturas_addons aa
       WHERE aa.idassinatura = a.idassinatura
         AND aa.ativo = 1
    ), 0)
  , 2) AS total_com_imposto
FROM assinaturas a;

-- =====================================================================
-- DADOS INICIAIS: Empresa e Usuário Admin
-- =====================================================================
INSERT INTO empresa (nome, cnpj, ativo) VALUES ('JZTech', '00000000000000', 1);

INSERT INTO usuarios (idempresa, nome, email, senha_hash, ativo) 
VALUES (1, 'Administrador', 'admin@jztech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
-- Senha padrão: password (trocar após primeiro login)
