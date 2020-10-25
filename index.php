<?php

/*
* $Id: index.php
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
switch ($op) {
    case 'send':

        if (empty($dest)) {
            // quando não vazio indica que foi chamado pela func. envia_xmails  em continuação

            // $dest not being empty indicates that it was called by the envia_xmails function

            if ('send' == $op && !empty($_POST['mail_send_to'])) {
                $added = [];

                $added_id = [];

                $criteria = [];

                if (!empty($_POST['mail_inactive'])) {
                    $criteria[] = 'level = 0';
                } else {
                    if (!empty($_POST['mail_mailok'])) {
                        $criteria[] = 'user_mailok = 1';
                    } else {
                        // incluir um visitante

                        $user_list = [];

                        if (!empty($_POST['user_id'])) {
                            foreach ($_POST['user_id'] as $userid) {
                                $user_list[] = $userid;
                            }
                        }

                        // incluir grupos de visitantes

                        if (!empty($_POST['mail_to_group'])) {
                            $memberHandler = xoops_getHandler('member');

                            foreach ($_POST['mail_to_group'] as $groupid) {
                                $members = $memberHandler->getUsersByGroup($groupid, true);

                                // RMV: changed this because makes more sense to me

                                // if options all grouped by 'AND', not 'OR'

                                foreach ($members as $member) {
                                    if (!in_array($member->getVar('uid'), $user_list, true)) {
                                        $user_list[] = $member->getVar('uid');
                                    }
                                }
                            }
                        }

                        if (!empty($user_list)) {
                            $criteria[] = 'uid IN (' . implode(',', $user_list) . ')';
                        }
                    }

                    if (!empty($_POST['mail_lastlog_min'])) {
                        $f_mail_lastlog_min = trim($_POST['mail_lastlog_min']);

                        $time = mktime(0, 0, 0, mb_substr($f_mail_lastlog_min, 5, 2), mb_substr($f_mail_lastlog_min, 8, 2), mb_substr($f_mail_lastlog_min, 0, 4));

                        if ($time > 0) {
                            $criteria[] = "last_login > $time";
                        }
                    }

                    if (!empty($_POST['mail_lastlog_max'])) {
                        $f_mail_lastlog_max = trim($_POST['mail_lastlog_max']);

                        $time = mktime(0, 0, 0, mb_substr($f_mail_lastlog_max, 5, 2), mb_substr($f_mail_lastlog_max, 8, 2), mb_substr($f_mail_lastlog_max, 0, 4));

                        if ($time > 0) {
                            $criteria[] = "last_login < $time";
                        }
                    }

                    if (!empty($_POST['mail_idle_more']) && is_numeric($_POST['mail_idle_more'])) {
                        $f_mail_idle_more = (int)trim($_POST['mail_idle_more']);

                        $time = 60 * 60 * 24 * $f_mail_idle_more;

                        $time = time() - $time;

                        if ($time > 0) {
                            $criteria[] = "last_login < $time";
                        }
                    }

                    if (!empty($_POST['mail_idle_less']) && is_numeric($_POST['mail_idle_less'])) {
                        $f_mail_idle_less = (int)trim($_POST['mail_idle_less']);

                        $time = 60 * 60 * 24 * $f_mail_idle_less;

                        $time = time() - $time;

                        if ($time > 0) {
                            $criteria[] = "last_login > $time";
                        }
                    }
                }

                if (!empty($_POST['mail_regd_min'])) {
                    $f_mail_regd_min = trim($_POST['mail_regd_min']);

                    $time = mktime(0, 0, 0, mb_substr($f_mail_regd_min, 5, 2), mb_substr($f_mail_regd_min, 8, 2), mb_substr($f_mail_regd_min, 0, 4));

                    if ($time > 0) {
                        $criteria[] = "user_regdate > $time";
                    }
                }

                if (!empty($_POST['mail_regd_max'])) {
                    $f_mail_regd_max = trim($_POST['mail_regd_max']);

                    $time = mktime(0, 0, 0, mb_substr($f_mail_regd_max, 5, 2), mb_substr($f_mail_regd_max, 8, 2), mb_substr($f_mail_regd_max, 0, 4));

                    if ($time > 0) {
                        $criteria[] = "user_regdate < $time";
                    }
                }

                if (!empty($criteria)) {
                    if (empty($_POST['mail_inactive'])) {
                        $criteria[] = 'level > 0';
                    }

                    $criteria_object = new CriteriaCompo();

                    foreach ($criteria as $c) {
                        [$field, $op, $value] = preg_split(' ', $c);

                        $criteria_object->add(new Criteria($field, $value, $op), 'AND');
                    }

                    $memberHandler = xoops_getHandler('member');

                    $getusers = $memberHandler->getUsers($criteria_object);

                    foreach ($getusers as $getuser) {
                        if (!in_array($getuser->getVar('uid'), $added_id, true)) {
                            $added_id[] = $getuser->getVar('uid');
                        }
                    }
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

                if (!$class_aux_send_l->incluir()) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_ERRINCLOTE . $men_erro . ' </div> ';

                    break;
                }
            }
        }
        envia_xmails_lote($lote, $dest, $mail_start);
        break;
    case 'form':

        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage');
        if (!$isadmin) {
            $sql .= " where uid='" . $xoopsUser->getVar('uid') . "'";
        }

        $result = $xoopsDB->queryF($sql);
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('submit.php', '1', _MD_XMAIL_NOTMEN);
        }

        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where aprovada=1  ';
        if (!$isadmin) {
            $sql .= " and uid='" . $xoopsUser->getVar('uid') . "'";
        }

        $result = $xoopsDB->queryF($sql);
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('index.php', '1', _MD_XMAIL_NOTHAMENAPROV);
        }

        echo "<h4><a href='index.php' > >> Menu Principal </a>  </h4> ";
        $sform = new XoopsThemeForm(_MD_XMAIL_SENDTO, 'mailusers', xoops_getenv('PHP_SELF'));

        $men_select = new XoopsFormSelect(_MD_XMAIL_SELMEN, 'id_men');
        $sql = 'select id_men,title_men from ' . $xoopsDB->prefix('xmail_mensage');
        if ($isadmin) {
            $sql .= ' where aprovada=1 ';
        } else {
            $sql .= ' where aprovada=1  and uid=' . $xoopsUser->getVar('uid');
        }
        $sql .= ' order by title_men';

        $result = $xoopsDB->query($sql);
        $array_men = [];
        $array_men[''] = '---------';
        while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
            $array_men[$cat_data['id_men']] = $cat_data['title_men'];
        }
        $men_select->addOptionArray($array_men);
        $men_select->setExtra('onchange=vermen(this.value)');
        $sform->addElement($men_select);

        // selecionar visitante
        $userstart = isset($_GET['userstart']) ? (int)$_GET['userstart'] : 0;

        $memberHandler = xoops_getHandler('member');
        $usercount = $memberHandler->getUserCount();
        $nav = new XoopsPageNav($usercount, 200, $userstart, 'userstart', 'op=form');

        $user_select = new XoopsFormSelect('', 'user_id', null, 5, true);
        $criteria = new CriteriaCompo();
        $criteria->setSort('uname');
        $criteria->setOrder('ASC');
        $criteria->setLimit(200);

        $criteria->setStart($userstart);
        $user_select->addOption('0', '--------');
        $user_select->addOptionArray($memberHandler->getUserList($criteria));
        $user_select_tray = new XoopsFormElementTray(_MD_XMAIL_SELUSER, '<br>');
        $user_select_tray->addElement($user_select);
        $user_select_nav = new XoopsFormLabel('', $nav->renderNav(4));
        $user_select_tray->addElement($user_select_nav);

        $sform->addElement($user_select_tray);

        //------------------

        // from finduser section
        $display_criteria = 1;

        if (!empty($display_criteria)) {
            $selected_groups = [];

            $group_select = new XoopsFormSelectGroup(_MD_XMAIL_GROUPIS . '<br>', 'mail_to_group', false, $selected_groups, 5, true);

            $group_select->addOption('0', '-----------------');

            $lastlog_min = new XoopsFormText(_MD_XMAIL_LASTLOGMIN . '<br>' . _MD_XMAIL_TIMEFORMAT . '<br>', 'mail_lastlog_min', 20, 10);

            $lastlog_max = new XoopsFormText(_MD_XMAIL_LASTLOGMAX . '<br>' . _MD_XMAIL_TIMEFORMAT . '<br>', 'mail_lastlog_max', 20, 10);

            $regd_min = new XoopsFormText(_MD_XMAIL_REGDMIN . '<br>' . _MD_XMAIL_TIMEFORMAT . '<br>', 'mail_regd_min', 20, 10);

            $regd_max = new XoopsFormText(_MD_XMAIL_REGDMAX . '<br>' . _MD_XMAIL_TIMEFORMAT . '<br>', 'mail_regd_max', 20, 10);

            $idle_more = new XoopsFormText(_MD_XMAIL_IDLEMORE . '<br>', 'mail_idle_more', 10, 5);

            $idle_less = new XoopsFormText(_MD_XMAIL_IDLELESS . '<br>', 'mail_idle_less', 10, 5);

            $mailok_cbox = new XoopsFormCheckBox('', 'mail_mailok');

            $mailok_cbox->addOption(1, _MD_XMAIL_MAILOK);

            $inactive_cbox = new XoopsFormCheckBox(_MD_XMAIL_INACTIVE . '<br>', 'mail_inactive');

            $inactive_cbox->addOption(1, _MD_XMAIL_IFCHECKD);

            $inactive_cbox->setExtra("onclick='javascript:disableElement(\"mail_lastlog_min\");disableElement(\"mail_lastlog_max\");disableElement(\"mail_idle_more\");disableElement(\"mail_idle_less\");disableElement(\"mail_to_group[]\");'");

            $criteria_tray = new XoopsFormElementTray(_MD_XMAIL_SENDTOUSERS, '<br><br>');

            $criteria_tray->addElement($group_select);

            $criteria_tray->addElement($lastlog_min);

            $criteria_tray->addElement($lastlog_max);

            $criteria_tray->addElement($idle_more);

            $criteria_tray->addElement($idle_less);

            $criteria_tray->addElement($mailok_cbox);

            $criteria_tray->addElement($inactive_cbox);

            $criteria_tray->addElement($regd_min);

            $criteria_tray->addElement($regd_max);

            $sform->addElement($criteria_tray);
        }

        $fname_text = new XoopsFormText(_MD_XMAIL_MAILFNAME, 'mail_fromname', 30, 255, $xoopsConfig['sitename']);
        $fromemail = !empty($xoopsConfig['adminmail']) ? $xoopsConfig['adminmail'] : $xoopsUser->getVar('email', 'E');
        $femail_text = new XoopsFormText(_MD_XMAIL_MAILFMAIL, 'mail_fromemail', 30, 255, $fromemail);
        $to_checkbox = new XoopsFormCheckBox(_MD_XMAIL_SENDTO, 'mail_send_to', 'mail');
        $to_checkbox->addOption('mail', _MD_XMAIL_EMAIL);
        $to_checkbox->addOption('pm', _MD_XMAIL_PM);
        $to_checkbox->addOption('pref', _MD_XMAIL_PREF);

        $start_hidden = new XoopsFormHidden('mail_start', 0);
        $op_hidden = new XoopsFormHidden('op', 'send');
        $submit_button = new XoopsFormButton('', 'mail_submit', _SEND, 'submit');

        $sform->addElement($fname_text);
        $sform->addElement($femail_text);
        $sform->addElement($to_checkbox);
        $sform->addElement(new XoopsFormText(_MD_XMAIL_MAILCONFIRM, 'email_conf ', 30, 255, ''));
        $sform->addElement($op_hidden);
        $sform->addElement($start_hidden);
        $sform->addElement($submit_button);

        $sform->display();

        break;
    default:
        xmaillinks();
}
require XOOPS_ROOT_PATH . '/footer.php';
?>

