<?php

/*
* $Id: include/storyform.inc.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/
require XOOPS_ROOT_PATH . '/class/xoopslists.php';
require XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

$sform = new XoopsThemeForm(_MD_XMAIL_SMNAME, 'storyform', xoops_getenv('PHP_SELF'));
$sform->addElement(new XoopsFormText(_MD_XMAIL_TIT, 'title_men', 50, 50, $title_men), true);
$subject_men_caption = _MD_XMAIL_MAILSUBJECT . "<br><br><span style='font-size:x-small;font-weight:bold;'>" . _MD_XMAIL_MAILTAGS . "</span><br><span style='font-size:x-small;font-weight:normal;'>" . _MD_XMAIL_MAILTAGS2 . '</span>';
$subject_caption = _MD_XMAIL_MAILBODY . "<br><br><span style='font-size:x-small;font-weight:bold;'>" . _MD_XMAIL_MAILTAGS . "</span><br><span style='font-size:x-small;font-weight:normal;'>" . _MD_XMAIL_MAILTAGS1 . '<br>' . _MD_XMAIL_MAILTAGS2 . '<br>' . _MD_XMAIL_MAILTAGS3 . '</span>';
$sform->addElement(new XoopsFormText($subject_men_caption, 'subject_men', 50, 80, $subject_men), true);

//	$sform->addElement(new XoopsFormDhtmlTextArea($subject_caption, 'body_men', $body_men, 7, 60));
//  editor visual ou não
$rows = 7;
$cols = 60;
$width = '100%';
$height = '400px';
$isWysiwyg = false;

$xmail_form = $param->tipo_editor;

if ($param->allow_html) {
    $editor = &xmail_getWysiwygForm($xmail_form, $subject_caption, 'body_men', $body_men, $width, $height);

    $isWysiwyg = true;
} else {
    $editor = &xmail_getTextareaForm('dhtml', $subject_caption, 'body_men', $body_men, $rows, $cols);
}
$sform->addElement($editor, true);

if ($param->allow_html) {
    $sform->addElement(new XoopsFormHidden('dobr', 0));
} else {
    //    $sform->addElement(new XoopsFormHidden('dohtml', 0));

    $sform->addElement(new XoopsFormHidden('dobr', 1));
}
$html_checkbox = new XoopsFormCheckBox('', 'dohtml', $dohtml);
$html_checkbox->addOption(1, _MD_XMAIL_DOHTML);
$sform->addElement($html_checkbox);

$isnew_checkbox = new XoopsFormCheckBox('', 'is_new', $is_new);
$isnew_checkbox->addOption(1, _MD_XMAIL_NEWSLETTER);
$sform->addElement($isnew_checkbox);

$sform->addElement(new XoopsFormHidden('id_men', $id_men));

$button_tray = new XoopsFormButton('', 'post', _MD_XMAIL_SUBMIT, 'submit');
$opt_select = new XoopsFormSelect('', 'opt');
if (!$param->allow_html) {
    // definir botão submit com preview e save somente se não for editor visual

    $opt_select->addOptionArray(['preview' => _MD_XMAIL_PREVIEW, 'save' => _MD_XMAIL_SAVE]);
} else {
    $opt_select->addOptionArray(['save' => _MD_XMAIL_SAVE]);
}

$opt_tray = new XoopsFormElementTray('');
$opt_tray->addElement($opt_select);
$opt_tray->addElement($button_tray);

$sform->addElement($opt_tray);

$sform->display();


 


