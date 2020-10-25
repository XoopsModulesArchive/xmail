<?php
/*
* $Id: include/xnews.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

// funções adaptadas do módulo evennews

require_once dirname(__DIR__, 3) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';
$xoopsModule = XoopsModule::getByDirname('xmail');
$myts = MyTextSanitizer::getInstance();
$dirname = $xoopsModule->dirname();

// carregar arquivos de tradução
if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php')) {
    require_once XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/main.php';
} else {
    if (file_exists(XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php')) {
        require_once XOOPS_ROOT_PATH . '/modules/xmail/language/english/main.php';
    }
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}

require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classparam.php';
$param = new classparam();
if (!$param->busca()) {
    $usa_perf = 0;
}
if (0 == $param->totreg) {
    $usa_perf = 0;
}

$usa_perf = $param->usa_perf;

//determine proper smarty template for page
switch ($action) {
    case 'subscribe_conf':
    case 'unsubscribe_conf':
        $GLOBALS['xoopsOption']['template_main'] = 'xmail_notice.html';
        break;
    case 'subscribe':
        $GLOBALS['xoopsOption']['template_main'] = 'xmail_subscr.html';
        break;
    case 'unsubscribe':
        $GLOBALS['xoopsOption']['template_main'] = 'xmail_unsub.html';
        break;
    default:
        $GLOBALS['xoopsOption']['template_main'] = 'xmail_index.html';
        break;
}

require_once XOOPS_ROOT_PATH . '/header.php';    // Include the page header

$xoopsTpl->assign('lang_status', _MD_XMAIL_STATUS);

//Fill smarty variables for each page
switch ($action) {
    case 'subscribe_conf':
        if ('' == $_POST['user_mail']) {
            $xoopsTpl->assign('en_message', _MD_XMAIL_GENERROR);

            break;
        }

        $ret = addUser(); // Try to add user
        $men_erro = '';
        //Display Appropriate Response to addUser Return value
        switch ($ret) {
            case 1:        //User Added, Confirmation Sent
                $xoopsTpl->assign('en_message', sprintf(_MD_XMAIL_CONFIRM, $_POST['user_mail']) . $men_erro);
                break;
            case 2:    //Resending confirmation
                $xoopsTpl->assign('en_message', sprintf(_MD_XMAIL_RESENDCONFIRM, $_POST['user_mail']));
                break;
            case -1:    //Email Already Exists
                $xoopsTpl->assign('en_message', _MD_XMAIL_EMAILEXISTS);
                break;
            case -2:  //Unable to send mail
                $xoopsTpl->assign('en_message', _MD_XMAIL_EMAILERROR);
                break;
        }
        break;
    case 'unsubscribe_conf':
        if ('' == $_POST['user_mail']) {
            $xoopsTpl->assign('en_message', _MD_XMAIL_GENERROR);

            break;
        }
        $ret = delUser();  // Try to del user
        switch ($ret) {
            case 1:        //User Removed
                $xoopsTpl->assign('en_message', sprintf(_MD_XMAIL_REMOVED, $_POST['user_mail']));
                break;
            case 0:   //User Unsubscribed, or unconfirmed
            case -1:
                $xoopsTpl->assign('en_message', _MD_XMAIL_REMERROR);
                break;
        }
        break;
    case 'subscribe':
        $nomeclass = 'classxmail_tabperfil';
        require XOOPS_ROOT_PATH . '/modules/xmail/include/' . "{$nomeclass}.php";
        $classperf = new $nomeclass();

        $xoopsTpl->assign('en_title', sprintf(_MD_XMAIL_SUBTITLE, $xoopsConfig['sitename']));
        $xoopsTpl->assign('en_form_action', xoops_getenv('PHP_SELF'));
        $xoopsTpl->assign('EN_DISCLAIMER', sprintf(_MD_XMAIL_DISCLAIMER, $xoopsConfig['sitename'], $xoopsConfig['sitename']));
        $xoopsTpl->assign('en_remote_host', $_SERVER['REMOTE_HOST']);
        $xoopsTpl->assign('lang_emailadress', _MD_XMAIL_EMAIL_ADRESS);
        $xoopsTpl->assign('lang_denote', _MD_XMAIL_DENOTE);
        $xoopsTpl->assign('lang_nickname', _MD_XMAIL_NICKNAME);
        $xoopsTpl->assign('lang_name', _MD_XMAIL_NAME);
        $xoopsTpl->assign('lang_email', _MD_XMAIL_EMAIL_ADRESS);
        $xoopsTpl->assign('lang_submit_button', _MD_XMAIL_SUBMITBTN);
        $xoopsTpl->assign('lang_enter_name', _MD_XMAIL_JS_ERROR1);
        $xoopsTpl->assign('lang_enter_surname', _MD_XMAIL_JS_ERROR2);
        $xoopsTpl->assign('lang_enter_email', _MD_XMAIL_JS_ERROR3);
        $xoopsTpl->assign('lang_enter_fields', _MD_XMAIL_JS_ERROR4);

        if ($xoopsUser) {
            $xoopsTpl->assign('en_realname', $xoopsUser->getVar('name'));

            $xoopsTpl->assign('en_username', $xoopsUser->getVar('uname'));

            $xoopsTpl->assign('en_email', $xoopsUser->getVar('email'));

        //            $perf_sel=$classperf->get_user_perf($classperf->get_id_from_email($xoopsUser->getVar('email')));
        } else {
            $xoopsTpl->assign('en_realname', _MD_XMAIL_JS_ERROR1);

            $xoopsTpl->assign('en_username', _MD_XMAIL_JS_ERROR5);

            $xoopsTpl->assign('en_email', _MD_XMAIL_JS_ERROR3);
        }
        if ($usa_perf) {
            $tab_perf = $classperf->get_tab_perf();

            $xoopsTpl->assign('usa_perf', $usa_perf);

            $xoopsTpl->assign('tab_perf', $tab_perf);

            $xoopsTpl->assign('perf_sel', $perf_sel);

            $xoopsTpl->assign('infoperf', _MD_XMAIL_INFOPERF);
        }

        break;
    case 'unsubscribe':
        $xoopsTpl->assign('en_title', sprintf(_MD_XMAIL_UNSUBTITLE, $xoopsConfig['sitename']));
        $xoopsTpl->assign('en_form_action', xoops_getenv('PHP_SELF'));
        $xoopsTpl->assign('lang_unsubscribe', _MD_XMAIL_BTNUNSUBSCRIBE);
        $xoopsTpl->assign('lang_emailadress', _MD_XMAIL_EMAIL_ADRESS);

        if ($xoopsUser) {
            $xoopsTpl->assign('en_email', $xoopsUser->getVar('email'));
        } else {
            $xoopsTpl->assign('en_email', _MD_XMAIL_ERROR1);
        }
        break;
    default:
        $xoopsTpl->assign('lang_tooltip1', _MD_XMAIL_TOOLTIP1);
        $xoopsTpl->assign('lang_tooltip2', _MD_XMAIL_TOOLTIP2);
        $xoopsTpl->assign('module_name', $xoopsModule->getVar('name'));
        $xoopsTpl->assign('subscr_url', xoops_getenv('PHP_SELF') . '?action=subscribe');
        $xoopsTpl->assign('unsubscr_url', xoops_getenv('PHP_SELF') . '?action=unsubscribe');
        $xoopsTpl->assign('news_images', sprintf('%s/modules/%s/language/%s/', XOOPS_URL, $dirname, $xoopsConfig['language']));
        break;
}
// Include the page footer
require_once XOOPS_ROOT_PATH . '/footer.php';

/* ------------------------------------------------------------
function delUser() - Removes a user from the newsletter by marking
them unconfirmed.
-------------------------------------------------------------*/
function delUser()
{
    global $xoopsDB, $myts, $xoopsConfig, $xoopsModule;

    $query = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_newsletter') . " WHERE user_email='" . $myts->addSlashes($_POST['user_mail']) . "' ";

    $result = $xoopsDB->query($query);

    $myarray = $xoopsDB->fetchArray($result);

    $mymail = $myts->addSlashes($_POST['user_mail']);

    if ($myarray) {
        if ('0' == $myarray['confirmed']) {
            return -1;
        }

        $query = 'UPDATE ' . $xoopsDB->prefix('xmail_newsletter') . " SET confirmed='0' WHERE user_email='$mymail'";

        $result = $xoopsDB->queryF($query);

        return 1;
    }
  

    return -2;
}

/* ------------------------------------------------------------
function addUser() - Adds a user to db and sends confirm email
-------------------------------------------------------------*/
function addUser()
{
    global $xoopsDB, $myts, $xoopsConfig, $xoopsModule, $dirname, $men_erro, $usa_perf;

    $user_name = $myts->addSlashes($_POST['user_name']);

    $user_nick = $myts->addSlashes($_POST['user_nick']);

    $user_mail = $myts->addSlashes($_POST['user_mail']);

    //	$user_host = $myts->addSlashes($_SERVER['REMOTE_HOST']);

    $user_host = $myts->addSlashes(gethostbyaddr($_SERVER['REMOTE_HOST']));

    $query = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_newsletter') . " WHERE user_email='$user_mail' ";

    $myarray = $xoopsDB->fetchArray($xoopsDB->query($query));

    $xoopsMailer = getMailer();

    $xoopsMailer->useMail();

    // Hervé

    $xoopsMailer->setTemplateDir(XOOPS_ROOT_PATH . '/modules/' . $dirname . '/language/' . $xoopsConfig['language'] . '/mail_template');

    if ($usa_perf) {
        $xoopsMailer->setTemplate('confirm_email_perf.tpl');
    } else {
        $xoopsMailer->setTemplate('confirm_email.tpl');
    }

    if (!$myarray) {    // New User
        $better_token = md5(uniqid(mt_rand(), 1));

        $query = 'INSERT INTO ' . $xoopsDB->prefix('xmail_newsletter') . ' (user_id, user_name, user_nick, user_email, user_host, user_conf, user_time) ';

        $query .= "VALUES (0, '$user_name', '$user_nick', '$user_mail', '$user_host', '$better_token', NOW())";

        $result = $xoopsDB->queryF($query);

        if (!$result) {
            echo "erro na inclusão $query ";

            return;
        }

        // incluir  em xmail_perfil_news $_POST['id_perf']

        // pegar user_id incluso

        $user_id = $xoopsDB->getInsertId();

        $lista_perfil = implode(',', $_POST['id_perf']);

        while (list($chave, $valor) = each($_POST['id_perf'])) {
            //             echo " chave: $chave => valor : $valor<br>";

            //               $perfil_definido.="$valor - ";

            $sql = ' insert into ' . $xoopsDB->prefix('xmail_perfil_news') . " (user_id,id_perf)  values ($user_id,$valor)  ";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                $men_erro .= '<br>' . _MD_XMAIL_ERRCADPERF . " - ($valor)";
            }
        }

        if (empty($lista_perfil)) {
            $perfil_definido = _MD_XMAIL_PERFNAODEF;
        } else {
            $nomeclass = 'classxmail_tabperfil';

            require XOOPS_ROOT_PATH . '/modules/xmail/include/' . "{$nomeclass}.php";

            $classperf = new $nomeclass();

            $perfil_definido = $classperf->get_lista_perf($lista_perfil);
        }

        $confirm_url = XOOPS_URL . '/modules/xmail/include/xmail_ativa.php?id=' . $better_token;

        $xoopsMailer->setToEmails($_POST['user_mail']);

        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);

        $xoopsMailer->setFromName($xoopsConfig['sitename']);

        $xoopsMailer->setSubject(_MD_XMAIL_CONFIRM_SUBJECT);

        $xoopsMailer->assign('X_UNAME', $user_name);

        $xoopsMailer->assign('X_CONTACT_NAME', $xoopsConfig['adminmail']);

        $xoopsMailer->assign('VALIDATION_URL', $confirm_url);

        $xoopsMailer->assign('PERFIL_DEF', $perfil_definido);

        if ($xoopsMailer->send()) {
            return 1;
        }
  

        return -2;
    } elseif ('0' == $myarray['confirmed']) {    //Still Needs Confirmation
        $confirm_url = XOOPS_URL . '/modules/xmail/include/xmail_ativa.php?id=' . $myarray['user_conf'];

        $xoopsMailer->setToEmails($myarray['user_email']);

        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);

        $xoopsMailer->setFromName($xoopsConfig['sitename']);

        $xoopsMailer->setSubject(_MD_XMAIL_CONFIRM_SUBJECT);

        $xoopsMailer->assign('X_UNAME', $user_name);

        $xoopsMailer->assign('X_CONTACT_NAME', $xoopsConfig['adminmail']);

        $xoopsMailer->assign('VALIDATION_URL', $confirm_url);

        $xoopsMailer->assign('PERFIL_DEF', $perfil_definido);

        if ($xoopsMailer->send()) {
            return 2;
        }
  

        return -2;
    }    // Duplicate Email in db

    return -1;
}
