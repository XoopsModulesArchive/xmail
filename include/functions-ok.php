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

function envia_xmails($mail_subject, $mail_body, $dest = '', $mail_start = 0, $mail_fromname = '', $mail_fromemail = '', $mail_send_to = '', $id_men = 0, $grvlog = 1)
{
    global $xoopsUser, $xoopsDB;

    // $grvlog=1  indica para gravar na tabela de log xmail_send_log

    // $grvlog=1  indication for logging in table xmail_send_log

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

            $result = $xoopsDB->query('SELECT subject_men, body_men FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where id_men=' . $id_men);

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

    foreach ($dest as $to_user) {
        if (!in_array($to_user, $added_id, true)) {
            $added[] = new XoopsUser($to_user);

            $added_id[] = $to_user;
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

            if ((in_array('pref', $mail_send_to, true) and 2 == $objuser->getVar('notify_method')) or (!in_array('pref', $mail_send_to, true) and in_array('mail', $mail_send_to, true))) {
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

            $xoopsMailer->setBody($myts->displayTarea($mail_body));

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

        for ($i = 0, $iMax = count($xoopsMailer->toUsers); $i < $iMax; $i++) {
            $obj_user = $xoopsMailer->toUsers[$i];

            if (trecho_in_array($obj_user->getVar('uname'), $xoopsMailer->success)) {
                $sql = 'INSERT INTO  ' . $xoopsDB->prefix('xmail_send_log') . " (id_user,id_men,dt_envio)
                     values ('" . $obj_user->getVar('uid') . "','" . $id_men . "','" . $dt_envio . "')";

                $result = $xoopsDB->query($sql);

                if (!$result) {
                    echo "<div class='errorMsg' >" . _MD_XMAIL_ERRGRVLOG . ' </div> ';
                }
            }
        } // fecha for / close for

        //  fim da gravação do log / end of logging

        // atualizar no cadastro da mensagem data de envio

        // update sent date in message record

        if (count($xoopsMailer->success) > 0) {
            $result = $xoopsDB->query('UPDATE ' . $xoopsDB->prefix('xmail_mensage') . " set date_envio='$dt_envio' where id_men='$id_men'");

            if (!$result) {
                echo "<div class='errorMsg' >" . _MD_XMAIL_ERRDTENV . ' </div> ';
            }
        }

        if ($added_count > $mail_end) {
            $form = new XoopsThemeForm(_MD_XMAIL_SENDTO, 'mailusers', $_SERVER['PHP_SELF'] . '?opt=MG');

            $submit_button = new XoopsFormButton('', 'mail_submit', _MD_XMAIL_NEXT, 'submit');

            $sent_label = new XoopsFormLabel(_MD_XMAIL_ENVIADO, sprintf(_MD_XMAIL_SENTNUM, $mail_start + 1, $mail_end, $added_count));

            $fname_hidden = new XoopsFormHidden('mail_fromname', htmlspecialchars($mail_fromname, ENT_QUOTES | ENT_HTML5));

            $femail_hidden = new XoopsFormHidden('mail_fromemail', htmlspecialchars($mail_fromemail, ENT_QUOTES | ENT_HTML5));

            $subject_hidden = new XoopsFormHidden('mail_subject', htmlspecialchars($mail_subject, ENT_QUOTES | ENT_HTML5));

            $body_hidden = new XoopsFormHidden('mail_body', htmlspecialchars($mail_body, ENT_QUOTES | ENT_HTML5));

            $start_hidden = new XoopsFormHidden('mail_start', $mail_end);

            $op_hidden = new XoopsFormHidden('op', 'send');

            $id_men_hidden = new XoopsFormHidden('id_men', $id_men);

            // técnica para passar matriz via post

            // technique to pass an array thru post

            if (!empty($mail_send_to)) {
                foreach ($mail_send_to as $mail) {
                    $to_hidden = new XoopsFormHidden('mail_send_to[]', $mail);

                    $form->addElement($to_hidden);
                }
            }

            $dest_hidden = new XoopsFormHidden('dest', implode(',', $dest));

            $form->addElement($sent_label);

            $form->addElement($fname_hidden);

            $form->addElement($femail_hidden);

            $form->addElement($subject_hidden);

            $form->addElement($body_hidden);

            $form->addElement($start_hidden);

            $form->addElement($dest_hidden);

            $form->addElement($op_hidden);

            $form->addElement($id_men_hidden);

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



