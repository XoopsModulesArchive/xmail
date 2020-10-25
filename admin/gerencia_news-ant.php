<?php
/*
* $Id: admin/gerencia_news.php
* Module: XMAIL
** Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

require_once dirname(__DIR__, 3) . '/include/cp_header.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmailer.php';
//require_once __DIR__ . '/admin_header.php';

error_reporting(E_ALL);
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action']) && !isset($_POST['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}
//$adminURL = XOOPS_URL .'/modules/'.$xoopsModule->dirname().'/admin/index.php';

$adminURL = xoops_getenv('PHP_SELF');

switch ($action) {
    case 'import_users':
        importusers();
        break;
    case 'rem_user_conf':
        removeUser();
        break;
    case 'optimize':
        optimizeTable($xoopsDB->prefix('xmail_newsletter'));
        break;
    case 'launch_import':
        launchimport();
        break;
    case 'rem_user':
        remUser();
        break;
    default:
        xoops_cp_header();
        OpenTable();
        showHeader();
        CloseTable();
        xoops_cp_footer();
        break;
}

// Ajout Hervé
function launchimport()  // ok
{
    global $xoopsDB, $xoopsUser;

    xoops_cp_header();

    OpenTable();

    showHeader();

    $imported = 0;

    while (list($null, $userid) = each($_POST['userslist'])) {
        $sql = 'SELECT count(user_id) as cpt from ' . $xoopsDB->prefix('xmail_newsletter') . " WHERE user_id=$userid";

        $arr = $xoopsDB->fetchArray($xoopsDB->queryF($sql));

        if (0 == $arr['cpt']) {    // The user is not in the table
            // Search user

            $sqluser = 'SELECT name, uname, user_regdate, email, user_mailok FROM ' . $xoopsDB->prefix('users') . " WHERE uid= $userid";

            $arruser = $xoopsDB->fetchArray($xoopsDB->queryF($sqluser));

            if (trim('' != $arruser['email'])) {
                if (1 == $arruser['user_mailok']) {    // User accepts emails
                    $better_token = md5(uniqid(mt_rand(), 1));

                    $sqlinsert = sprintf(
                        "INSERT INTO %s (user_id, user_name, user_nick, user_email, user_host, user_conf, confirmed, user_time) VALUES (%u ,'%s' ,'%s', '%s', '%s', '%s', '1' ,NOW())",
                        $xoopsDB->prefix('xmail_newsletter'),
                        $userid,
                        $arruser['name'],
                        $arruser['uname'],
                        $arruser['email'],
                        '',
                        $better_token
                    );

                    if (!$resultinsert = $xoopsDB->queryF($sqlinsert)) {
                        printf(_AM_XMAIL_USERSMSG5, $xoopsUser->getUnameFromId($userid));
                    } else {    // User inserted successfully
                        printf(_AM_XMAIL_USERSMSG4, $xoopsUser->getUnameFromId($userid));
                    }
                } else {
                    printf(_AM_XMAIL_USERSMSG3, $xoopsUser->getUnameFromId($userid));
                }
            } else {    // Empty email adress
                printf(_AM_XMAIL_USERSMSG2, $xoopsUser->getUnameFromId($userid));
            }
        } else {    // User already present in the table
            printf(_AM_XMAIL_USERSMSG1, $xoopsUser->getUnameFromId($userid));
        }
    }

    CloseTable();

    xoops_cp_footer();
}

// Ajout Hervé
function importusers()  //  ok
{
    global $xoopsDB, $xoopsModule;

    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    xoops_cp_header();

    OpenTable();

    showHeader();

    $sform = new XoopsThemeForm(_AM_XMAIL_IMPORTUSER, 'importform', $adminURL);

    $sform->addElement(new XoopsFormSelectUser(_AM_XMAIL_MSGIMPORTUSER, 'userslist', false, '', 10, true), true);

    $sform->addElement(new XoopsFormHidden('action', 'launch_import'), false);

    $button_tray = new XoopsFormElementTray('', '');

    $submit_btn = new XoopsFormButton('', 'submit', _AM_XMAIL_BNTIMPORTUSEROK, 'submit');

    $button_tray->addElement($submit_btn);

    $cancel_btn = new XoopsFormButton('', 'reset', _AM_XMAIL_BNTIMPORTUSERCANCEL, 'reset');

    $button_tray->addElement($cancel_btn);

    $sform->addElement($button_tray);

    $sform->display();

    CloseTable();

    xoops_cp_footer();
}

function removeUser()
{  //  ok
    global $xoopsDB;

    $query = 'UPDATE ' . $xoopsDB->prefix('xmail_newsletter') . " SET confirmed='0' WHERE user_conf='" . $_POST['user_conf'] . "'";

    $result = $xoopsDB->queryF($query);

    xoops_cp_header();

    OpenTable();

    showHeader();

    if (!$result) {
        printf(_AM_XMAIL_DBERROR, $result, $query);
    }

    printf(_AM_XMAIL_USERREMOVED, $_POST['user_name']);

    CloseTable();

    xoops_cp_footer();
}

function optimizeTable($tablename)
{  //  ok
    global $xoopsDB;

    $query = "OPTIMIZE TABLE $tablename";

    $result = $xoopsDB->queryF($query);

    xoops_cp_header();

    OpenTable();

    showHeader();

    if (!$result) {
        printf(_AM_XMAIL_DBERROR, $result, $query);
    } else {
        printf(_AM_XMAIL_TABLEOPT, $tablename);
    }

    CloseTable();

    xoops_cp_footer();
}

function showHeader()
{
    global $xoopsModule, $adminURL;

    print "<center><table width='70%' bgcolor='white' border='1' cols='2' rows='2' cellpadding='2' cellspacing='0'>\n";

    print "<th colspan='2'>" . _AM_XMAIL_ADMINMENUNEWS . "</th>\n";

    print '<tr>';

    print "<td><a href='$adminURL?action=rem_user'>" . _AM_XMAIL_REMOVEUSER . "</a></td>\n";

    print "<td><a href='$adminURL?action=optimize'>" . _AM_XMAIL_OPTIMDATAB . "</a></td>\n";

    print '</tr>';

    print "<td colspan=2><a href='$adminURL?action=import_users'>" . _AM_XMAIL_IMPORTUSER . "</a></td>\n";

    print "</table></center><BR>\n";
}

function remUser()  //  ok
{
    global $xoopsDB, $adminURL;

    $query = 'select * from ' . $xoopsDB->prefix('xmail_newsletter');

    $result = $xoopsDB->queryF($query);

    xoops_cp_header();

    OpenTable();

    showHeader();

    echo '<h2>' . _AM_XMAIL_REMOVEUSER . '</h2>';

    echo "<center><table width='90%' border='1' cellpadding='0' cellspacing='0'>\n";

    echo '<th>' . _AM_XMAIL_CONFIRMED . '</th><th>' . _AM_XMAIL_USERID . '</th><th>' . _AM_XMAIL_USERNAME . '</th><th>' . _AM_XMAIL_NICKNAME . '</th><th>' . _AM_XMAIL_EMAIL . '</th>';

    echo '<th>' . _AM_XMAIL_HOST . '</th><th>' . _AM_XMAIL_TIME . '</th><th>' . _AM_XMAIL_DELETEUSER . "</th>\n";

    if (!$result) {
        echo '<tr><td>' . _AM_XMAIL_NOTHINGINDB . " ?</td></tr>\n";
    } else {
        while (false !== ($arr = $xoopsDB->fetchArray($result))) {
            $mail = $arr['user_email'];

            if (!$mail) {
                $mail = "<font color='red'>" . _AM_XMAIL_ERROR . '</font>';
            }

            $conf = '';

            if ('1' == $arr['confirmed']) {
                $conf = _AM_XMAIL_YES;
            } else {
                $conf = _AM_XMAIL_NO;
            }

            echo "<tr>\n";

            echo "<td>$conf</td>\n";

            echo '<td>' . $arr['user_id'] . "</td>\n";

            echo '<td>' . $arr['user_name'] . "</td>\n";

            echo '<td>' . $arr['user_nick'] . "</td>\n";

            echo "<td>$mail</td>\n";

            echo '<td>' . $arr['user_host'] . "&nbsp;</td>\n";

            [$year, $month, $day, $hour, $min, $sec] = explode(':', eregi_replace("[' '|-]", ':', $arr['user_time']));

            echo '<td nowrap>' . formatTimestamp(mktime($hour, $min, $sec, $month, $day, $year)) . "</td>\n";

            echo "<form action='$adminURL' method='post'>\n";

            echo "<input type='hidden' name='user_id' value='" . $arr['user_id'] . "'>\n";

            echo "<input type='hidden' name='user_name' value='" . $arr['user_name'] . "'>\n";

            echo "<input type='hidden' name='user_nick' value='" . $arr['user_nick'] . "'>\n";

            echo "<input type='hidden' name='user_email' value='" . $arr['user_email'] . "'>\n";

            echo "<input type='hidden' name='user_host' value='" . $arr['user_host'] . "'>\n";

            echo "<input type='hidden' name='user_conf' value='" . $arr['user_conf'] . "'>\n";

            echo "<input type='hidden' name='user_time' value='" . $arr['user_time'] . "'>\n";

            echo "<input type='hidden' name='action' value='rem_user_conf'>\n";

            echo "<input type='hidden' name='confirmed' value='" . $arr['confirmed'] . "'>\n";

            echo "<td nowrap><input type='submit' name='rem_user_conf' value='" . _AM_XMAIL_DELETEUSER . "'></td>\n";

            echo "</form>\n";

            echo "</tr>\n";
        }
    }

    echo "</table>\n";

    echo "</center>\n";

    CloseTable();

    xoops_cp_footer();
}
