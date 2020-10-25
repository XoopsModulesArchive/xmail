<?php
// $Id: spaw_control.class.php, V 1.0 phppp Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * Adapted SPAW editor
 *
 * @author        phppp, http://xoops.org.cn
 * @copyright     copyright (c) 2004 XOOPS.org
 */
// ================================================
// SPAW PHP WYSIWYG editor control
// ================================================
// Configuration file
// ================================================
// Developed: Alan Mendelevich, alan@solmetra.lt
// Copyright: Solmetra (c)2003 All rights reserved.
// ------------------------------------------------
//                                www.solmetra.com
// ================================================
// v.1.0, 2003-03-27
// ================================================

/*********************************************************
 * In order to call the spaw from inside a function, the following parameters must be $GLOBAL
 */
global $spaw_dir, $spaw_base_url, $spaw_root, $spaw_default_toolbars, $spaw_default_theme, $spaw_default_lang, $spaw_default_css_stylesheet, $spaw_inline_js, $spaw_active_toolbar, $spaw_dropdown_data, $spaw_valid_imgs, $spaw_upload_allowed, $spaw_img_delete_allowed, $spaw_imglibs, $spaw_imglib_include, $spaw_a_targets, $spaw_img_popup_url, $spaw_internal_link_script, $spaw_disable_style_controls;
//
//
///////////////////////////////////////////////////////////////////////////////
if (!defined('XOOPS_ROOT_PATH')) {
    require_once dirname(__DIR__, 3) . '/mainfile.php';
}
// directory where spaw files are located
// $spaw_dir = 'spaw/';
$spaw_dir = XOOPS_URL . '/class/spaw/';

// base url for images
$spaw_base_url = XOOPS_URL . '/';
$spaw_root = XOOPS_ROOT_PATH . '/class/spaw/';
//Spaw Theme settings
$spaw_default_toolbars = 'default';
$spaw_default_theme = 'default';
$spaw_default_lang = 'en';
$spaw_default_css_stylesheet = $spaw_dir . 'wysiwyg.css';

// add javascript inline or via separate file
$spaw_inline_js = false;

// use active toolbar (reflecting current style) or static
$spaw_active_toolbar = true;

// default dropdown content
$spaw_dropdown_data['style']['default'] = 'normal';

$spaw_dropdown_data['table_style']['default'] = 'normal';

$spaw_dropdown_data['td_style']['default'] = 'normal';

/* For Chinese only
 */
if ('gb2312' == mb_strtolower(_CHARSET)) {
    $spaw_dropdown_data['font']['simsun'] = 'ËÎÌå';

    $spaw_dropdown_data['font']['simhei'] = 'ºÚÌå';

    $spaw_dropdown_data['font']['simkai'] = '¿¬Ìå';

    $spaw_dropdown_data['font']['simfang'] = '·ÂËÎ';
}

$spaw_dropdown_data['font']['Arial'] = 'Arial';
$spaw_dropdown_data['font']['Arial Black'] = 'Arial Black';
$spaw_dropdown_data['font']['Arial Narrow'] = 'Arial Narrow';
$spaw_dropdown_data['font']['Century Gothic'] = 'Century Gothic';
$spaw_dropdown_data['font']['Courier'] = 'Courier';
$spaw_dropdown_data['font']['Comic Sans MS'] = 'Comic Sans MS';
$spaw_dropdown_data['font']['Courier New'] = 'Courier New';
$spaw_dropdown_data['font']['Fixedsys'] = 'Fixedsys';
$spaw_dropdown_data['font']['Impact'] = 'Impact';
$spaw_dropdown_data['font']['Lucida Console'] = 'Lucida Console';
$spaw_dropdown_data['font']['Modern'] = 'Modern';
$spaw_dropdown_data['font']['monospace'] = 'monospace';
$spaw_dropdown_data['font']['MS Sans Serif'] = 'MS Sans Serif';
$spaw_dropdown_data['font']['MS Serif'] = 'MS Serif';
$spaw_dropdown_data['font']['sans-serif'] = 'sans-serif';
$spaw_dropdown_data['font']['System'] = 'System';
$spaw_dropdown_data['font']['Tahoma'] = 'Tahoma';
$spaw_dropdown_data['font']['Terminal'] = 'Terminal';
$spaw_dropdown_data['font']['Times New Roman'] = 'Times';
$spaw_dropdown_data['font']['Verdana'] = 'Verdana';

$spaw_dropdown_data['fontsize']['1'] = '1';
$spaw_dropdown_data['fontsize']['2'] = '2';
$spaw_dropdown_data['fontsize']['3'] = '3';
$spaw_dropdown_data['fontsize']['4'] = '4';
$spaw_dropdown_data['fontsize']['5'] = '5';
$spaw_dropdown_data['fontsize']['6'] = '6';

// in mozilla it works only with this settings, if you don't care
// about mozilla you can change <H1> to Heading 1 etc.
// this way it will be reflected in active toolbar
$spaw_dropdown_data['paragraph']['Normal'] = 'Normal';
$spaw_dropdown_data['paragraph']['<H1>'] = 'Heading 1';
$spaw_dropdown_data['paragraph']['<H2>'] = 'Heading 2';
$spaw_dropdown_data['paragraph']['<H3>'] = 'Heading 3';
$spaw_dropdown_data['paragraph']['<H4>'] = 'Heading 4';
$spaw_dropdown_data['paragraph']['<H5>'] = 'Heading 5';
$spaw_dropdown_data['paragraph']['<H6>'] = 'Heading 6';

// image library related config

// allowed extentions for uploaded image files
$spaw_valid_imgs = ['gif', 'jpg', 'jpeg', 'png'];

// allow upload in image library
$spaw_upload_allowed = true;

// allow delete in image library
$spaw_img_delete_allowed = true;

// image libraries
$spaw_imglibs = [
    [
        'value' => 'uploads/',
        'text' => 'Uploads[Xoops]',
    ],
    [
        'value' => 'uploads/wordpress/',
        'text' => 'Uploads[WordPress]',
    ],
];

/*
global $xoopsDB;
$result = $xoopsDB->query("SELECT imgcat_name, imgcat_id, imgcat_storetype FROM " . $xoopsDB->prefix('imagecategory') . " ORDER BY imgcat_name ASC");
$i=count($spaw_imglibs);
while($imgcat = $xoopsDB->fetcharray($result)){
    $spaw_imglibs[$i]["type"]  = "XoopsImage";
    $spaw_imglibs[$i]["value"] = 'uploads/';
    $spaw_imglibs[$i]["text"] = $imgcat["imgcat_name"]."[XOOPS]";
    $spaw_imglibs[$i]["catID"] = $imgcat["imgcat_id"];
    $spaw_imglibs[$i]["storetype"] = $imgcat["imgcat_storetype"];
    $spaw_imglibs[$i]["autoID"] = $i;

    $i++;
}
*/
// file to include in img_library.php (useful for setting $spaw_imglibs dynamically
// $spaw_imglib_include = '';

// allowed hyperlink targets
$spaw_a_targets['_self'] = 'Self';
$spaw_a_targets['_blank'] = 'Blank';
$spaw_a_targets['_top'] = 'Top';
$spaw_a_targets['_parent'] = 'Parent';

// image popup script url
$spaw_img_popup_url = $spaw_dir . 'img_popup.php';

// internal link script url
$spaw_internal_link_script = 'url to your internal link selection script';

// disables style related controls in dialogs when css class is selected
$spaw_disable_style_controls = true;
