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
// Main control class
// ================================================
// Developed: Alan Mendelevich, alan@solmetra.lt
// Copyright: Solmetra (c)2003 All rights reserved.
// ------------------------------------------------
//                                www.solmetra.com
// ================================================
// v.1.0, 2003-03-25
// ================================================

$spaw_root = XOOPS_ROOT_PATH . '/class/spaw/';

include $spaw_root . 'config/spaw_control.config.php';
include $spaw_root . 'class/util.class.php';
include $spaw_root . 'class/toolbars.class.php';
include $spaw_root . 'class/lang.class.php';

// instance counter (static)
$spaw_wysiwyg_instCount = 0;

class SPAW_Wysiwyg
{
    // controls name

    public $control_name;

    // controls unmodified name

    public $original_name;

    // value

    public $value;

    // holds control toolbar mode.

    public $mode;

    // editor dimensions;

    public $height;

    public $width;

    // language object

    public $lang;

    // theme (skin)

    public $theme;

    // editor stylesheet

    public $css_stylesheet;

    // toolbar dropdown data

    public $dropdown_data;

    // toolbars

    public $toolbars;

    // constructor

    public function __construct(
        $control_name = 'richeditor',
        $value = '',
        $lang = '',
        $mode = '',
        $theme = '',
        $width = '100%',
        $height = '300px',
        $css_stylesheet = '',
        $dropdown_data = ''
    ) {
        global $spaw_dir;

        global $spaw_wysiwyg_instCount;

        global $spaw_default_theme;

        global $spaw_default_css_stylesheet;

        global $spaw_wysiwyg_instCount;

        $spaw_wysiwyg_instCount++;

        $this->original_name = $control_name;

        $this->control_name = str_replace(']', '_', str_replace('[', '_', str_replace('[]', '_' . $spaw_wysiwyg_instCount . '_', $control_name)));

        $this->value = $value;

        $this->width = $width;

        $this->height = $height;

        if ('' == $css_stylesheet) {
            $this->css_stylesheet = $spaw_default_css_stylesheet;
        } else {
            $this->css_stylesheet = $css_stylesheet;
        }

        $this->getLang($lang);

        if ('' == $theme) {
            $this->theme = $spaw_default_theme;
        } else {
            $this->theme = $theme;
        }

        $this->mode = $mode;

        $this->dropdown_data = $dropdown_data;

        $this->getToolbar();
    }

    // sets _mode variable and fills toolbar items array

    public function setMode($value)
    {
        $this->mode = $value;
    }

    // returns _mode value

    public function getMode()
    {
        return ($this->mode);
    }

    // set value/get value

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return ($this->value);
    }

    // set height/get height

    public function setHeight($value)
    {
        $this->height = $value;
    }

    public function getHeight()
    {
        return ($this->height);
    }

    // set/get width

    public function setWidth($value)
    {
        $this->width = $value;
    }

    public function getWidth()
    {
        return ($this->width);
    }

    // set/get css_stylesheet

    public function setCssStyleSheet($value)
    {
        $this->css_stylesheet = $value;
    }

    public function getCssStyleSheet()
    {
        return ($this->css_stylesheet);
    }

    // outputs css and javascript code include

    public function getCssScript($inline = false)
    {
        // static method... use only once per page

        global $spaw_dir;

        global $spaw_inline_js;

        global $spaw_root;

        global $spaw_active_toolbar;

        global $spaw_internal_link_script;

        global $spaw_img_popup_url;

        $buf = '';

        if ($spaw_inline_js) {
            // inline javascript

            echo "<script language='JavaScript'>\n";

            echo "<!--\n";

            echo 'var spaw_active_toolbar = ' . ($spaw_active_toolbar ? 'true' : 'false') . ";\n";

            include $spaw_root . 'class/script.js.php';

            echo "//-->\n";

            echo "</script>\n";
        } else {
            // external javascript

            $buf = "<script language='JavaScript'>\n";

            $buf .= "<!--\n";

            $buf .= 'var spaw_active_toolbar = ' . ($spaw_active_toolbar ? 'true' : 'false') . ";\n";

            $buf .= "//-->\n";

            $buf .= "</script>\n";

            $buf .= '<script language="JavaScript" src="' . $spaw_dir . 'spaw_script.js.php"></script>' . "\n\n";
        }

        return $buf;
    }

    // checks browser compatibility with the control

    public function checkBrowser()
    {
        return SPAW_Util::checkBrowser();
    }

    // load language data

    public function getLang($lang = '')
    {
        $this->lang = new SPAW_Lang($lang);
    }

    // load toolbars

    public function getToolbar()
    {
        $this->toolbars = new SPAW_Toolbars($this->lang, $this->control_name, $this->mode, $this->theme, $this->dropdown_data);
    }

    // returns html for wysiwyg control

    public function getHtml()
    {
        global $spaw_dir;

        global $spaw_wysiwyg_instCount;

        global $spaw_active_toolbar;

        $n = $this->control_name;

        $orn = $this->original_name;

        // todo: make more customizable

        $buf = '';

        if (SPAW_Util::checkBrowser()) {
            if (1 == $spaw_wysiwyg_instCount) {
                $buf .= $this->getCssScript();
            }

            // theme based css file and javascript

            $buf .= '<script language="JavaScript" src="' . $spaw_dir . 'lib/themes/' . $this->theme . '/js/toolbar.js.php"></script>';

            $buf .= '<link rel="stylesheet" type="text/css" href="' . $spaw_dir . 'lib/themes/' . $this->theme . '/css/toolbar.css">';

            $buf .= '<table border="0" cellspacing="0" cellpadding="0" width="' . $this->getWidth() . '" height="' . $this->getHeight() . '">';

            $buf .= '<tr>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_top_design" class="SPAW_' . $this->theme . '_toolbar" colspan="3">';

            $buf .= $this->toolbars->get('top');

            $buf .= '</td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_top_html" class="SPAW_' . $this->theme . '_toolbar" colspan="3" style="display : none;">';

            $buf .= $this->toolbars->get('top', 'html');

            $buf .= '</td>';

            $buf .= '</tr>';

            $buf .= '<tr>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_left_design" valign="top" class="SPAW_' . $this->theme . '_toolbar" >';

            $buf .= $this->toolbars->get('left');

            $buf .= '</td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_left_html" valign="top" class="SPAW_' . $this->theme . '_toolbar" style="display : none;">';

            $buf .= $this->toolbars->get('left', 'html');

            $buf .= '</td>';

            $buf .= '<td align="left" valign="top" width="100%" height="100%">';

            //$buf.= '<input type="hidden" id="'.$n.'" name="'.$n.'">';

            $buf .= '<textarea id="' . $n . '" name="' . $orn . '" style="width:' . $this->getWidth() . '; height:' . $this->getHeight() . '; display:none;" class="SPAW_' . $this->theme . '_editarea"></textarea>';

            $buf .= '<input type="hidden" id="SPAW_' . $n . '_editor_mode" name="SPAW_' . $n . '_editor_mode" value="design">';

            $buf .= '<input type="hidden" id="SPAW_' . $n . '_lang" value="' . $this->lang->lang . '">';

            $buf .= '<input type="hidden" id="SPAW_' . $n . '_theme" value="' . $this->theme . '">';

            $buf .= '<input type="hidden" id="SPAW_' . $n . '_borders" value="on">';

            $buf .= '<iframe id="' . $n . '_rEdit" style="width:100%; height:' . $this->getHeight() . '; direction:' . $this->lang->getDir() . ';" class="SPAW_' . $this->theme . '_editarea" frameborder="no" src="' . $spaw_dir . 'empty.html"></iframe><br>';

            $buf .= "\n<script language=\"javascript\">\n<!--\n";

            $tmpstr = str_replace("\r\n", "\n", $this->getValue());

            $tmpstr = str_replace("\r", "\n", $tmpstr);

            $content = explode("\n", $tmpstr);

            $plus = '';

            foreach ($content as $line) {
                $buf .= "setTimeout('document.getElementById(\"" . $n . '").value ' . $plus . '="' . str_replace('-->', '@@END_COMMENT', str_replace('<!--', '@@START_COMMENT', str_replace('"', '&quot;', str_replace("'", "\'", str_replace('\\', '\\\\\\\\', $line))))) . "\\\\n\";',0);\n";

                $plus = '+';
            }

            $buf .= "setTimeout('document.getElementById(\"" . $n . '").value = document.getElementById("' . $n . "\").value.replace(/&quot;/g,\'\"\');',0);" . "\n";

            $buf .= "setTimeout('document.getElementById(\"" . $n . '").value = document.getElementById("' . $n . "\").value.replace(/@@START_COMMENT/g,\'<!--\');',0);" . "\n";

            $buf .= "setTimeout('document.getElementById(\"" . $n . '").value = document.getElementById("' . $n . "\").value.replace(/@@END_COMMENT/g,\'-->\');',0);" . "\n";

            //      $buf.='setTimeout("alert(document.all.'.$n.'.value);",0);'."\n";

            //      $buf.='setTimeout("'.$n.'_rEdit.document.body.innerHTML += document.all.'.$n.'.value;",0);'."\n";

            //  $buf.='setTimeout("SPAW_toggle_borders(\''.$n.'\',this[\''.$n.'_rEdit\'].document.body,null);",0);'."\n";

            // editor init

            $buf .= 'setTimeout("SPAW_editorInit(\'' . $n . '\',\'' . htmlspecialchars($this->getCssStyleSheet(), ENT_QUOTES | ENT_HTML5) . '\',\'' . $this->lang->getDir() . '\');",0);' . "\n";

            $buf .= '//--></script>';

            $buf .= '</td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_right_design" valign="top" class="SPAW_' . $this->theme . '_toolbar">';

            $buf .= $this->toolbars->get('right');

            $buf .= '</td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_right_html" valign="top" class="SPAW_' . $this->theme . '_toolbar" style="display : none;">';

            $buf .= $this->toolbars->get('right', 'html');

            $buf .= '</td>';

            $buf .= '</tr>';

            $buf .= '<tr><td class="SPAW_' . $this->theme . '_toolbar"></td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_bottom_design" class="SPAW_' . $this->theme . '_toolbar" width="100%">';

            $buf .= $this->toolbars->get('bottom');

            $buf .= '</td>';

            $buf .= '<td id="SPAW_' . $n . '_toolbar_bottom_html" class="SPAW_' . $this->theme . '_toolbar" width="100%" style="display : none;">';

            $buf .= $this->toolbars->get('bottom', 'html');

            $buf .= '</td>';

            $buf .= '<td class="SPAW_' . $this->theme . '_toolbar"></td></tr>';

            $buf .= '</table>';
        } else {
            // show simple text area

            $buf = '<textarea cols="20" rows="5" name="' . $n . '" style="width:' . $this->getWidth() . '; height:' . $this->getHeight() . '">' . htmlspecialchars($this->getValue(), ENT_QUOTES | ENT_HTML5) . '</textarea>';
        }

        return $buf;
    }

    // outputs wysiwyg control

    public function show()
    {
        echo $this->getHtml();
    }
}
