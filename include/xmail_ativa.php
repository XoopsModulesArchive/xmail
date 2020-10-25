<?php
/*
* $Id: include/xmail_ativa.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

//  Este script não deve ser colocado no diretório raiz do xmail ,pois senão anônimos não poderão acessar
//  pois o módulo xmail não deve ser acessado por anônimos

//require_once dirname(__DIR__, 3) . '/mainfile.php';
//require_once  XOOPS_ROOT_PATH . "/header.php" ;
//    // carregar arquivos de tradução
//   	if ( file_exists(XOOPS_ROOT_PATH."/modules/xmail/language/".$xoopsConfig['language']."/main.php") ) {
//		require_once XOOPS_ROOT_PATH."/modules/xmail/language/".$xoopsConfig['language']."/main.php";
//	} else {
//		if ( file_exists(XOOPS_ROOT_PATH."/modules/xmail/language/english/main.php") ) {
//			require_once XOOPS_ROOT_PATH."/modules/xmail/language/english/main.php";
//		}
//	}
//
//

$conf_id = $_GET['id'];
if (empty($conf_id) or null === $conf_id) {  // indica ser ativação de conta
    $xoopsOption['pagetype'] = 'user';

    require_once dirname(__DIR__, 3) . '/mainfile.php';

    require_once XOOPS_ROOT_PATH . '/header.php';

    require_once XOOPS_ROOT_PATH . '/modules/xmail/include/functions.php';

    $myts = MyTextSanitizer::getInstance();

    $configHandler = xoops_getHandler('config');

    $xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

    // carregar arquivos de tradução

    if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php')) {
        require_once XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php';
    } else {
        if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php')) {
            require_once XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php';
        }
    }

    $email = isset($_GET['email']) ? trim($_GET['email']) : '';

    $email = isset($_POST['email']) ? trim($_POST['email']) : $email;

    // Se $email for vazio, mostra o form para envio do link

    if ('' == $email) {
        require XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

        if (0 == $xoopsConfigUser['activation_type']) {
            $label = _MD_XMAIL_EMAILATIVA;
        } else {
            $label = _MD_XMAIL_EMAILATIVA2;
        }

        $sform = new XoopsThemeForm(_MD_XMAIL_FORMATIVA, 'storyform', xoops_getenv('PHP_SELF'));

        $sform->addElement(new XoopsFormText($label, 'email', 26, 60, ''), true);

        $sform->addElement(new XoopsFormButton('', 'post', _MD_XMAIL_SUBMIT, 'submit'));

        $sform->display();

    // Se $email não for vazio , vamos fazer uma série de verificações antes de enviar
    } else {
        $myts = MyTextSanitizer::getInstance();

        $memberHandler = xoops_getHandler('member');

        // A linha abaixo retornará um array com todos os visitantes que tenham o e-mail citado, no nosso caso será apenas $getuser[0]

        $getuser = $memberHandler->getUsers(new Criteria('email', $myts->addSlashes($email)));

        // Se o e-mail não existir na base de dados, $getuser será vazio...

        if (empty($getuser)) {
            xoops_error(_MD_XMAIL_EMAILNOTCAD);

            require XOOPS_ROOT_PATH . '/footer.php';

            exit();
        }

        //Verificando se o visitante já está ativo...

        if ($getuser[0]->isActive()) {
            xoops_error(sprintf(_MD_XMAIL_EMAILJACAD, $getuser[0]->getVar('uname'), $getuser[0]->getVar('email')));

            require XOOPS_ROOT_PATH . '/footer.php';

            exit();
        }

        // enviando email

        envia_email_ativa($getuser[0], 0, $xoopsConfigUser, $xoopsConfig);
    }

    require XOOPS_ROOT_PATH . '/footer.php';
} else {
    require_once dirname(__DIR__, 3) . '/mainfile.php';

    require_once XOOPS_ROOT_PATH . '/header.php';

    $GLOBALS['xoopsOption']['template_main'] = 'xmail_notice.html';

    require XOOPS_ROOT_PATH . '/header.php';    // Include the page header

    // carregar arquivos de tradução

    if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php')) {
        require_once XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php';
    } else {
        if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php')) {
            require_once XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php';
        }
    }

    $xoopsTpl->assign('lang_status', _MD_XMAIL_STATUS);

    $conf_id = $_GET['id'];

    $query = 'select * from ' . $xoopsDB->prefix('xmail_newsletter') . " where user_conf='$conf_id'";

    $result = $xoopsDB->query($query);

    $arr = $xoopsDB->fetchArray($result);

    if ('1' == $arr['confirmed']) {
        $xoopsTpl->assign('en_message', ucfirst($arr['user_name']) . ', ' . _MD_XMAIL_PREVCONFIRM);
    } else {
        $query = 'update ' . $xoopsDB->prefix('xmail_newsletter') . " SET confirmed='1' where user_conf='$conf_id'";

        $result = $xoopsDB->queryF($query);

        $xoopsTpl->assign('en_message', ucfirst($arr['user_name']) . ', ' . _MD_XMAIL_CONFIRMASUCCESS . _MD_XMAIL_CONFIRMATION_NUMBER . $conf_id);
    }

    // Include the page footer

    require XOOPS_ROOT_PATH . '/footer.php';
}
