<?php

/*
* $Id: admin/gerencia_ativa.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/
$xoopsOption['pagetype'] = 'user';
include 'admin_header.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/functions.php';
xoops_cp_header();

$configHandler = xoops_getHandler('config');
$xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

//$op ='';
//
//foreach ($_POST as $k => $v) {
//	${$k} = $v;
//}
//
//foreach ($_GET as $k => $v) {
//	${$k} = $v;
//}
//

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

$mat_id = $_POST['mat_id'];  // matriz com id dos visitantes selecionados
$userstart = $_GET['userstart'];

$param = new classparam();
if (!$param->busca()) {
    redirect_header('index.php', 2, _MD_XMAIL_ERRORPARAM);
}
if (0 == $param->totreg) {
    redirect_header('index.php', 2, _MD_XMAIL_NOTPARAM);
}

// Global $xoopsUser, $xoopsConfig, $xoopsDB ;

switch ($op) {
    case 'exc_exec':  // executar excluir visitante

        break;
    case 'exc':  // delete visitante

        break;
    case 'send':  // enviar email de ativaçào
        // usar função  envia_email_ativa......  após testa-la no xmail_ativa.php

        // instanciar xoopsuser para os id passados na matriz $mat_id
        $added = [];
        foreach ($mat_id as $to_user) {
            $added[] = new XoopsUser($to_user);
        }
        if (0 == count($added)) {
            redirect_header(xoops_getenv('PHP_SELF'), 2, _AM_XMAIL_SELUSER);
        }
        envia_email_ativa($added, $xoopsUser->getVar('uid'), $xoopsConfigUser, $xoopsConfig);

        break;
    case 'default':

    default:     // mostrar todos visitantes sem ativação
        //   alterar abaixo   adaptando

        $sql = 'select count(ativa.id_user) as tentativas ,users.uid,uname,name,email,
         user_regdate as data_cad ,user_from,user_occ
         from ' . $xoopsDB->prefix('users') . ' as users
         left join  ' . $xoopsDB->prefix('xmail_ativacao') . ' as ativa on users.uid=ativa.id_user
         where users.level=0 group by users.uid order by user_regdate ';

        $result = $xoopsDB->queryF($sql);
        if (!$result) {
            echo "ERR $sql";

            break;
        }
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            xoops_result(_AM_XMAIL_NOTUSERDESATIVO);
        } else {
            require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

            $men_p_page = $param->limite_page;

            $userstart = isset($_GET['userstart']) ? (int)$_GET['userstart'] : 0;

            $userfim = $userstart + $men_p_page;

            $usercount = $GLOBALS['xoopsDB']->getRowsNum($result);

            $nav = new XoopsPageNav($usercount, $men_p_page, $userstart, 'userstart', 'op=default');

            echo "<form name='ativa' method='post' action='" . xoops_getenv('PHP_SELF') . "?op=send'>\n";

            echo "<table border=1 cellpadding=0 cellspacing=0 width='100%' >\n";

            echo "	<tr  class='Head' >\n";

            if (0 == $xoopsConfigUser['activation_type']) {
                echo "		<td  align='center'   >  </td>\n";
            }

            echo '		<td><b>' . _AM_XMAIL_ID . " </b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_LOGIN . " </b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_NOME . " </b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_DATACAD . "</b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_QTDTENTAR . "</b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_EMAIL . '/' . _US_WEBSITE . "</b></td>\n";

            echo '		<td><b>' . _US_LOCATION . '<br>/' . _US_OCCUPATION . "</b></td>\n";

            echo '		<td><b>' . _AM_XMAIL_OPT . "</b></td>\n";

            echo "	</tr>\n";

            $i = 0;

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                if (0 == ($i % 2)) {
                    echo " <tr class='even' >";
                } else {
                    echo " <tr class='odd' >";
                }

                if ($i >= $userstart and $i < $userfim) {
                    if (0 == $xoopsConfigUser['activation_type']) {
                        echo "<td align='center'  >
                    <input type='checkbox' name='mat_id[]' value='" . $cat_data['uid'] . "' ></td>\n";
                    }

                    echo '<td   >' . $cat_data['uid'] . "</td>\n";

                    echo '<td   >' . $cat_data['uname'] . "</td>\n";

                    echo '<td>' . $cat_data['name'] . "</td>\n";

                    echo '<td>' . formatTimestamp($cat_data['data_cad'], $param->format_time) . "</td>\n";

                    echo '<td>' . $cat_data['tentativas'] . "</td>\n";

                    echo '<td>' . $cat_data['email'] . "\n";

                    echo '<br>' . $cat_data['url'] . "</td>\n";

                    echo '<td>' . $cat_data['user_from'] . " / \n";

                    echo '<br>' . $cat_data['user_occ'] . "</td>\n";

                    echo "<td>
                <a href='" . XOOPS_URL . '/modules/system/admin.php?fct=users&op=delUser&uid=' . $cat_data['uid'] . "'>" . _AM_XMAIL_EXC . ' </a>';

                    if (0 != $xoopsConfigUser['activation_type']) {
                        echo "<br><a href='" . XOOPS_URL . '/modules/system/admin.php?fct=users&op=modifyUser&uid=' . $cat_data['uid'] . "'>" . _AM_XMAIL_ATIVAR . " </a></td>\n";
                    }

                    echo "	</tr>\n";
                }

                $i++;
            }

            echo "</table>\n";

            echo "</form> \n";

            if (0 == $xoopsConfigUser['activation_type']) {
                echo "<p align='center' ><a  href=\"javascript:document.ativa.submit();\">" . _AM_XMAIL_ENVIAREMAIL . ' </a></p>';
            }

            echo "<p align='center' >" . $nav->renderNav(4) . '</p>';
        }
        break;
}
xoops_cp_footer();



