<?php
/*
* $Id: submit.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include 'header.php';

require XOOPS_ROOT_PATH . '/header.php';

global $xoopsUser, $xoopsUser, $xoopsConfig;

if (!is_object($xoopsUser)) {
    redirect_header('index.php', 1, _NOPERM);

    exit();
}

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

foreach ($_GET as $k => $v) {
    ${$k} = $v;
}

$op = 'form';

if (isset($_POST['post'])) {
    $op = 'post';
} elseif (isset($_POST['edit'])) {
    $op = 'edit';
}

$param = new classparam();
if (!$param->busca()) {
    redirect_header(XOOPS_URL . '/modules/xmail/admin/index.php', 2, _AM_XMAIL_ERRORPARAM);
} else {
    if (0 == $param->totreg) {
        // incluir registro

        // add record

        $param->__construct();

        if (!$param->incluir()) {
            redirect_header(XOOPS_URL . '/modules/xmail/admin/index.php', 2, _AM_XMAIL_ERRORPARAMINC);
        }
    }
}

echo "<h4><a href='index.php' > >> " . _MD_XMAIL_MENUPRINCIP . ' </a>  </h4> ';
switch ($op) {
    case 'post':

        if ('save' == $opt) {
            $myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object

            global $xoopsUser, $xoopsConfig;

            if (is_object($xoopsUser)) {
                $uid = $xoopsUser->uid();
            } else {
                $uid = 0;
            }

            $title_men = $myts->addSlashes($_POST['title_men']);

            $subject_men = $myts->addSlashes($_POST['subject_men']);

            $body_men = $myts->addSlashes($_POST['body_men']);

            $uid = $xoopsUser->uid();

            $datesub = time();

            $xoopsModule = XoopsModule::getByDirname('xmail');

            if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
                $aprovada = 0;
            } else {
                $aprovada = 1;
            }

            // verificar se irá aprovar automaticamente.

            if ($param->aprov_auto) {
                $aprovada = 1;
            }

            if ($dohtml) {
                $dobr = 0;
            } else {
                $dobr = 1;
            }

            // 1 = este deverá ser uma newsletter

            if (1 == $is_new) {
                $is_new = 1;
            } else {
                $is_new = 0;
            }

            // 1 = está usando Html.

            if (1 == $dohtml) {
                $dohtml = 1;
            } else {
                $dohtml = 0;
            }

            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_mensage') . " (title_men, subject_men, body_men, uid, datesub,aprovada,dohtml,dobr,is_new) VALUES ('$title_men', '$subject_men', '$body_men', '$uid', '$datesub',$aprovada,$dohtml,$dobr,'$is_new')";

            $result = $xoopsDB->queryF($sql);

            if ($result) {
                if (!$aprovada) {
                    // não é o administrador do módulo - enviar email

                    $xoopsMailer = getMailer();

                    $xoopsMailer->useMail();

                    $xoopsMailer->setToEmails($xoopsConfig['adminmail']);

                    $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);

                    $xoopsMailer->setFromName($xoopsConfig['sitename']);

                    $xoopsMailer->setSubject(_MD_XMAIL_NOTIFYSBJCT);

                    $body = _MD_XMAIL_NOTIFYMSG;

                    $body .= "\n\n" . _MD_XMAIL_TITLE . ': ' . $title_men . ' ' . _MD_XMAIL_IDMEN . ': ' . $xoopsDB->getInsertId();

                    $body .= "\n" . _MD_XMAIL_POSTEDBY . ': ' . XoopsUser::getUnameFromId($uid);

                    $body .= "\n" . _MD_XMAIL_DATE . ': ' . formatTimestamp(time(), 'm', $xoopsConfig['default_TZ']);

                    $body .= "\n" . _MD_XMAIL_CLICKAPROV;

                    $body .= "\n\n" . XOOPS_URL . '/modules/xmail/gerencia.php?op=apr';

                    $xoopsMailer->setBody($body);

                    $xoopsMailer->send(true);

                    echo "<div align='center' >";

                    echo $xoopsMailer->getSuccess();

                    echo $xoopsMailer->getErrors();

                    echo '</div >';

                    redirect_header('index.php', 2, _MD_XMAIL_SUBMITUSER);
                } else {
                    redirect_header('index.php', 2, _MD_XMAIL_SUBMITAPROV);
                }
            } else {
                redirect_header('submit.php', 2, _MD_XMAIL_ERRORSAVINGDB . $sql);
            }

            break;
        }  
            //  $body_men2=$myts->displayTarea($body_men,$dohtml);
            $body_men2 = $myts->previewTarea($body_men, $dohtml);
            show_preview($subject_men, $body_men2);

        // no break
    case 'form':
    default:
        if (empty($opt)) {
            $title_men = '';

            $subject_men = '';

            $body_men = '';

            if ($param->allow_html) {
                $dohtml = 1;
            } else {
                $dohtml = 0;
            }
        }
        require_once __DIR__ . '/include/storyform.inc.php';
        require XOOPS_ROOT_PATH . '/footer.php';
        break;
}


