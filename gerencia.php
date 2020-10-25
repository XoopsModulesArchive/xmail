<?php
/*
* $Id: gerencia.php
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

if ($xoopsUser) {
    // Even the regular user can access, but gets to see only his own messages

    if (!$isadmin and ('apr' == $op or 'apr_exe' == $op or 'desapr' == $op)) {
        redirect_header(XOOPS_URL . '/', 3, _NOPERM);

        exit();
    }
} else {
    redirect_header(XOOPS_URL . '/', 3, _NOPERM);

    exit();
}

if ('apr' == $op) {
    $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where aprovada=0 ORDER BY id_men ');

    if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
        redirect_header(XOOPS_URL . '/modules/xmail/submit.php?op=add', '1', _MD_XMAIL_NOTMENAPROV);
    }

    $totmen = $GLOBALS['xoopsDB']->getRowsNum($result);
} else {
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage');

    if (!$isadmin) {
        $sql .= ' where  uid= ' . $xoopsUser->getvar('uid');
    }

    if (empty($ordem)) {
        $ordem = $param->ordem_admin;
    }

    if ('C' == $ordem) {
        $sql .= ' ORDER BY id_men ';
    } elseif ('DN' == $ordem) {
        $sql .= ' ORDER BY date_envio desc ';
    } elseif ('DA' == $ordem) {
        $sql .= ' ORDER BY date_envio ';
    } elseif ('A' == $ordem) {
        $sql .= ' ORDER BY title_men ';
    } else {
        $sql .= ' ORDER BY id_men ';
    }  

    $result = $xoopsDB->query($sql);

    if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
        redirect_header(XOOPS_URL . '/modules/xmail/submit.php?op=add', '1', _MD_XMAIL_NOTMEN);
    }

    // calculate total number of messages

    //  that will change once we make the navigation by page

    if (!empty($limite) and $limite > 0) {
        $sql = 'SELECT count(id_men) as totmen from  ' . $xoopsDB->prefix('xmail_mensage');

        $result2 = $xoopsDB->query($sql);

        $cat_data = $xoopsDB->fetchArray($result2);

        $totmen = $cat_data['totmen'];
    } else {
        $totmen = $GLOBALS['xoopsDB']->getRowsNum($result);
    }
}

global $xoopsUser, $xoopsConfig, $xoopsDB;

switch ($op) {
    case 'exc_exec':  // execute message deletion

        $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . " WHERE id_men =$id_men");
        if (!$result) {
            redirect_header('gerencia.php', 2, _MD_XMAIL_NOTFOUND . ' ' . $id_men);
        }
        $cat_data = $xoopsDB->fetchArray($result);
        $dias_ult_envio = (time() - $cat_data['date_envio']) / 60 / 60 / 24;
        if ($dias_ult_envio > $param->dias_excluir) {
            //  delete from log

            $result = $xoopsDB->query('delete FROM ' . $xoopsDB->prefix('xmail_send_log') . " WHERE id_men =$id_men");

            if (!$result) {
                $men_erro = _MD_XMAIL_ERRORLOG;
            } else {
                $men_erro = _MD_XMAIL_LOGDELOK;
            }

            //  delete from table

            $result = $xoopsDB->query('delete FROM ' . $xoopsDB->prefix('xmail_mensage') . " WHERE id_men = $id_men");

            if (!$result) {
                $men_erro .= '<br>' . _MD_XMAIL_ERRORSAVINGDB;
            } else {
                $men_erro .= '<br>' . _MD_XMAIL_SAVEOK;
            }

            // check to see if file deleted from drive and database

            $classfiles = new classfiles();

            $array_anexos = $classfiles->array_anexos($id_men);

            for ($i = 0, $iMax = count($array_anexos); $i < $iMax; $i++) {
                $classfiles->fileid = $array_anexos[$i]['fileid'];

                if (!$classfiles->excluir($id_men)) {
                    echo 'não excluiu o fileid ';
                }
            }

            // exclude external files link

            $men_anexo = new men_anexo();

            if (!$men_anexo->excluir($id_men)) {
                $men_erro .= '<br>' . _MD_XMAIL_ERRANEXOEXC;
            }

            redirect_header('gerencia.php', 4, $men_erro);
        } else {
            redirect_header('gerencia.php', 2, sprintf(_MD_XMAIL_NOTDELEMEN, $param->dias_excluir));
        }

        break;
    case 'exc':  // delete message
        $result = $xoopsDB->query('SELECT id_men, title_men FROM ' . $xoopsDB->prefix('xmail_mensage') . " WHERE id_men = $id_men");
        if ($result) {
            [$id_men, $title_men] = $xoopsDB->fetchRow($result);

            echo "<table width='60%' border='0' cellpadding = '2' cellspacing='1' class = 'confirmMsg'><tr><td class='confirmMsg'>";

            echo "<div class='confirmMsg'>";

            echo '<h4>';

            echo '' . _MD_XMAIL_DELETEMEN . "</font></h4>$title_men<br><br>";

            echo '<table><tr><td>';

            echo myTextForm2('gerencia.php?op=exc_exec&id_men=' . $id_men, _MD_XMAIL_YES);

            echo '</td><td>';

            echo myTextForm2('gerencia.php?op=default', _MD_XMAIL_NO);

            echo '</td></tr></table>';

            echo '</div><br><br>';

            echo '</td></tr></table>';
        } else {
            redirect_header('gerencia.php', 2, _MD_XMAIL_NOTFOUND . ' ' . $id_men);
        }

        break;
    case 'apr_exe':  // execute message approval

        $result = $xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('xmail_mensage') . " SET  aprovada=1  where id_men='$id_men'   ");

        if (!$result) {
            redirect_header('gerencia.php', 2, _MD_XMAIL_ERRORSAVINGDB);
        }

        // send mail to user letting him know that message was approved
        $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . " where id_men='$id_men' ");
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header(XOOPS_URL . '/modules/xmail/submit.php?op=add', '1', _MD_XMAIL_NOTFOUND . ' ' . $id_men);
        }
        $cat_data = $xoopsDB->fetchArray($result);
        $obj_user = new XoopsUser($cat_data['uid']);

        $xoopsMailer = getMailer();
        $xoopsMailer->setToUsers($obj_user);

        $xoopsMailer->setFromName($xoopsUser->getVar('name'));
        $xoopsMailer->setFromEmail($xoopsUser->getVar('email'));
        $xoopsMailer->setSubject(_MD_XMAIL_YOURMENAPROV);
        $body = _MD_XMAIL_NOTIFYMSG;
        $body .= "\n\n" . _MD_XMAIL_YOURMENAPROV;
        $body .= "\n" . _MD_XMAIL_TITLE . ': ' . $cat_data['title_men'];
        $body .= "\n" . _MD_XMAIL_DATE . ': ' . formatTimestamp(time(), 'm', $xoopsConfig['default_TZ']);

        $xoopsMailer->setBody($body);

        /* send to private message is troubleshooting
           // untill we fix it, message is sent via mail

                  if ($obj_user->getVar('notify_method')==2) {
                       $xoopsMailer->useMail();
                }
                if ($obj_user->getVar('notify_method')==1) {
                    $xoopsMailer->usePM();
                }

        */

        $xoopsMailer->useMail();
        $xoopsMailer->send(true);
        echo $xoopsMailer->getSuccess();
        echo $xoopsMailer->getErrors();

        redirect_header('gerencia.php', 2, _MD_XMAIL_SAVEOK);

        break;
    case 'desapr':  // execute disapproval

        $result = $xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('xmail_mensage') . " SET  aprovada=0  where id_men='$id_men'   ");

        if (!$result) {
            redirect_header('gerencia.php', 2, _MD_XMAIL_ERRORSAVINGDB);
        }

        redirect_header('gerencia.php', 2, _MD_XMAIL_SAVEOK);

        break;
    case 'post':  // executar alteração / execute modification

        if ('save' == $opt) {
            $myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object

            global $xoopsUser, $xoopsConfig;

            $title_men = $myts->addSlashes($_POST['title_men']);

            $subject_men = $myts->addSlashes($_POST['subject_men']);

            $body_men = $myts->addSlashes($_POST['body_men']);

            if ($dohtml) {
                $dobr = 0;
            } else {
                $dobr = 1;
            }

            $sql = 'UPDATE ' . $xoopsDB->prefix('xmail_mensage') . " SET title_men='$title_men', subject_men='$subject_men', body_men='$body_men' ,dohtml='$dohtml' ,dobr=$dobr,is_new='$is_new' where id_men='$id_men' ";

            $result = $xoopsDB->queryF($sql);

            //    echo $sql;

            //    return;

            if (!$result) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_ERRORSAVINGDB . $sql);
            }

            redirect_header('gerencia.php', 2, _MD_XMAIL_SAVEOK);
        } else {
            $body_men2 = $myts->displayTarea($body_men, $dohtml);

            show_preview($subject_men, $body_men2);
        }

        // no break
    case 'alt':
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
        if (empty($opt)) {  // indicate it is not a preview
            // find id_men

            $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . " where id_men='$id_men'");

            if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_NOTFOUND . ' ' . $id_men);
            }

            $cat_data = $xoopsDB->fetchArray($result);

            if ($cat_data['date_envio'] > 0) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_NOTALT . ' ' . $id_men);
            }

            if (!$isadmin and $cat_data['aprovada']) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_NOTALT_FOIAPROV . ' ' . $id_men);
            }

            $title_men = $cat_data['title_men'];

            $subject_men = $cat_data['subject_men'];

            $body_men = $cat_data['body_men'];

            $dobr = $cat_data['dobr'];

            //        if ( $dobr!= 0) {

            //			 $body_men= $myts->nl2Br($body_men);

            //		}

            $dohtml = $cat_data['dohtml'];

            $is_new = $cat_data['is_new'];
        }
        require_once __DIR__ . '/include/storyform.inc.php';
        // form for uploading
        if ($param->permite_anexo) {
            // show table with attachement files

            $classfiles = new classfiles();

            $classfiles->exibe_anexos($id_men);

            echo '<br> ';

            $classfiles->exibe_files($id_men);

            // teste com form do xoops

            $uform = new XoopsThemeForm(_MD_XMAIL_UPLOADANEXO, 'formupload', xoops_getenv('PHP_SELF'));

            $uform->setExtra("enctype='multipart/form-data'");

            $uform->addElement(new XoopsFormHidden('id_men', $id_men));

            $uform->addElement(new XoopsFormFile('', 'anexo1', $param->maxupload));

            $uform->addElement(new XoopsFormTextArea(_MD_XMAIL_FILEDESCRIPT, 'filedescript', '', 2, 60));

            $uform->addElement(new XoopsFormButton('', 'upload', 'Upload', 'submit'));

            $uform->display();
        }
        break;
    case 'upload':

        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/uploadfile.php';
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
        $upload = new uploadfile('anexo1');
        $upload->setAddExt(0);  // sets not to show file extension
        $upload->maxFilesize = $param->maxupload;
        $upload->loadPostVars();
        $upload->setMode($param->file_mode);
        $upload->stripSpaces = 0;
        $dirupload = XOOPS_ROOT_PATH . '/' . $param->dir_upload;
        if (!is_dir($dirupload)) {
            $oldumask = umask(0);

            if (!mkdir((string)$dirupload, octdec($param->file_mode))) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_DIRNOTFOUND . $dirupload);
            }

            umask($oldumask);
        }

        $dirupload .= '/' . $xoopsUser->getVar('uname');
        if (!is_dir($dirupload)) {
            $oldumask = umask(0);

            if (!mkdir((string)$dirupload, octdec($param->file_mode))) {
                redirect_header('gerencia.php', 2, _MD_XMAIL_DIRNOTFOUND . $dirupload);
            }

            umask($oldumask);
        }
        //  check for file exists and lets the user know if yess
        if (is_file($dirupload . '/' . $upload->originalName)) {
            redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_FILEFOUND);
        }

        $distfilename = $upload->doUpload($dirupload . '/' . $upload->originalName);

        if ($distfilename) {
            $classfiles = new classfiles();

            $classfiles->filerealname = $upload->originalName;

            $classfiles->date = time();

            $classfiles->ext = $upload->ext;

            $classfiles->minetype = $upload->minetype;

            $classfiles->filedescript = $filedescript;

            $classfiles->uid = $xoopsUser->getVar('uid');

            $classfiles->dir_upload = $param->dir_upload . '/' . $xoopsUser->getVar('uname');

            if ($classfiles->incluir()) {
                $men_anexo = new men_anexo();

                $men_anexo->fileid = $xoopsDB->getInsertId();

                $men_anexo->idmen = $id_men;

                if (!$men_anexo->incluir()) {
                    redirect_header('gerencia.php', 2, _MD_XMAIL_FALHAMENANEXO);
                }
            } else {
                redirect_header('gerencia.php', 2, _MD_XMAIL_FALHAINCFILE . $dirupload);
            }

            redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_UPLOADOK . $dirupload);
        } else {
            redirect_header('gerencia.php', 2, _MD_XMAIL_FALHAUPLOAD . $dirupload);
        }

        break;
    case 'exc_anexo':  //  execute delete attachment file
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';

        $result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xmail_mensage') . " where id_men='$id_men'");
        if ('0' == $GLOBALS['xoopsDB']->getRowsNum($result)) {
            redirect_header('gerencia.php', 2, _MD_XMAIL_NOTFOUND . ' ' . $id_men);
        }

        $cat_data = $xoopsDB->fetchArray($result);
        if ($cat_data['date_envio'] > 0) {
            redirect_header('gerencia.php', 2, _MD_XMAIL_NOTALT . ' ' . $id_men);
        }

        $classfiles = new classfiles();
        $men_anexo = new men_anexo();
        $men_anexo->fileid = $fileid;
        $men_anexo->idmen = $id_men;

        $classfiles->fileid = $fileid;

        if ($men_anexo->excluir($id_men, $fileid)) {
            $classfiles->excluir();
        } else {
            redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_FALHAEXCANEXO);
        }

        redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_SAVEOK);

        break;
    case 'anexar':
        require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
        $men_anexo = new men_anexo();
        $men_anexo->fileid = $fileid;
        $men_anexo->idmen = $id_men;
        if ($men_anexo->incluir()) {
            redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_SAVEOK);
        } else {
            redirect_header('gerencia.php?op=alt&id_men=' . $id_men, 2, _MD_XMAIL_FALHAMENANEXO);
        }

        break;
    case 'apr':

    case 'default':
    default:     // show all stored messages

        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $men_p_page = $param->limite_page;
        $userstart = isset($_GET['userstart']) ? (int)$_GET['userstart'] : 0;
        $userfim = $userstart + $men_p_page;

        $usercount = $totmen;
        $nav = new XoopsPageNav($usercount, $men_p_page, $userstart, 'userstart', 'op=' . $op . '&ordem=' . $ordem);

        if ('apr' == $op) {
            $xoopsTpl->assign(['titulo' => _MD_XMAIL_TIT2]);
        } else {
            $xoopsTpl->assign(['titulo' => _MD_XMAIL_TIT1]);

            $xoopsTpl->assign(['totalmen' => _MD_XMAIL_TOTALMEN . ' ' . $totmen]);
        }

        $GLOBALS['xoopsOption']['template_main'] = 'xmail_mensage.htm';
        $topics = [];

        $count = 0;
        while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
            if ($count >= $userstart and $count < $userfim) {
                $topics['body_men'] = $myts->displayTarea($cat_data['body_men'], $cat_data['dohtml'], 1, 1, 1, $cat_data['dobr']);

                $topics['id_men'] = $cat_data['id_men'];

                //	        	$topics['datesub']= formatTimestamp($cat_data['datesub'],"d-M-Y");

                $topics['datesub'] = formatTimestamp($cat_data['datesub'], $param->format_time);

                $topics['subject_men'] = $cat_data['subject_men'];

                // only works for XOOPS-JP 2.0.5

                $topics['poster'] = XoopsUserUtility::getUnameFromId($cat_data['uid']);

                //    			$topics['poster'] = $cat_data['uid'];  // p/ versões anteriores

                //    			uncomment the line above for earlier versions

                $topics['title_men'] = $cat_data['title_men'];

                if ($isadmin) {
                    if ('0' == $cat_data['aprovada']) {
                        $topics['opt2'] = 'apr_exe';

                        $topics['aprov'] = _MD_XMAIL_APROVAR;
                    } else {
                        $topics['opt2'] = 'desapr';

                        $topics['aprov'] = _MD_XMAIL_DESAPROV;
                    }
                } else {
                    $topics['opt2'] = '';

                    $topics['aprov'] = '';
                }

                $topics['date_envio'] = (!empty($cat_data['date_envio'])) ? formatTimestamp($cat_data['date_envio'], $param->format_time) : '';

                $topics['is_new'] = $cat_data['is_new'];

                if ($cat_data['is_new']) {
                    $topics['newsletter'] = _MD_XMAIL_NEWSLETTER;
                } else {
                    $topics['newsletter'] = '';
                }

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
                        'opt2' => $topics['opt2'],
                        'aprov' => $topics['aprov'],
                        'anexos' => $topics['anexos'],
                        'newsletter' => $topics['newsletter'],
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
        if ($cat_data['is_new']) {
            $xoopsTpl->assign(['newsletter' => _MD_XMAIL_NEWSLETTER]);
        } else {
            $xoopsTpl->assign(['newsletter' => '']);
        }

        if ('apr' == $op) {
            $xoopsTpl->assign(['comform' => 'N']);
        } else {
            $xoopsTpl->assign(['comform' => 'S']);
        }

        $xoopsTpl->assign(['navega' => $nav->renderNav(4)]);

        break;
}

require XOOPS_ROOT_PATH . '/footer.php';


