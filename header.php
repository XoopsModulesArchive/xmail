<?php
/*
* $Id: header.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

include '../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/functions.php';
// funções do groupaccess.php  entra em conflito quando tem o wfsection instalado
// as func que eram usadas foram para o arquivo functions.php  (saveAccess_xmail  e getGroupIda_xmail)
//require_once XOOPS_ROOT_PATH."/modules/xmail/include/groupaccess.php";
require XOOPS_ROOT_PATH . '/class/xoopstree.php';
require XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmodule.php';

require_once XOOPS_ROOT_PATH . '/modules/xmail/include/classparam.php';
$myts = MyTextSanitizer:: getInstance();
if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/', 3, _NOPERM);

    exit();
}

// checar se user é admin
//	$xoopsModule = XoopsModule::getByDirname($modversion['dirname']);
// acima não deu certo pois não reconhece $modversion  -  verificar qual melhor forma de fazer
//  sem colocar explicitamente  xmail  e sim resgatar com função
//  Couldn't make work as above since $modversion isn't recognized. To do: check the
//  best way to set $xoopsModule without the need to input "xmail", i.e., fetch it with
//  a function as in the commented line above.

$xoopsModule = XoopsModule::getByDirname('xmail');
if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
    $isadmin = false;
} else {
    $isadmin = true;
}
