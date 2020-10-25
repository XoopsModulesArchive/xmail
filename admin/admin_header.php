<?php
/*
* admin_header.php
* Module: XMAIL
* Version: v2.0
* Release Date: 17 Fevereiro  2004
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include '../../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';
require XOOPS_ROOT_PATH . '/include/cp_functions.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/functions.php';

require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classparam.php';

require XOOPS_ROOT_PATH . '/class/xoopstree.php';
require XOOPS_ROOT_PATH . '/class/xoopslists.php';
require XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/groupaccess.php';
//require_once XOOPS_ROOT_PATH."/include/groupaccess.php";

if ($xoopsUser) {
    $xoopsModule = XoopsModule::getByDirname('xmail');

    if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
        redirect_header(XOOPS_URL . '/', 3, _NOPERM);

        exit();
    }
} else {
    redirect_header(XOOPS_URL . '/', 3, _NOPERM);

    exit();
}
if (file_exists('../language/' . $xoopsConfig['language'] . '/admin.php')) {
    include '../language/' . $xoopsConfig['language'] . '/admin.php';
} else {
    include '../language/english/admin.php';
}
if (file_exists('../language/' . $xoopsConfig['language'] . '/main.php')) {
    include '../language/' . $xoopsConfig['language'] . '/main.php';
} else {
    include '../language/english/main.php';
}

$myts = MyTextSanitizer::getInstance();


