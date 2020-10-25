<?php
/*
* $Id: vermem.php
* Module: XMAIL
** Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

//error_reporting(E_ALL);
include '../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classfiles.php';
if (!is_object($xoopsUser)) {
    exit('Access Denied');
}

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

foreach ($_GET as $k => $v) {
    ${$k} = $v;
}

$result = $xoopsDB->query('SELECT subject_men, body_men ,dohtml ,dobr FROM ' . $xoopsDB->prefix('xmail_mensage') . ' where id_men=' . $id_men);
if ($result) {
    $myts = MyTextSanitizer::getInstance();

    $query_data = $GLOBALS['xoopsDB']->fetchBoth($result, MYSQL_ASSOC);

    $mail_subject = $query_data['subject_men'];

    $mail_body = $query_data['body_men'];

    $mail_subject = $myts->stripSlashesGPC($mail_subject);

    $mail_body = $myts->displayTarea($mail_body, $query_data['dohtml'], 1, 1, 1, $query_data['dobr']);

    // verificar arquivos anexos

    $classfiles = new classfiles();

    $arqs = $classfiles->array_anexos($id_men);

    $anexos = '<br><b>Attachment Files: </b>';

    for ($i = 0, $iMax = count($arqs); $i < $iMax; $i++) {
        if ($i > 0) {
            $anexos .= ' , ';
        }

        $anexos .= '   ' . ($arqs[$i]['filerealname']);
    }

    $mail_body .= '<br> ' . $anexos;
} else {
    $mail_body = _MD_XMAIL_NOTFOUND;

    $mail_subject = '';
}

//$css = getCss($theme);
echo "<html>\n<head>\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _CHARSET . "\"></meta>\n";
echo '<title>' . sprintf(_MD_XMAIL_VERMENTIT . '  ', $id_men) . "</title>\n";

echo "</head>\n";
echo "<body >\n";

//echo "ola estou testando veja id_men  $id_men";

echo '<div> <b> ' . _MD_XMAIL_MAILSUBJECT . "</b>&nbsp; $mail_subject<br><br> ";
echo '<div> <b> ' . _MD_XMAIL_MAILBODY . " </b><br> $mail_body<br><br> ";

echo '</body></html>';


