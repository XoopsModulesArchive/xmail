<?php

require __DIR__ . '/header.php';

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

foreach ($_GET as $k => $v) {
    ${$k} = $v;
}

if (empty($t)) {
    redirect_header('index.php');
}

function PrintPage($t)
{
    global $xoopsConfig, $xoopsDB, $xoopsModule, $myts;

    $result = $xoopsDB->queryF('SELECT * FROM ' . $xoopsDB->prefix('faqtopics') . " WHERE topicID = '$t' and submit = '1' order by datesub");

    [$topicID, $catID, $question, $answer, $summary, $uid, $submit, $datesub, $counter, $weight, $groupid, $html, $smiley, $xcodes] = $xoopsDB->fetchRow($result);

    $datetime = formatTimestamp($datesub, 'D, d-M-Y, H:i');

    $answer = $myts->displayTarea($answer);

    echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";

    echo "<html>\n<head>\n";

    echo '<title>' . $xoopsConfig['sitename'] . "</title>\n";

    echo "<meta http-equiv='Content-Type' content='text/html; charset=" . _CHARSET . "'>\n";

    echo "<meta name='AUTHOR' content='" . $xoopsConfig['sitename'] . "'>\n";

    echo "<meta name='COPYRIGHT' content='Copyright (c) 2001 by " . $xoopsConfig['sitename'] . "'>\n";

    echo "<meta name='DESCRIPTION' content='" . $xoopsConfig['slogan'] . "'>\n";

    echo "<meta name='GENERATOR' content='" . XOOPS_VERSION . "'>\n\n\n";

    echo "<body bgcolor='#ffffff' text='#000000?>          <table border='0'><tr><td align='center?>          <table border='0' width='650' cellpadding='0' cellspacing='1' bgcolor='#000000'><tr><t?>          <table border='0' width='650' cellpadding='20' cellspacing='1' bgcolor='#ffffff'><tr><td align='center?>          <img src='"
         . XOOPS_URL
         . '/modules/'
         . $xoopsModule->dirname()
         . "/images/logo.gif' border='0' alt=''><br ?>          <h2>"
         . $question
         . '</h2><hr>';

    echo '<tr><td>' . $answer . '<br><br><br><hr><br>';

    echo '<small><b>Date: </b>&nbsp;' . $datetime . '<br>';

    //echo "<b>". _WFS_TOPICC . "</b>&nbsp;" . $catID . "<br>";

    echo "</td></tr></table></td></tr></table>\n
          </td></tr></tabl?>          </bod?>          </html>";
}

PrintPage($t);
