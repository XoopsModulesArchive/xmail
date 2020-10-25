<?php
/*
* $Id: UPGRADE1.06.php
* Module: XMAIL
* Version: v1.07
* Release Date: 02 Dezembro  2003
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include 'header.php';

global $xoopsDB, $xoopsConfig;
require XOOPS_ROOT_PATH . '/header.php';

function install_header()
{
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>XMAIL Upgrade</title>
    <meta http-equiv="Content-Type" content="text/html; charset=">
</head>
<body>
<br><br>
<div style='text-align:center'><img src='images/logo-xmail.png'><h4>XMAIL Upgrade</h4>
    <?php
}

    function install_footer()
    {
        ?>
    <br><br><img src='images/logo-xmail.png'></div>
</body>
</html>
<?php
    }

//echo "Welcome to the xmail  upgrade script";

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

foreach ($_GET as $k => $v) {
    ${$k} = $v;
}

if (!isset($action) || '' == $action) {
    $action = 'message';
}

if ('message' == $action) {
    install_header();

    echo "
  <table width='100%' border='0'>
  <tr>
    <td align='center'><b>" . _MD_XMAIL_UPDATE1 . '</b></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>';

    echo "
	<table width='50%' border='0'><tr><td colspan='2'>" . _MD_XMAIL_UPDATE2 . '<br><br><b>' . _MD_XMAIL_UPDATE3 . '<b></td></tr>

	<tr><td></td><td >' . _MD_XMAIL_UPDATE4 . "</td></tr>
	<tr><td></td><td><span style='color:#ff0000;font-weight:bold;'>" . _MD_XMAIL_UPDATE5 . '</span></td></tr>
	</table>
	';

    echo '<p>' . _MD_XMAIL_UPDATE6 . '</p>';

    echo "<form action='" . $HTTP_SERVER_VARS['PHP_SELF'] . "' method='post'><input type='submit' value=" . _MD_XMAIL_UPDATE7 . "><input type='hidden' value='upgrade' name='action'></form>";

    install_footer();

    require_once XOOPS_ROOT_PATH . '/footer.php';

    //	exit();
}

//  THIS IS THE UPDATE DATABASE FROM HERE!!!!!!!!! DO NOT TOUCH THIS!!!!!!!!

if ('upgrade' == $action) {
    install_header();

    echo '<p>' . _MD_XMAIL_UPDATE24 . "</p>\n";

    $count = 0;

    $sql = 'ALTER TABLE ' . $xoopsDB->prefix('xmail_param') . " ADD ordem_admin char(2) NOT NULL default 'C' ";

    $result = $xoopsDB->queryF($sql);

    if ($result) {
        echo 'Adding ordem_admin  from ' . $xoopsDB->prefix('xmail_param') . '.<br>';

        $count++;
    } else {
        echo "erro na sql $sql";
    }

    $sql = 'ALTER TABLE ' . $xoopsDB->prefix('xmail_param') . " ADD limite_page tinyint(4) NOT NULL default '10' ";

    $result = $xoopsDB->queryF($sql);

    if ($result) {
        echo 'Adding limite_page to ' . $xoopsDB->prefix('xmail_param') . '.<br>';

        $count++;
    } else {
        echo "erro na sql $sql";
    }

    if (0 == $count) {
        echo '<div>' . _MD_XMAIL_UPDATE25 . '</div>';
    } else {
        echo '<br>';

        echo '' . _MD_XMAIL_UPDATE22 . '';
    }

    echo '<p><span> ' . _MD_XMAIL_UPDATE23 . "</span></p>\n";

    require_once XOOPS_ROOT_PATH . '/footer.php';
}
?>

