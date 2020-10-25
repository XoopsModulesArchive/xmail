<?php

define('_AM_XMAIL_NOTMEN', 'Não ha mensagens cadastradas ');
define('_AM_XMAIL_NOTMENAPROV', 'Não ha mensagens a serem aprovadas ');
define('_AM_XMAIL_TIT1', 'MENSAGENS CADASTRADAS ');
define('_AM_XMAIL_TIT2', 'MENSAGENS PARA SEREM APROVADAS ');
define('_AM_XMAIL_MESAGE', 'Mensagem');
define('_AM_XMAIL_OPT', 'Opções');
define('_AM_XMAIL_ALT', 'Alterar');
define('_AM_XMAIL_EXC', 'Remover');
define('_AM_XMAIL_APROV', 'Aprovar');
define('_AM_XMAIL_DESAPROV', 'Desaprovar');
define('_AM_XMAIL_TITULO', 'Título');
define('_AM_XMAIL_SUBJCT', 'Assunto');
define('_AM_XMAIL_IDMEN', 'Cíodigo');
define('_AM_XMAIL_USUCAD', 'Incluída por');
define('_AM_XMAIL_DATACAD', 'Data Cad.');
define('_AM_XMAIL_ULTENVIO', 'Último Envio');
define('_AM_XMAIL_NOTFOUND', 'Mensagem não localizada');
define('_AM_XMAIL_ERRORSAVINGDB', 'Ocorreu um erro: A base de dados não foi  atualizada devido a um erro.');
define('_AM_XMAIL_SAVEOK', 'Dados atualizados com sucesso ');
define('_AM_XMAIL_DELETEMAN', 'Exclui esta mensagem ?  ');
define('_AM_XMAIL_YES', 'SIM ');
define('_AM_XMAIL_NO', 'NÃO ');
define('_AM_XMAIL_ERRORPARAM', 'Erro no cadastro de parâmetros ');
define('_AM_XMAIL_ERRORPARAMINC', 'Erro na inclusão de registro do  cadastro de parâmetros ');
define('_AM_XMAIL_ERRORLOG', 'Erro no cadastro de log ');
define('_AM_XMAIL_LOGDELOK', 'Registros de log eliminados com sucesso  ');
define('_AM_XMAIL_FORMPARAM', 'Alteração de Parâmetros  ');
define('_AM_XMAIL_DIASEXC', 'Exluir mensagem, após x dias que fora enviada  ');
define('_AM_XMAIL_ENVIAXMAILS', 'Enviar mensagem de quantas em quantas ? ');

define('_AM_XMAIL_PARAM1', 'Alfabetica de Título');
define('_AM_XMAIL_PARAM2', 'Código da mensagem');
define('_AM_XMAIL_PARAM3', 'Data de Envio decrescente');
define('_AM_XMAIL_PARAM4', 'Data de Envio crescente');
define('_AM_XMAIL_ORDEMADMIN', 'Ordem para visualizar mensagens');
define('_AM_XMAIL_LIMITEPAGE', 'Limite de mensagens por página');

// versão 1.09

define('_AM_XMAIL_DIRUPLOAD', 'Diretório para upload de arquivos anexos <br> Será criado um subdiretório para cada visitante ');

define('_AM_XMAIL_PATHEXIST', 'Diretório existente !!');
define('_AM_XMAIL_PATHNOTEXIST', 'Diretório não existente - Verifique !!');
define('_AM_XMAIL_ALLOWMIMETYPES', 'Mimetypes permitidos ');

define('_AM_XMAIL_MAXUPLOAD', 'Tamanho MÁX do upload (KB) 1048576 = 1 Meg ');
define('_AM_XMAIL_FORMAT_TIME', 'Formato de data para exibição.<br> Vide função date do php para exemplos:');
define('_AM_XMAIL_PERMITE_ANEXO', 'Permite anexar arquivos ? ');
define('_AM_XMAIL_FILE_MODE', 'Configuração de Permissão de Upload de Arquivo');
define('_AM_XMAIL_VERI_MAILOK', 'Verificar perfil do visitante, se aceita receber email ');
define('_AM_XMAIL_ERRUPLOAD_MAX', 'Valor máximo para upload maior do que definido no php.ini');

// versão  2.0

define('_AM_XMAIL_NOTUSERDESATIVO', 'Não ha visitantes com conta desativada ');
define('_AM_XMAIL_ID', 'Id');
define('_AM_XMAIL_LOGIN', 'Login');
define('_AM_XMAIL_NOME', 'Nome');
define('_AM_XMAIL_QTDTENTAR', 'Tentou<br>Ativar');
define('_AM_XMAIL_ENVIAREMAIL', 'Enviar email');
define('_AM_XMAIL_EMAIL', 'Email');
define('_AM_XMAIL_SELUSER', 'Selecione os visitantes ');
define('_AM_XMAIL_ATIVAR', 'Ativar');
define('_AM_XMAIL_ALLOWHTML', 'Permite Editor Visual ?');
define('_AM_XMAIL_TIPOEDITOR', 'Selecione Editor Visual,<br>se desejar<br><i>As classes do editor devem estar no Kernel do Xoops</i>');

define('_AM_XMAIL_DBERROR', 'Ocorreu um erro de banco de dados. Os detalhes estão abaixo:<br>Resultado: %s<br>Query: %s');
define('_AM_XMAIL_USERREMOVED', 'visitante %s foi retirado da lista.');
define('_AM_XMAIL_TABLEOPT', 'Tabela %s foi otimizada');
define('_AM_XMAIL_ADMINMENUNEWS', 'Administração da Newsletter ');
define('_AM_XMAIL_REMOVEUSER', 'Detalhes dos Assinantes');
define('_AM_XMAIL_OPTIMDATAB', 'Otimizar BD');
define('_AM_XMAIL_NOTHINGINDB', 'Nada para mostrar');
define('_AM_XMAIL_CONFIRMED', 'Confirmado');
define('_AM_XMAIL_USERID', 'ID do visitante');
define('_AM_XMAIL_USERNAME', 'Nome do visitante');
define('_AM_XMAIL_NICKNAME', 'Apelido:');
define('_AM_XMAIL_HOST', 'IP');
define('_AM_XMAIL_TIME', 'Hora');
define('_AM_XMAIL_DELETEUSER', 'Delete');
define('_AM_XMAIL_USERSMSG1', '<BR>O visitante %s já é assinante do Boletim.');
define('_AM_XMAIL_USERSMSG2', '<BR>%s dados não foram importados, o campo email está vazio.');
define('_AM_XMAIL_USERSMSG3', '<BR><b>Aviso:</b> %s dados não foi importada. Motivo: visitante não deseja receber email.');
define('_AM_XMAIL_USERSMSG4', '<BR>User %s dados importados com sucesso ');
define('_AM_XMAIL_USERSMSG5', '<BR>Erro ao incluir visitante %s');
define('_AM_XMAIL_IMPORTUSER', 'Importar visitantes para lista de assinantes ');
define('_AM_XMAIL_MSGIMPORTUSER', 'Selecione os visitantes para importação ');
define('_AM_XMAIL_BNTIMPORTUSEROK', 'Importar');
define('_AM_XMAIL_BNTIMPORTUSERCANCEL', 'Cancelar');
define('_AM_XMAIL_ERROR', 'ERRO');
define('_AM_XMAIL_ADMINMENUXMAIL', 'Administração Xmail ');

define('_AM_XMAIL_ADMENU1', 'Menu Principal');
define('_AM_XMAIL_ADMENU2', 'Aprovar mensagens');
define('_AM_XMAIL_ADMENU3', 'Ver log de envio');
define('_AM_XMAIL_ADMENU4', 'Gerênciar Ativação');
define('_AM_XMAIL_ADMENU5', 'Alterar Parâmetros');
define('_AM_XMAIL_ADMENU6', 'Gerênciar Newsletter');
define('_AM_XMAIL_ADMENU7', 'Gerênciar Tabela de Perfis');

define('_AM_XMAIL_ADMPERUSER', 'Administrar tabela de Perfis de visitantes');
define('_AM_XMAIL_ERRBUSCA', 'Erro na localização do registro ');
define('_AM_XMAIL_DESCRIPERF', 'Descrição do Perfil');
define('_AM_XMAIL_INCLUSAO', 'Inclusão');
define('_AM_XMAIL_ALTERACAO', 'Alteração');
define('_AM_XMAIL_CONFDELUSER', 'Confirma eliminar o assinante  %s da lista ?');
define('_AM_XMAIL_USAPERF', 'Deseja usar esquema de perfis  na newsletter ?');
