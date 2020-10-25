<?php

/*
* $Id: sendnews.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/
include 'header.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/class_aux_send.php';
global $xoopsUser, $xoopsDB, $xoopsConfig, $myts;

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

$PHP_SELF = $_SERVER['PHP_SELF'];

require_once XOOPS_ROOT_PATH . '/header.php';

?>
<script>
    function vermen(id_men) {

        openWithSelfMain('<?=XOOPS_URL?>/modules/xmail/vermen.php?id_men=' + id_men, "Info", 400, 350);

    }
</script>
<?php
set_time_limit(0);
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classparam.php';
$param = new classparam();
if (!$param->busca()) {
    $usa_perf = 0;
}
if (0 == $param->totreg) {
    $usa_perf = 0;
}
$usa_perf = $param->usa_perf;

$nomeclass = 'classxmail_tabperfil';
require XOOPS_ROOT_PATH . '/modules/xmail/include/' . "{$nomeclass}.php";
$classperf = new $nomeclass();

switch ($op) {
    case 'send':

        if (empty($dest)) {
            // quando não vazio indica que foi chamado pela func. envia_xmails  em continuação

            // $dest not being empty indicates that it was called by the envia_xmails function

            if ('send' == $op && !empty($_POST['mail_send_to'])) {
                $added_id = [];

                // resgatar os usuarios para newsletter

                $sql = 'select confirmed,perf.id_perf,news.user_id from ' . $xoopsDB->prefix('xmail_newsletter') . ' as news' . ' left join ' . $xoopsDB->prefix('xmail_perfil_news') . ' as perf on perf.user_id= news.user_id  ' . ' where confirmed=1 ';

                if (isset($_POST['perfil'])) {
                    $tabperf = implode(',', $_POST['perfil']);

                    $sql .= "  and  ( perf.id_perf in ($tabperf) ";

                    if (isset($_POST['sem_perf']) and 1 == $_POST['sem_perf']) {
                        $sql .= '  or isnull(perf.id_perf)';
                    }

                    $sql .= ') ';
                }

                $sql .= ' group by news.user_id';

                echo $sql;

                $result = $xoopsDB->queryF($sql);

                if (!$result) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_ERRCADNEW . ' </div> ';

                    break;
                }

                if (0 == $xoopsDB->getRowsNum($result)) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_NOTSEL . ' </div> ';

                    break;
                }

                while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                    $added_id[] = $cat_data['user_id'];
                }

                $dest = implode(',', $added_id);

                // gerar nro. de lote

                // gravar as mensagens no xmail_aux_send

                // após chamar  função envia_xmails passando nro. do lote

                $lote = get_novo_lote();

                $class_aux_send_l = new classxmail_aux_send_l();

                $class_aux_send_l->lote_solicit = $lote;

                $class_aux_send_l->id_men = $id_men;

                $class_aux_send_l->user_logado = $xoopsUser->getVar('uid');

                $class_aux_send_l->email_conf = $email_conf;

                $class_aux_send_l->mail_fromname = $mail_fromname;

                $class_aux_send_l->mail_fromemail = $mail_fromemail;

                $class_aux_send_l->mail_send_to = implode(',', $mail_send_to); // transf. array em string

                $class_aux_send_l->array_users = $added_id;

                $class_aux_send_l->is_new = 1;

                if (!$class_aux_send_l->incluir()) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_ERRINCLOTE . $men_erro . ' </div> ';

                    break;
                }
            }
        }

        envia_xmails_lote($lote, $dest, $mail_start);
        break;
    case 'form':

    default:
        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where is_new=1';
        if (!$isadmin) {
            $sql .= " and uid='" . $xoopsUser->getVar('uid') . "'";
        }

        $result = $xoopsDB->queryF($sql);
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('submit.php', '1', _MD_XMAIL_NOTMEN);
        }

        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where aprovada=1 and is_new=1  ';
        if (!$isadmin) {
            $sql .= " and uid='" . $xoopsUser->getVar('uid') . "'";
        }
        $sql .= ' order by id_men desc  ';
        $result = $xoopsDB->queryF($sql);
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('index.php', '1', _MD_XMAIL_NOTHAMENAPROV);
        }

        echo "<h4><a href='index.php' > >> Menu Principal </a>  </h4> ";
        $sform = new XoopsThemeForm(_MD_XMAIL_SENDNEWS, 'mailusers', xoops_getenv('PHP_SELF'));

        $men_select = new XoopsFormSelect(_MD_XMAIL_NEWSLETTER, 'id_men');

        $array_men = [];
        $array_men[''] = '---------';
        while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
            $array_men[$cat_data['id_men']] = $cat_data['title_men'];
        }
        $men_select->addOptionArray($array_men);
        $men_select->setExtra('onchange=vermen(this.value)');
        $sform->addElement($men_select);

        $fname_text = new XoopsFormText(_MD_XMAIL_MAILFNAME, 'mail_fromname', 30, 255, $xoopsConfig['sitename']);
        $fromemail = !empty($xoopsConfig['adminmail']) ? $xoopsConfig['adminmail'] : $xoopsUser->getVar('email', 'E');
        $femail_text = new XoopsFormText(_MD_XMAIL_MAILFMAIL, 'mail_fromemail', 30, 255, $fromemail);

        $start_hidden = new XoopsFormHidden('mail_start', 0);
        $op_hidden = new XoopsFormHidden('op', 'send');
        $mail_hidden = new XoopsFormHidden('mail_send_to', 'mail');
        $submit_button = new XoopsFormButton('', 'mail_submit', _SEND, 'submit');

        $sform->addElement($fname_text);
        $sform->addElement($femail_text);
        $sform->addElement($to_checkbox);
        $sform->addElement(new XoopsFormText(_MD_XMAIL_MAILCONFIRM, 'email_conf ', 30, 255, ''));
        $sform->addElement($op_hidden);
        $sform->addElement($mail_hidden);
        $sform->addElement($start_hidden);

        if ($usa_perf) {
            $tab_perf = $classperf->get_tab_perf('<br>');

            $check_perfil = new XoopsFormCheckBox(_MD_XMAIL_SELPERF, 'perfil');

            $check_perfil->addOptionArray($tab_perf);

            $sform->addElement($check_perfil);

            $sform->addElement(new XoopsFormRadioYN(_MD_XMAIL_SEMPERF, 'sem_perf', '', _MD_XMAIL_YES, _MD_XMAIL_NO));
        }

        $sform->addElement($submit_button);
        $sform->display();

        break;
}
require XOOPS_ROOT_PATH . '/footer.php';
?>

