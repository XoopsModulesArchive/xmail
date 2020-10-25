<?php
/*
* $Id: admin/index.php
* Module: XMAIL
** Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

//include "admin_header.php";

require_once dirname(__DIR__, 3) . '/include/cp_header.php';
xoops_cp_header();
OpenTable();
print "<center><table width='70%' bgcolor='white' border='1' cols='2' rows='2' cellpadding='2' cellspacing='0'>\n";
print "<th colspan='2'>" . _AM_XMAIL_ADMINMENUXMAIL . "</th>\n";
print '<tr>';
print "<td><a href='" . XOOPS_URL . "/modules/xmail/gerencia.php?op=apr'>" . _AM_XMAIL_ADMENU2 . "</a></td>\n";
print "<td><a href='" . XOOPS_URL . "/modules/xmail/verlog.php'>" . _AM_XMAIL_ADMENU3 . "</a></td>\n";
print '</tr>';
print '<tr>';
print "<td><a href='" . XOOPS_URL . "/modules/xmail/admin/param.php'>" . _AM_XMAIL_ADMENU5 . "</a></td>\n";
print "<td><a href='" . XOOPS_URL . "/modules/xmail/admin/gerencia_ativa.php'>" . _AM_XMAIL_ADMENU4 . "</a></td>\n";
print '</tr>';

print "<tr><td><a href='" . XOOPS_URL . "/modules/xmail/index.php'>" . _AM_XMAIL_ADMENU1 . "</a></td>\n";
print "<td><a href='" . XOOPS_URL . "/modules/xmail/admin/gerencia_news.php'>" . _AM_XMAIL_ADMENU6 . "</a></td></tr>\n";

print "<tr><td colspan='2' ><a href='" . XOOPS_URL . "/modules/xmail/admin/manut_xmail_tabperfil.php'>" . _AM_XMAIL_ADMENU7 . "</a></td>\n";
print "</tr>\n";

print "</table></center><BR>\n";
CloseTable();

xoops_cp_footer();


