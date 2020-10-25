<?php
/*
* $Id: include/functions.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

$IconArray = [
    'css.gif' => 'css',
    //"ico.gif"		  => "ico",
    'doc.gif' => 'doc',
    'html.gif' => 'html htm shtml htm',
    'pdf.gif' => 'pdf',
    'txt.gif' => 'conf sh shar csh ksh tcl cgi',
    'php.gif' => 'php php4 php3 phtml phps',
    'js.gif' => 'js',
    'sql.gif' => 'sql',
    'pl.gif' => 'pl',
    'gif.gif' => 'gif',
    'png.gif' => 'png',
    'bmp.gif' => 'bmp',
    'jpg.gif' => 'jpeg jpe jpg',
    'c.gif' => 'c cpp',
    'rar.gif' => 'rar',
    'zip.gif' => 'zip tar gz tgz z ace arj cab bz2',
    'mid.gif' => 'mid kar',
    'wav.gif' => 'wav',
    'wax.gif' => 'wax',
    'xm.gif' => 'xm',
    'ram.gif' => 'ram',
    'mpg.gif' => 'mp1 mp2 mp3 wma',
    'mp3.gif' => 'mpeg mpg mov avi rm',
    'exe.gif' => 'exe com dll bin dat rpm deb',
    'txt.gif' => 'txt ini xml xsl ini inf cfg log nfo ico',
];

function envia_xmails($mail_subject, $mail_body, $dest, $mail_start, $mail_fromname, $mail_fromemail, $mail_send_to, $id_men, $grvlog, $lote, $email_conf = '', $is_new = 0)
{
    global $xoopsUser, $xoopsDB;

    // $grvlog=1  indica para gravar na tabela de log xmail_send_log

    // $grvlog=1  indication for logging in table xmail_send_log

    // is_new=1  indica que é uma newsletter. Necessário para resgatar dados dos

    //           visitantes a partir da tabela xmail_newsletter

    $dt_envio = time();

    if (empty($mail_send_to)) {
        redirect_header('index.php?op=form', 2, _MD_XMAIL_ERRMAILSEND);
    }

    if (empty($dest)) {
        redirect_header('index.php?op=form', 2, _MD_XMAIL_NOTSEL);
    }

    if (empty($mail_subject) or empty($mail_body)) {
        if ($id_men > 0) {
            // localizar mensagem / find message

            $result = $xoopsDB->query('SELECT subject_men, body_men ,dohtml ,dobr FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where id_men=' . $id_men);

            if ($result) {
                $query_data = $GLOBALS['xoopsDB']->fetchBoth($result, MYSQL_ASSOC);

                $mail_subject = $query_data['subject_men'];

                $mail_body = $query_data['body_men'];
            } else {
                redirect_header('index.php?op=form', 2, _MD_XMAIL_ERRCADMEN);
            }
        } else {
            if (empty($mail_subject) or empty($mail_body)) {
                redirect_header('index.php?op=form', 2, _MD_XMAIL_ERRMENNOTSEL);
            }
        }
    }

    if (empty($mail_fromname)) {
        $mail_fromname = $xoopsUser->getVar('email');
    }

    if (empty($mail_fromemail)) {
        $mail_fromemail = $xoopsUser->getVar('email');
    }

    if (!is_array($dest)) {
        $dest = explode(',', $dest);
    }

    $added = [];

    $added_id = [];

    if (!$is_new) {
        foreach ($dest as $to_user) {
            if (!in_array($to_user, $added_id, true)) {
                $added[] = new XoopsUser($to_user);

                $added_id[] = $to_user;
            }
        }
    } else {
        // montar objeto xoopsuser com dados da tabela xmail_newsletter

        //****     terminar  *****

        $sql = 'select * from  ' . $xoopsDB->prefix('xmail_newsletter') . ' where confirmed=1 ';

        $result = $xoopsDB->queryF($sql);

        if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
            redirect_header('index.php', 2, _MD_XMAIL_NOTASSIN);
        } else {
            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $added_id[] = $cat_data['user_id'];

                $user_obj = new XoopsUser();

                $user_obj->assignVar('uid', $cat_data['user_id']);

                $user_obj->assignVar('name', $cat_data['user_name']);

                $user_obj->assignVar('uname', $cat_data['user_nick']);

                $user_obj->assignVar('email', $cat_data['user_email']);

                $added[] = $user_obj;
            }
        }
    }

    $added_count = count($added);

    //  pegar de parametros  xmail_param / get parameters xmail_param

    $param = new classparam();

    if (!$param->busca()) {
        redirect_header('index.php', 2, _MD_XMAIL_ERRORPARAM);
    }

    if (0 == $param->totreg) {
        redirect_header('index.php', 2, _MD_XMAIL_NOTPARAM);
    }

    $quant_envia = $param->envia_xmails;

    if ($added_count > 0) {
        $mail_end = ($added_count > ($mail_start + $quant_envia)) ? ($mail_start + $quant_envia) : $added_count;

        $myts = MyTextSanitizer::getInstance();

        $xoopsMailer = getMailer();

        $xoopsMailer->setFromName($myts->stripSlashesGPC($mail_fromname));

        $xoopsMailer->setFromEmail($myts->stripSlashesGPC($mail_fromemail));

        $xoopsMailer->setSubject($myts->stripSlashesGPC($mail_subject));

        $xoopsMailer->multimailer->ContentType = 'text/html';

        // testando confirmação  17/01/2005

        $xoopsMailer->multimailer->ConfirmReadingTo = $email_conf;

        //  fim do teste

        $email_to_pm = [];  // guardar os id de usuarios que receberão pm ao invez de mail

        // anexar arquivos

        $classfiles = new classfiles();

        $arqs = $classfiles->array_anexos($id_men);

        for ($i = 0, $iMax = count($arqs); $i < $iMax; $i++) {
            $xoopsMailer->multimailer->addAttachment($arqs[$i]['file']);
        }

        $array_mail = []; // visitantes que receberão email
        $array_pm = [];   // visitantes que receberão mensagem particular

        for ($i = $mail_start; $i < $mail_end; $i++) {
            $objuser = $added[$i];

            if ((in_array('pref', $mail_send_to, true) and 2 == $objuser->getVar('notify_method')) or (!in_array('pref', $mail_send_to, true) and in_array('mail', $mail_send_to, true)) or $is_new) {
                if (0 == $param->veri_mailok) {
                    if ($objuser->getVar('user_mailok')) {
                        $array_mail[] = $added[$i];
                    } else {
                        // enviar para mensagem particular

                        $array_pm[] = $added[$i];

                        $email_to_pm[] = $added_id[$i];

                        $xoopsMailer->errors[] = sprintf(_MD_XMAIL_NOTMAILOK, $objuser->getVar('uname'));
                    }
                } else {
                    if ($objuser->getVar('user_mailok')) {
                        $array_mail[] = $added[$i];
                    } else {
                        $xoopsMailer->errors[] = sprintf(_MD_XMAIL_NOTMAILOK2, $objuser->getVar('uname'));
                    }
                }
            }

            if ((in_array('pref', $mail_send_to, true) and 1 == $objuser->getVar('notify_method')) or (!in_array('pref', $mail_send_to, true) and in_array('pm', $mail_send_to, true))) {
                if (!in_array($added_id[$i], $email_to_pm, true)) {
                    $array_pm[] = $added[$i];
                }
            }

            if ((in_array('pref', $mail_send_to, true) and 0 == $objuser->getVar('notify_method'))) {
                $xoopsMailer->errors[] = sprintf(_MD_XMAIL_NOTDEFPREF, $objuser->getVar('uname'));
            }
        }

        if (count($array_mail) > 0) {
            $xoopsMailer->toUsers = [];

            for ($i = 0, $iMax = count($array_mail); $i < $iMax; $i++) {
                $xoopsMailer->setToUsers($array_mail[$i]);
            }

            $xoopsMailer->setBody($myts->displayTarea($mail_body, $query_data['dohtml'], 1, 1, 1, $query_data['dobr']));

            $xoopsMailer->isMail = true;

            $xoopsMailer->isPM = false;

            $xoopsMailer->send(true);
        }

        if (count($array_pm) > 0) {
            $mail_body = inc_anexos_pm($mail_body, $arqs);

            $xoopsMailer->toUsers = [];

            for ($i = 0, $iMax = count($array_pm); $i < $iMax; $i++) {
                $objuser = $array_pm[$i];

                $xoopsMailer->setToUsers($objuser);
            }

            $xoopsMailer->setBody($mail_body);

            $xoopsMailer->isMail = false;

            $xoopsMailer->isPM = true;

            $xoopsMailer->send(true);
        }

        echo $xoopsMailer->getSuccess();

        echo $xoopsMailer->getErrors();

        $lista_users = [];

        for ($i = 0, $iMax = count($xoopsMailer->toUsers); $i < $iMax; $i++) {
            $obj_user = $xoopsMailer->toUsers[$i];

            if (trecho_in_array($obj_user->getVar('uname'), $xoopsMailer->success)) {
                $sql = 'INSERT INTO  ' . $xoopsDB->prefix('xmail_send_log') . " (id_user,id_men,dt_envio)
                     values ('" . $obj_user->getVar('uid') . "','" . $id_men . "','" . $dt_envio . "')";

                $result = $xoopsDB->queryF($sql);

                if (!$result) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_ERRGRVLOG . "$sql  </div> ";
                } else {
                    // montar array com visitantes para eliminar dos lotes

                    $lista_users[] = $obj_user->getVar('uid');
                }
            }
        } // fecha for / close for

        // eliminar da tabela de lote

        if (count($lista_users) > 0) {
            $obj_lote = new classxmail_aux_send();

            $obj_lote->lote_solicit = $lote;

            if (!$obj_lote->excluir(0, $lista_users)) {
                echo "<div class='errorMsg' >" . sprintf(_MD_XMAIL_ERREXCUSER, $lote . ' - ' . $men_erro) . ' </div> ';
            }
        }

        //  fim da gravação do log / end of logging

        // atualizar no cadastro da mensagem data de envio

        // update sent date in message record

        if (count($xoopsMailer->success) > 0) {
            $result = $xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('xmail_mensage') . " set date_envio='$dt_envio' where id_men='$id_men'");

            if (!$result) {
                echo "<div class='errorMsg' >" . _MD_XMAIL_ERRDTENV . ' </div> ';
            }
        }

        if ($added_count > $mail_end) {
            //				$form = new XoopsThemeForm(_MD_XMAIL_SENDTO, "mailusers",$_SERVER['PHP_SELF']."?opt=MG" );

            $form = new XoopsThemeForm(_MD_XMAIL_SENDTO, 'mailusers', $_SERVER['PHP_SELF']);

            $submit_button = new XoopsFormButton('', 'mail_submit', _MD_XMAIL_NEXT, 'submit');

            $sent_label = new XoopsFormLabel(_MD_XMAIL_ENVIADO, sprintf(_MD_XMAIL_SENTNUM, $mail_start + 1, $mail_end, $added_count));

            //				$fname_hidden = new XoopsFormHidden("mail_fromname", htmlspecialchars($mail_fromname));

            //				$femail_hidden = new XoopsFormHidden("mail_fromemail", htmlspecialchars($mail_fromemail));

            //				$subject_hidden = new XoopsFormHidden("mail_subject", htmlspecialchars($mail_subject));

            //				$body_hidden = new XoopsFormHidden("mail_body", htmlspecialchars($mail_body));

            $start_hidden = new XoopsFormHidden('mail_start', $mail_end);

            $lote_hidden = new XoopsFormHidden('lote', $lote);

            $op_hidden = new XoopsFormHidden('op', 'send');

            //                $id_men_hidden = new XoopsFormHidden("id_men", $id_men);

            // técnica para passar matriz via post

            // technique to pass an array thru post

            //   if ( !empty($mail_send_to) ) {

            //					foreach ( $mail_send_to as $mail) {

            //						$to_hidden = new XoopsFormHidden("mail_send_to[]", $mail);

            //						$form->addElement($to_hidden);

            //					}

            //				}

            //

            $dest_hidden = new XoopsFormHidden('dest', implode(',', $dest));

            $form->addElement($sent_label);

            //	$form->addElement($fname_hidden);

            //	$form->addElement($femail_hidden);

            //	$form->addElement($subject_hidden);

            //	$form->addElement($body_hidden);

            $form->addElement($start_hidden);

            $form->addElement($lote_hidden);

            $form->addElement($dest_hidden);

            $form->addElement($op_hidden);

            //	$form->addElement($id_men_hidden);

            $form->addElement($submit_button);

            $form->display();
        } else {
            echo '<h4>' . _MD_XMAIL_SENDCOMP . '</h4>';
        }
    } else {
        echo '<h4>' . MD_XMAIL_NOTSEL . '</h4>';
    }
}

function inc_anexos_pm($mail_body, $arqs)
{
    if (count($arqs) > 0) {
        $mail_body .= "\n\n";

        $mail_body .= '[img align=left]' . XOOPS_URL . '/modules/xmail/images/icon/download.gif [/img] ' . _MD_XMAIL_ANEXOS . "\n";

        for ($i = 0, $iMax = count($arqs); $i < $iMax; $i++) {
            $mail_body .= "\n[url=" . XOOPS_URL . '/modules/xmail/download.php?fileid=' . $arqs[$i]['fileid'] . ']' . $arqs[$i]['filerealname'] . '[/url]';
        }
    }

    return $mail_body;
}

function xmaillinks()
{
    global $isadmin;

    echo "<table width='100%' border='0' cellspacing='1' cellpadding='2' class = outer>";

    echo "<tr><th class = 'bg3' colspan = '3'  >" . _MD_XMAIL_HEADLINK . '</th></tr>';

    echo '<tr>';

    echo " <td class = 'even'><a href='index.php?op=form'>" . _MD_XMAIL_ENV . '</a></td>';

    echo " <td class = 'odd'>" . _MD_XMAIL_ENV2 . '</td>';

    echo '</tr>';

    //    if($isadmin) {

    echo '<tr>';

    echo " <td width='24%' class = 'even'><a href='gerencia.php'>" . _MD_XMAIL_ADM . '</a></td>';

    echo " <td class = 'odd'>" . _MD_XMAIL_ADM2 . '</td>';

    echo '</tr>';

    //    }

    echo '<tr>';

    echo "<td class = 'even'><a href='./submit.php?op=add'>" . _MD_XMAIL_CAD . '</a></td>';

    echo "<td class = 'odd'>" . _MD_XMAIL_CAD2 . '</td>';

    echo '</tr>';

    if ($isadmin) {
        echo '<tr>';

        echo "<td class = 'even'><a href='gerencia.php?op=apr'>" . _MD_XMAIL_APROV . '</a></td>';

        echo "<td class = 'odd'>" . _MD_XMAIL_APROV2 . '</td>';

        echo '</tr>';
    }

    echo '<tr>';

    echo "<td class = 'even'><a href='verlog.php'>" . _MD_XMAIL_LOG . '</a></td>';

    echo "<td class = 'odd'>" . _MD_XMAIL_LOG2 . '</td>';

    echo '</tr>';

    echo '<tr>';

    echo "<td class = 'even'><a href='gerencia_lote.php'>" . _MD_XMAIL_LOTES_P . '</a></td>';

    echo "<td class = 'odd'>" . _MD_XMAIL_LOTES_P2 . '</td>';

    echo '</tr>';

    echo '<tr>';

    echo "<td class = 'even'><a href='sendnews.php'>" . _MD_XMAIL_SENDNEWS . '</a></td>';

    echo "<td class = 'odd'>" . _MD_XMAIL_SENDNEWS2 . '</td>';

    echo '</tr>';

    echo '<tr>';

    echo "<td class = 'even'><a href='verlog_news.php'>" . _MD_XMAIL_LOGNEWS . '</a></td>';

    echo "<td class = 'odd'>" . _MD_XMAIL_LOGNEWS2 . '</td>';

    echo '</tr>';

    echo '</table>';
}

function myTextForm2($url, $value)
{
    return '<form action="' . $url . '" method="post"><input type="submit" value="' . $value . '"></form>';
}

function trecho_in_array($var, $array_var)
{
    // verifica se $var esta em algum trecho dentro da matriz $array_var

    // checks whether $var is present inside the array $array_var

    $retorno = false;

    for ($i = 0, $iMax = count($array_var); $i < $iMax; $i++) {
        if (mb_substr_count($array_var[$i], $var) > 0) {
            return true;
        }
    }

    return $retorno;
}

function getcorrectpath($path)
{
    if (file_exists(XOOPS_ROOT_PATH . '/' . $path)) {
        $ret = '      ' . _AM_XMAIL_PATHEXIST . ' ';
    } else {
        $ret = '      ' . _AM_XMAIL_PATHNOTEXIST . ' ';
    }

    return $ret;
}

function acerta_array($mat = '')
{
    if (!is_array($mat)) {
        return false;
    }  

    $mat_key = array_keys($mat);

    for ($i = 0, $iMax = count($mat); $i < $iMax; $i++) {
        $mat[$mat_key[$i]] = '(' . $mat_key[$i] . ') ' . $mat[$mat_key[$i]];
    }

    return $mat;
}

function get_icon($file)        ## Get the icon from the filename
{
    global $IconArray;

    reset($IconArray);

    $extension = mb_strtolower(mb_substr(mb_strrchr($file, '.'), 1));

    if ('' == $extension) {
        return 'unknown.gif';
    }

    while (list($icon, $types) = each($IconArray)) {
        foreach (explode(' ', $types) as $type) {
            if ($extension == $type) {
                return $icon;
            }
        }
    }

    return 'unknown.gif';
}

function PrettySize($size)
{
    $mb = 1024 * 1024;

    if ($size > $mb) {
        $mysize = sprintf('%01.2f', $size / $mb) . ' MB';
    } elseif ($size >= 1024) {
        $mysize = sprintf('%01.2f', $size / 1024) . ' KB';
    } else {
        $mysize = sprintf(_MD_XMAIL_NUMBYTES, $size);
    }

    return $mysize;
}

function show_preview($subject_men, $body_men)
{
    echo "<table  border='1'  bgcolor='ffffff'  > ";

    echo "<tr><td style='color: #000000' ><b>$subject_men </b> </td></tr> ";

    echo '<tr><td>   </td></tr> ';

    echo "<tr><td style='color: #000000' >" . $body_men . ' </td></tr> ';

    //           echo "<tr><td>". $myts->previewTarea($body_men)." </td></tr> ";
}

function getGroupIda_xmail($grps)
{
    $ret = [];

    if (!is_array($grps)) {
        $ret = explode(' ', $grps);
    }

    return $ret;
}

function saveAccess_xmail($grps)
{
    if (is_array($grps)) {
        $grps = implode(' ', $grps);
    }

    return ($grps);
}

// nova versão 2.0

function get_novo_lote()
{
    global $xoopsDB;

    $sql = 'select max(lote_solicit) as lote from ' . $xoopsDB->prefix('xmail_aux_send_l');

    $result = $xoopsDB->queryF($sql);

    if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
        return false;
    }

    $cat_data = $xoopsDB->fetchArray($result);

    return $cat_data['lote'] + 1;
}

function envia_xmails_lote($lote, $dest = '', $mail_start = 0)
{
    // resgatar os dados gravados no lote $lote

    // $dest -> lista de visitantes separados por vírgula, se não for informado, deverá resgatar

    // todos do lote

    global $xoopsDB;

    $class_l = new classxmail_aux_send_l();

    $class_l->lote_solicit = $lote;

    $class_l->busca();

    $mail_send_to = explode(',', $class_l->mail_send_to);

    if (empty($dest)) {
        $dest = implode(',', $class_l->array_users);
    }

    envia_xmails('', '', $dest, $mail_start, $class_l->mail_fromname, $class_l->mail_fromemail, $mail_send_to, $class_l->id_men, 1, $lote, $class_l->email_conf, $class_l->is_new);

    //envia_xmails($mail_subject,$mail_body,$dest='',$mail_start=0,$mail_fromname='',$mail_fromemail='',$mail_send_to='' ,$id_men=0,$grvlog=1) {
}

function envia_email_ativa($touser, $user_logado, $xoopsConfigUser, $xoopsConfig)
{
    //Enviando email de ativação de conta

    // $touser => objeto xoopsuser do usuario destinatário, pode ser array com vários objetos

    if (is_array($touser)) {
        foreach ($touser as $user) {
            envia_email_ativa($user, $user_logado, $xoopsConfigUser, $xoopsConfig);
        }// fecha foreach

        return;
    }

    require_once XOOPS_ROOT_PATH . '/modules/xmail/include/class_ativacao.php';

    $classativa = new Xmail_ativacao();

    $classativa->dt_envio = time();

    $classativa->user_logado = $user_logado;

    $classativa->activation_type = $xoopsConfigUser['activation_type'];

    $tentativas = $classativa->get_tentativas();

    $xoopsMailer = getMailer();

    $xoopsMailer->useMail();

    $xoopsMailer->setTemplateDir(XOOPS_ROOT_PATH . '/modules/xmail/language/' . $xoopsConfig['language'] . '/mail_template/');

    if (0 == $xoopsConfigUser['activation_type']) {
        $xoopsMailer->setTemplate('register.tpl');

        $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);

        $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);

        $xoopsMailer->assign('SITEURL', XOOPS_URL . '/');

        $xoopsMailer->assign('TENTATIVAS', $tentativas);

        $xoopsMailer->setToUsers($touser);

        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);

        $xoopsMailer->setFromName($xoopsConfig['sitename']);

        $xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $touser->getVar('uname')));

        if (!$xoopsMailer->send(true)) {
            xoops_error($xoopsMailer->getErrors());

            return;
        }  

        xoops_result(sprintf(_MD_XMAIL_REENVIO_OK, $touser->getVar('uname')));

        if (!$classativa->incluir($touser)) {
            xoops_error(_MD_XMAIL_ERRORSAVINGDB . '<br>' . $men_erro);
        }

        return;
    } elseif (2 == $xoopsConfigUser['activation_type']) {
        $xoopsMailer->setTemplate('adminactivate.tpl');

        $xoopsMailer->assign('USERNAME', $touser->getVar('uname'));

        $xoopsMailer->assign('USEREMAIL', $touser->getVar('email'));

        $xoopsMailer->assign('USERACTLINK', XOOPS_URL . '/user.php?op=actv&id=' . $touser->getVar('uid') . '&actkey=' . $touser->getVar('actkey'));

        $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);

        $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);

        $xoopsMailer->assign('SITEURL', XOOPS_URL . '/');

        $xoopsMailer->assign('TENTATIVAS', $tentativas);

        $memberHandler = xoops_getHandler('member');

        $xoopsMailer->setToGroups($memberHandler->getGroup($xoopsConfigUser['activation_group']));

        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);

        $xoopsMailer->setFromName($xoopsConfig['sitename']);

        $xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $touser->getVar('uname')));

        //			OpenTable();

        if (!$xoopsMailer->send(true)) {
            xoops_error($xoopsMailer->getErrors() . '<br>' . sprintf(_MD_XMAIL_ERRENVIOLINK, _MD_XMAIL_ADMIN));

            return;
        }  

        xoops_result(sprintf(_MD_XMAIL_REENVIO_OK, _MD_XMAIL_ADMIN));

        if (!$classativa->incluir($touser)) {
            xoops_error(_MD_XMAIL_ERRORSAVINGDB . '<br>' . $men_erro);
        }

        return;
    }
}

// nova versão
function &xmail_getWysiwygForm($xmail_form, $caption, $name, $value = '', $width = '100%', $height = '400px')
{
    $editor = false;

    switch (mb_strtolower($xmail_form)) {
        case 'spaw':
            if (is_readable(XOOPS_ROOT_PATH . '/class/spaw/formspaw.php')) {
                require_once XOOPS_ROOT_PATH . '/class/spaw/formspaw.php';

                $editor = new XoopsFormSpaw($caption, $name, $value, $width, $height);
            }
            break;
        case 'fck':
            if (is_readable(XOOPS_ROOT_PATH . '/class/fckeditor/formfckeditor.php')) {
                require_once XOOPS_ROOT_PATH . '/class/fckeditor/formfckeditor.php';

                $editor = new XoopsFormFckeditor($caption, $name, $value, $width, $height);
            }
            break;
        case 'htmlarea':
            if (is_readable(XOOPS_ROOT_PATH . '/class/htmlarea/formhtmlarea.php')) {
                require_once XOOPS_ROOT_PATH . '/class/htmlarea/formhtmlarea.php';

                $editor = new XoopsFormHtmlarea($caption, $name, $value, $width, $height);
            }
            break;
        case 'koivi':
            if (is_readable(XOOPS_ROOT_PATH . '/class/wysiwyg/formwysiwygtextarea.php')) {
                require_once XOOPS_ROOT_PATH . '/class/wysiwyg/formwysiwygtextarea.php';

                $editor = new XoopsFormWysiwygTextArea($caption, $name, $value, $width, $height, '');
            }
            break;
        case 'tinymce':
            if (is_readable(XOOPS_ROOT_PATH . '/class/tinymce/formtinymce.php')) {
                require_once XOOPS_ROOT_PATH . '/class/tinymce/formtinymce.php';

                $editor = new XoopsFormTinymce($caption, $name, $value, $width, $height);
            }
            break;
    }

    return $editor;
}

function &xmail_getTextareaForm($xmail_form, $caption, $name, $value = '', $rows = 25, $cols = 60)
{
    switch (mb_strtolower($xmail_form)) {
        case 'textarea':
            $form = new XoopsFormTextArea($caption, $name, $value, $rows, $cols);
            break;
        case 'dhtml':
        default:
            $form = new XoopsFormDhtmlTextArea($caption, $name, $value, $rows, $cols);
            break;
    }

    return $form;
}



