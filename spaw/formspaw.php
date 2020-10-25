<?php
// $Id: formspaw.php, V 1.0 phpp Exp $
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

/**
 * Adapted SPAW editor
 *
 * @author        phppp, http://xoops.org.cn
 * @copyright     copyright (c) 2004 XOOPS.org
 */
class XoopsFormSpaw extends XoopsFormTextArea
{
    public $language = _LANGCODE;

    public $width;

    public $height;

    /**
     * Constructor
     *
     * @param string $caption Caption
     * @param string $name    "name" attribute
     * @param string $value   Initial text
     * @param string $width   iframe width
     * @param string $height  iframe height
     * @param mixed $checkCompatible
     */

    public function __construct($caption, $name, $value = '', $width = '100%', $height = '300px', $checkCompatible = false)
    {
        if ($checkCompatible && !$this->isCompatible()) {
            $this = false;

            return false;
        }

        $this->XoopsFormTextArea($caption, $name, $value);

        $this->width = $width;

        $this->height = $height;
    }

    /**
     * get textarea width
     *
     * @return    string
     */

    public function getWidth()
    {
        return $this->width;
    }

    /**
     * get textarea height
     *
     * @return    string
     */

    public function getHeight()
    {
        return $this->height;
    }

    /**
     * get language
     *
     * @return    string
     */

    public function getLanguage()
    {
        return str_replace('-', '_', mb_strtolower($this->language));
    }

    /**
     * set language
     *
     * @param mixed $lang
     */

    public function setLanguage($lang = 'en')
    {
        $this->language = $lang;
    }

    /**
     * get allowed extensions for uploading
     *
     * @return    string
     */

    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * set  allowed extensions for uploading
     *
     * @param mixed $extensions
     */

    public function setAllowedExtensions($extensions = '')
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * set file path
     *
     * @param mixed $path
     */

    public function setFilePath($path = '')
    {
        $this->filePath = $path;
    }

    /**
     * enable upload
     *
     * @param mixed $extensions
     */

    public function enableUpload($extensions = '')
    {
        $this->uploadEnabled = true;

        $this->setAllowedExtensions($extensions);
    }

    /**
     * enable upload
     */

    public function getUploadStatus()
    {
        return $this->uploadEnabled;
    }

    /**
     * get file path
     */

    public function getFilePath()
    {
        $check_func = ($this->getUploadStatus()) ? 'is_writable' : 'is_readable';

        return $check_func($this->filePath) ? $this->filePath : false;
    }

    /**
     * prepare HTML for output
     *
     * @return    sting HTML
     */

    public function render()
    {
        $myts = MyTextSanitizer::getInstance();

        $ret = '';

        if (is_readable(XOOPS_ROOT_PATH . '/class/spaw/spaw_control.class.php')) {
            require_once XOOPS_ROOT_PATH . '/class/spaw/spaw_control.class.php';

            $value = $this->getValue();

            $value = str_replace('&amp;', '&', $myts->undoHtmlSpecialChars($value));

            $value = str_replace('<BR>', '<br>', $value);

            $spaw = new SPAW_Wysiwyg($this->getName(), $value, $this->getLanguage(), 'full', 'default', $this->getWidth(), $this->getHeight());

            ob_start();

            $spaw->show();

            $ret = ob_get_contents();

            ob_end_clean();
        }

        return $ret;
    }

    /**
     * Check if compatible
     *
     * @return bool
     */

    public function isCompatible()
    {
        if (!is_readable(XOOPS_ROOT_PATH . '/class/spaw/spaw_control.class.php')) {
            return false;
        }

        require_once XOOPS_ROOT_PATH . '/class/spaw/spaw_control.class.php';

        return SPAW_Wysiwyg::checkBrowser();
    }
}
