<?php
/*
* $Id: gerencia_lote.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include 'header.php';
require_once XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';

$op = '';

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

foreach ($_GET as $k => $v) {
    ${$k} = $v;
}

if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
if (isset($_POST['op'])) {
    $op = $_POST['op'];
}

if (isset($_POST['post'])) {
    $op = 'post';
}
if (isset($_POST['upload'])) {
    $op = 'upload';
}

$param = new classparam();
if (!$param->busca()) {
    redirect_header('index.php', 2, _MD_XMAIL_ERRORPARAM);
}
if (0 == $param->totreg) {
    redirect_header('index.php', 2, _MD_XMAIL_NOTPARAM);
}

// Global $xoopsUser, $xoopsConfig, $xoopsDB ;

switch ($op) {
    case 'exc_exec':  // executar excluir lote
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/class_aux_send.php';
        $class_lotef = new classxmail_aux_send();
        $class_lotef->lote_solicit = $lote;
        $class_lotep = new classxmail_aux_send_l();
        $class_lotep->lote_solicit = $lote;

        if (!$isadmin) {
            $class_lotep->busca();

            if ($class_lotep->user_logado != $xoopsUser->getvar('uid')) {
                redirect_header(XOOPS_URL . '/modules/xmail/', '1', sprintf(_MD_XMAIL_LOTENOTUSER, $class_lotep->user_logado));
            }
        }

        if (!$class_lotef->excluir(1)) {
            redirect_header('gerencia_lote.php', 2, _MD_XMAIL_OPERNOTOK . ' ' . $men_erro);
        } else {
            redirect_header('gerencia_lote.php', 2, _MD_XMAIL_SAVEOK);
        }
        break;
    case 'exc':  // delete lote
        $result = $xoopsDB->query('SELECT  * FROM ' . $xoopsDB->prefix('xmail_aux_send_l') . " WHERE lote_solicit = $lote");
        if ($result) {
            $cat_data = $xoopsDB->fetchArray($result);

            echo "<table width='60%' border='0' cellpadding = '2' cellspacing='1' class = 'confirmMsg'>
             <tr><td class='confirmMsg'>";

            echo "<div class='confirmMsg'>";

            echo '<h4>';

            echo '' . _MD_XMAIL_CONFEXCLOTE . "</h4>$lote<br><br>";

            echo "<table><tr><td align='right'   >";

            echo myTextForm2('gerencia_lote.php?op=exc_exec&lote=' . $lote, _MD_XMAIL_YES);

            echo '</td><td>';

            echo myTextForm2('gerencia_lote.php?op=default', _MD_XMAIL_NO);

            echo '</td></tr></table>';

            echo '</div><br><br>';

            echo '</td></tr></table>';
        } else {
            redirect_header('gerencia_lote.php', 2, _MD_XMAIL_LOTENOTFOUND . ' ' . $lote);
        }
        break;
    case 'send':  // enviar mensagens do lote
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/class_aux_send.php';
        if (!isset($mail_start)) {
            $mail_start = 0;
        }

        if (!isset($dest)) {
            $dest = '';
        }

        envia_xmails_lote($lote, $dest, $mail_start);
        break;
    //case "default":

    default:     // show all stored messages
        $sql = 'SELECT pai.*,users.uname as solicitante  ,mensage.* ,count(filho.id_user) as pendente FROM ' . $xoopsDB->prefix('xmail_aux_send_l') . ' as pai
    left join  ' . $xoopsDB->prefix('xmail_aux_send') . ' as filho on pai.lote_solicit=filho.lote_solicit
    left join  ' . $xoopsDB->prefix('xmail_mensage') . ' as mensage  on pai.id_men=mensage.id_men
    left join  ' . $xoopsDB->prefix('users') . ' as users  on pai.user_logado = users.uid ';

        if (!$isadmin) {
            $sql .= ' where pai.user_logado= ' . $xoopsUser->getvar('uid');
        }

        $sql .= ' group by filho.lote_solicit ';

        if (empty($ordem)) {
            $ordem = 'D';
        }

        if ('D' == $ordem) {   // data de solicitação
            $sql .= ' ORDER BY dt_solicit ';
        } elseif ('U' == $ordem) {     // visitante
            $sql .= ' ORDER BY user_logado  ';
        }

        $result = $xoopsDB->queryF($sql);
        if (!$result) {
            echo "ERR $sql";

            break;
        }
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header(XOOPS_URL . '/modules/xmail/', '1', _MD_XMAIL_NOTLOTEPEN);
        }

        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $men_p_page = $param->limite_page;
        $userstart = isset($_GET['userstart']) ? (int)$_GET['userstart'] : 0;
        $userfim = $userstart + $men_p_page;

        $usercount = $GLOBALS['xoopsDB']->getRowsNum($result);
        $nav = new XoopsPageNav($usercount, $men_p_page, $userstart, 'userstart', 'op=default&ordem=' . $ordem);

        $xoopsTpl->assign(['titulo' => _MD_XMAIL_TIT3]);

        $GLOBALS['xoopsOption']['template_main'] = 'xmail_lote.htm';
        $topics = [];

        $count = 0;
        while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
            if ($count >= $userstart and $count < $userfim) {
                $topics['body_men'] = $myts->displayTarea($cat_data['body_men']);

                $topics['id_men'] = $cat_data['id_men'];

                //	        	$topics['datesub']= formatTimestamp($cat_data['datesub'],"d-M-Y");

                $topics['datesub'] = formatTimestamp($cat_data['datesub'], $param->format_time);

                $topics['subject_men'] = $cat_data['subject_men'];

                // only works for XOOPS-JP 2.0.5

                $topics['poster'] = XoopsUserUtility::getUnameFromId($cat_data['uid']);

                //    			$topics['poster'] = $cat_data['uid'];  // p/ versões anteriores

                //    			uncomment the line above for earlier versions

                $topics['title_men'] = $cat_data['title_men'];

                $topics['date_envio'] = (!empty($cat_data['date_envio'])) ? formatTimestamp($cat_data['date_envio'], $param->format_time) : '';

                // check for attachment files

                $classfiles = new classfiles();

                $arqs = $classfiles->array_anexos($cat_data['id_men']);

                $topics['anexos'] = '';

                for ($i = 0, $iMax = count($arqs); $i < $iMax; $i++) {
                    if ($i > 0) {
                        $topics['anexos'] .= ' , ';
                    }

                    $topics['anexos'] .= '   ' . ($arqs[$i]['filerealname']);
                }

                $xoopsTpl->append(
                    'topics',
                    [
                        'id_men' => $cat_data['id_men'],
                        'body_men' => $topics['body_men'],
                        'datesub' => $topics['datesub'],
                        'poster' => $topics['poster'],
                        'subject_men' => $topics['subject_men'],
                        'title_men' => $topics['title_men'],
                        'date_envio' => $topics['date_envio'],
                        'aprov' => $topics['aprov'],
                        'anexos' => $topics['anexos'],
                        'lote_solicit' => $cat_data['lote_solicit'],
                        'dt_solicit' => formatTimestamp($cat_data['dt_solicit'], $param->format_time),
                        'user_logado' => $cat_data['solicitante'],
                        'qtd_pendente' => $cat_data['pendente'],
                        'link_user_logado' => XoopsUserUtility::getUnameFromId($cat_data['user_logado']),
                    ]
                );
            }

            $count++;
        }
        $xoopsTpl->assign(['opt' => _MD_XMAIL_OPT, 'alt' => _MD_XMAIL_ALT, 'exc' => _MD_XMAIL_EXC, 'aprov' => _MD_XMAIL_APROVAR, 'desaprov' => _MD_XMAIL_DESAPROV, 'mensagem' => _MD_XMAIL_MESAGE]);
        $xoopsTpl->assign(['title_men' => _MD_XMAIL_TITULO, 'subject' => _MD_XMAIL_SUBJECT, 'codigo' => _MD_XMAIL_IDMEN, 'usucad' => _MD_XMAIL_USUCAD, 'datacad' => _MD_XMAIL_DATACAD]);
        $xoopsTpl->assign(['ultenvio' => _MD_XMAIL_ULTENVIO]);
        $xoopsTpl->assign(['limite' => _MD_XMAIL_LIMITE, 'ordem' => _MD_XMAIL_ORDEM, 'dtnova' => _MD_XMAIL_DTNOVA, 'dtantiga' => _MD_XMAIL_DTANTIGA, 'enviar' => _MD_XMAIL_SUBMIT]);
        $xoopsTpl->assign(['actionform' => xoops_getenv('PHP_SELF')]);
        $xoopsTpl->assign(['anexos' => _MD_XMAIL_ANEXOS]);
        $xoopsTpl->assign(['data_lote' => _MD_XMAIL_DATE]);
        $xoopsTpl->assign(['solicitante' => _MD_XMAIL_SOLICIT]);
        $xoopsTpl->assign(['pendente' => _MD_XMAIL_PENDENTE]);
        $xoopsTpl->assign(['lote' => _MD_XMAIL_LOTE]);

        if ('apr' == $op) {
            $xoopsTpl->assign(['comform' => 'N']);
        } else {
            $xoopsTpl->assign(['comform' => 'S']);
        }

        $xoopsTpl->assign(['navega' => $nav->renderNav(4)]);

        break;
}

require XOOPS_ROOT_PATH . '/footer.php';


