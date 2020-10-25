<?php
/*
* $Id: installscript.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

function xoops_module_install_xmail($module)
{
    // copiar arquivo de esquema dos dados (em xml) para .antes

    $dir_array = $module->getVars('dirname');

    $dirname = $dir_array['dirname']['value'];

    $file = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/table_' . $dirname . '_esquema.xml';

    if (!copy($file, $file . '.antes')) {
        return false;
    }
  

    return true;
}




