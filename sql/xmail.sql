# Module: XMAIL
# Version: v1.10
# Release Date: 17 Fevereiro 2004
# Author: Claudia Antonini Vitiello Callegari
# License: GNU
#


# MySQL-Front Dump 2.5
#
# Host: localhost   Database: xoops
# --------------------------------------------------------
# Server version 3.23.51-nt


#
# Table structure for table 'xoops_xmail_files'
#

CREATE TABLE `xmail_files` (
    `fileid`       INT(8)       NOT NULL AUTO_INCREMENT,
    `filerealname` VARCHAR(255) NOT NULL DEFAULT '',
    `date`         INT(10)      NOT NULL DEFAULT '0',
    `ext`          VARCHAR(64)  NOT NULL DEFAULT '',
    `minetype`     VARCHAR(64)  NOT NULL DEFAULT '',
    `filedescript` TEXT,
    `uid`          INT(10)      NOT NULL DEFAULT '0',
    `dir_upload`   VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`fileid`),
    KEY `uid` (`uid`)
)
    ENGINE = ISAM COMMENT ='by Claudia A. V. Callegari';



#
# Table structure for table 'xoops_xmail_men_anexo'
#

CREATE TABLE `xmail_men_anexo` (
    `fileid` INT(8) NOT NULL DEFAULT '0',
    `idmen`  INT(5) NOT NULL DEFAULT '0',
    KEY `idmen` (`idmen`),
    KEY `fileid` (`fileid`)
)
    ENGINE = ISAM COMMENT ='by Claudia A. V. Callegari';



#
# Table structure for table 'xoops_xmail_mensage'
#

CREATE TABLE `xmail_mensage` (
    `id_men`      INT(5) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title_men`   VARCHAR(50)         NOT NULL DEFAULT '',
    `subject_men` VARCHAR(80)         NOT NULL DEFAULT '',
    `body_men`    TEXT                NOT NULL,
    `aprovada`    TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `uid`         INT(6) UNSIGNED     NOT NULL DEFAULT '0',
    `datesub`     INT(11) UNSIGNED    NOT NULL DEFAULT '0',
    `date_envio`  INT(11) UNSIGNED    NOT NULL DEFAULT '0',
    `dohtml`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `dobr`        TINYINT(1) UNSIGNED NOT NULL,
    `is_new`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_men`),
    KEY `title_men` (`title_men`),
    KEY `aprovada` (`aprovada`)
)
    ENGINE = ISAM COMMENT ='by Claudia A. V. Callegari';



#
# Table structure for table 'xoops_xmail_param'
#

CREATE TABLE `xmail_param` (
    `dias_excluir`  INT(4) UNSIGNED     NOT NULL DEFAULT '100',
    `envia_xmails`  INT(4) UNSIGNED     NOT NULL DEFAULT '50',
    `ordem_admin`   CHAR(2)                      DEFAULT 'A' NOT NULL,
    `limite_page`   TINYINT(4)                   DEFAULT '10' NOT NULL,
    `aprov_auto`    TINYINT(1)          NOT NULL DEFAULT '0',
    `dir_upload`    VARCHAR(255)        NOT NULL DEFAULT '/modules/xmail/upload',
    `selmimetype`   TEXT                NOT NULL,
    `maxupload`     INT(10)             NOT NULL DEFAULT '1048576',
    `format_time`   VARCHAR(100)        NOT NULL DEFAULT 'd-M-Y H:i:s',
    `permite_anexo` TINYINT(1)          NOT NULL DEFAULT '0',
    `file_mode`     VARCHAR(4)          NOT NULL DEFAULT '0774',
    `veri_mailok`   TINYINT(1)          NOT NULL DEFAULT '1',
    `allow_html`    TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `tipo_editor`   VARCHAR(10),
    `usa_perf`      TINYINT(1)          NOT NULL DEFAULT '0'
)
    ENGINE = ISAM;

#
# Table structure for table 'xoops_xmail_send_log'
#

CREATE TABLE `xmail_send_log` (
    `id_user`    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `id_men`     INT(5) UNSIGNED       NOT NULL DEFAULT '0',
    `dt_envio`   INT(11)               NOT NULL DEFAULT '0',
    `email_conf` VARCHAR(60)           NOT NULL DEFAULT '',
    KEY `id_user` (`id_user`),
    KEY `id_men` (`id_men`),
    KEY `dt_envio` (`dt_envio`)
)
    ENGINE = ISAM;


# MySQL-Front Dump 2.5
#
# Host: localhost   Database: xoops
# --------------------------------------------------------
# Server version 4.1.7-nt


#
# Table structure for table 'xoops_xmail_ativacao'
#


CREATE TABLE xmail_ativacao (
    id_user         SMALLINT(8) UNSIGNED NOT NULL DEFAULT '0',
    dt_envio        INT(11) UNSIGNED              DEFAULT NULL,
    user_logado     MEDIUMINT(8) UNSIGNED         DEFAULT NULL,
    activation_type TINYINT(1) UNSIGNED           DEFAULT NULL,
    KEY `id_user` (`id_user`)
)
    ENGINE = ISAM;

#
# Table structure for table 'xoops_xmail_aux_send'
#

CREATE TABLE xmail_aux_send (
    id_user      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'id do usuário',
    lote_solicit INT(11)               NOT NULL DEFAULT '0' COMMENT 'nro. lote de solicitação de ',
    UNIQUE KEY lote (lote_solicit, id_user)
)
    ENGINE = ISAM;


#
# Table structure for table 'xoops_xmail_aux_send_l'
#

CREATE TABLE xmail_aux_send_l (
    lote_solicit   INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'nro. lote de solicitação',
    id_men         INT(5)           NOT NULL DEFAULT '0' COMMENT 'id da mensagem',
    user_logado    MEDIUMINT(11)             DEFAULT NULL COMMENT 'usuário que solicitou envios',
    dt_solicit     INT(11)                   DEFAULT NULL COMMENT 'data da solicitação',
    email_conf     VARCHAR(60)               DEFAULT NULL COMMENT 'email de confirmação de recebimento',
    mail_fromname  VARCHAR(60)               DEFAULT NULL COMMENT 'nome do remetente das mensagens',
    mail_fromemail VARCHAR(60)               DEFAULT NULL COMMENT 'email do remetente das mensagens',
    mail_send_to   VARCHAR(20)               DEFAULT NULL COMMENT '(mail, pm ,pref)',
    is_new         TINYINT(1)                DEFAULT 0,
    UNIQUE KEY lote_solicit (lote_solicit),
    UNIQUE KEY user_logado (user_logado, lote_solicit)
)
    ENGINE = ISAM;

#
# Table structure for table 'xoops_xmail_newsletter'
#

CREATE TABLE xmail_newsletter (
    user_id    INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_name  VARCHAR(60)              DEFAULT NULL,
    user_nick  VARCHAR(25)              DEFAULT NULL,
    user_email VARCHAR(60)     NOT NULL DEFAULT '0',
    user_conf  VARCHAR(120)             DEFAULT NULL,
    confirmed  TINYINT(1)               DEFAULT '0',
    user_time  DATETIME                 DEFAULT NULL,
    user_host  VARCHAR(120)             DEFAULT NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY luser_email (user_email)
)
    ENGINE = ISAM;


CREATE TABLE xmail_perfil_news (
    user_id INT(8) UNSIGNED NOT NULL DEFAULT '0',
    id_perf INT(5) UNSIGNED          DEFAULT NULL,
    KEY user_id (user_id)
)
    ENGINE = ISAM;



CREATE TABLE xmail_tabperfil (
    id_perf     INT(5) NOT NULL AUTO_INCREMENT,
    descri_perf VARCHAR(60) DEFAULT NULL,
    PRIMARY KEY (id_perf)
)
    ENGINE = ISAM;


INSERT INTO xmail_tabperfil
VALUES ('1', 'Geral');



