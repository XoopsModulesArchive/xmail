<?php
/*
* $Id: verlog.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include 'header.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
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

$param = new classparam();
if (!$param->busca()) {
    redirect_header('gerencia.php', 2, _MD_XMAIL_ERRORPARAM);
}
if (0 == $param->totreg) {
    redirect_header('gerencia.php', 2, _MD_XMAIL_NOTPARAM);
}

switch ($op) {
    case 'send':

        $sql = 'SELECT log.id_user,log.id_men,log.dt_envio,mensage.title_men,mensage.subject_men,mensage.body_men,mensage.date_envio,mensage.uid,mensage.dobr,mensage.dohtml
      FROM ' . $xoopsDB->prefix('xmail_send_log') . ' as log ,' . $xoopsDB->prefix('xmail_mensage') . ' as mensage ';
        $where = ' mensage.id_men=log.id_men   and mensage.is_new=0';
        if (empty($lista_user)) {
            if (!empty($_POST['mail_to_group'])) {  // se informou grupos
                $memberHandler = xoops_getHandler('member');

                $user_list = [];

                foreach ($_POST['mail_to_group'] as $groupid) {
                    $members = $memberHandler->getUsersByGroup($groupid, true);

                    foreach ($members as $member) {
                        if (!in_array($member->getVar('uid'), $user_list, true)) {
                            $user_list[] = $member->getVar('uid');
                        }
                    }
                }

                if (!empty($user_list)) {
                    $where .= ' and id_user IN (' . implode(',', $user_list) . ')';
                }
            }

            $lista_user = implode(',', $user_list);
        } else {
            $where .= ' and id_user IN (' . $lista_user . ')';
        }

        if (!empty($_POST['id_men'])) {
            if (!empty($where)) {
                $where .= ' and  ';
            }

            $where .= " log.id_men='$id_men'";
        }
        if (!$isadmin) {
            if (!empty($where)) {
                $where .= ' and  ';
            }

            $where .= " ( mensage.uid='" . $xoopsUser->getVar('uid') . "' or  log.id_user='" . $xoopsUser->getVar('uid') . "')";
        }

        $sql .= (!empty($where)) ? ' where ' . $where : '';
        $sql2 = $sql; // $sql2 capturada aqui antes da clausula order by para não dar erro

        $sql .= ' order by dt_envio desc ';

        $result = $xoopsDB->query($sql);
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('index.php', '1', _MD_XMAIL_NOTMENLOG);

            exit();
        }

        // paginação
        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $men_p_page = $param->limite_page;
        $userstart = isset($_GET['userstart']) ? (int)$_GET['userstart'] : 0;
        $userfim = $userstart + $men_p_page;

        $sql2    .= ' group by dt_envio order by dt_envio ';
        $result2 = $xoopsDB->query($sql2);

        $usercount = $GLOBALS['xoopsDB']->getRowsNum($result2);

        $nav = new XoopsPageNav($usercount, $men_p_page, $userstart, 'userstart', 'op=send&id_men=' . $id_men . '&lista_user=' . $lista_user);

        $xoopsTpl->assign(['titulo' => _MD_XMAIL_TITLOG]);

        $GLOBALS['xoopsOption']['template_main'] = 'xmail_verlog.htm';
        $topics = [];

        $chave = '';
        $primeiro = true;

        $count = 0;

        while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
            if ($cat_data['dt_envio'] . $cat_data['id_men'] != $chave) {
                if (!$primeiro) {
                    if ($count >= $userstart and $count < $userfim) {
                        $xoopsTpl->append(
                            'topics',
                            [
                                'id_men' => $topics['id_men'],
                                'body_men' => $topics['body_men'],
                                'users' => $topics['users'],
                                'subject_men' => $topics['subject_men'],
                                'title_men' => $topics['title_men'],
                                'date_envio' => $topics['date_envio'],
                                'anexos' => $topics['anexos'],
                            ]
                        );

                        $count++;
                    } else {
                        $count++;
                    }
                } else {
                    $primeiro = false;
                }

                $topics['id_men'] = $cat_data['id_men'];

                $topics['title_men'] = $cat_data['title_men'];

                $topics['body_men'] = $myts->displayTarea($cat_data['body_men'], $cat_data['dohtml'], 1, 1, 1, $cat_data['dobr']);

                $topics['subject_men'] = $cat_data['subject_men'];

                //	        	$topics['date_envio']= (!empty($cat_data['dt_envio'])) ?  formatTimestamp($cat_data['dt_envio'],"d-M-Y H:i:s ") : ""   ;

                $topics['date_envio'] = (!empty($cat_data['dt_envio'])) ? formatTimestamp($cat_data['dt_envio'], $param->format_time) : '';

                $topics['users'] = XoopsUserUtility::getUnameFromId($cat_data['id_user']);

                // verificar arquivos arquivos

                $classfiles = new classfiles();

                $arqs = $classfiles->array_anexos($cat_data['id_men']);

                $topics['anexos'] = '';

                for ($i = 0, $iMax = count($arqs); $i < $iMax; $i++) {
                    if ($i > 0) {
                        $topics['anexos'] .= ' , ';
                    }

                    $topics['anexos'] .= '   ' . ($arqs[$i]['filerealname']);
                }
            } else {
                $topics['users'] .= '<br>' . XoopsUserUtility::getUnameFromId($cat_data['id_user']);
            }

            $chave = $cat_data['dt_envio'] . $cat_data['id_men'];
        }
        if ($count >= $userstart and $count < $userfim) {
            $xoopsTpl->append('topics', ['id_men' => $topics['id_men'], 'body_men' => $topics['body_men'], 'users' => $topics['users'], 'subject_men' => $topics['subject_men'], 'title_men' => $topics['title_men'], 'date_envio' => $topics['date_envio'], 'anexos' => $topics['anexos']]);
        }

        $xoopsTpl->assign(['envto' => _MD_XMAIL_ENVTO, 'mensagem' => _MD_XMAIL_MESAGE]);
        $xoopsTpl->assign(['title_men' => _MD_XMAIL_TITULO, 'subject' => _MD_XMAIL_SUBJECT, 'codigo' => _MD_XMAIL_IDMEN, 'datacad' => _MD_XMAIL_DATACAD]);
        $xoopsTpl->assign(['ultenvio' => _MD_XMAIL_ULTENVIO]);
        $xoopsTpl->assign(['anexos' => _MD_XMAIL_ANEXOS]);

        $xoopsTpl->assign(['navega' => $nav->renderNav(4)]);
        break;
    default:  // solicitar form pedindo dados para consulta / request a form that gets input for a query

        echo "<h4><a href='index.php' > >> " . _MD_XMAIL_MENUPRINCIP . ' </a>  </h4> ';

        $result = $xoopsDB->queryF('SELECT * FROM ' . $xoopsDB->prefix('xmail_send_log') . ' limit 1  ');
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('index.php', '1', _MD_XMAIL_NOTMENSEND);
        }

        //    	$mytree = new XoopsTree($xoopsDB->prefix("xmail_mensage"),"id_men","0");
        $sform = new XoopsThemeForm(_MD_XMAIL_FORMVERLOG, 'mailusers', xoops_getenv('PHP_SELF'));

        //		ob_start();
        //		$sform->addElement(new XoopsFormHidden('id_men', $id_men));
        //		$mytree->makeMySelBox("title_men", "title_men",0,1);
        //		$sform->addElement(new XoopsFormLabel(, ob_get_contents()));
        //		ob_end_clean();

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
        $sform->addElement($men_select);

        $display_criteria = 1;

        if (!empty($display_criteria)) {
            $selected_groups = [];

            $group_select = new XoopsFormSelectGroup(_MD_XMAIL_GROUPIS . '<br>', 'mail_to_group', false, $selected_groups, 5, true);

            $group_select->setExtra("onclick='javascript:disableElement(\"id_men\")'");

            $criteria_tray = new XoopsFormElementTray(_MD_XMAIL_SENDTOUSERS, '<br><br>');

            $criteria_tray->addElement($group_select);

            $sform->addElement($criteria_tray);
        }

        $op_hidden = new XoopsFormHidden('op', 'send');
        $submit_button = new XoopsFormButton('', 'mail_submit', _SEND, 'submit');

        $sform->addElement($op_hidden);
        $sform->addElement($submit_button);
        $sform->display();
}
require XOOPS_ROOT_PATH . '/footer.php';


