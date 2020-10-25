<?php
/*
* $Id: uploadfile.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

// File Upload Class
// By Haruki Setoyama   http://harux.net   haruki@harux.net
// License: GPL2 or later
// $Id: uploadfile.php,v 1.4 Date: 06/01/2003, Author: Catzwolf Exp $

//require_once XOOPS_ROOT_PATH.'/modules/xmail/class/common.php';
require_once XOOPS_ROOT_PATH . '/modules/xmail/include/mimetype.php';

// mt_srand((double)microtime() * 1000000);

class UploadFile
{
    public $fieldName;

    public $fileName;

    public $filesize;

    public $minetype;

    public $originalName;

    public $ext;

    public $allowedMinetype;

    public $bannedMinetype;

    public $maxImageWidth;

    public $maxImageHight;

    public $maxFilesize;       // Byte
    public $mode;              // file mode of Unix Style
    public $stripSpaces;       // 0 or 1
    public $bannedChars;

    public $addExt;     // 0 or 1

    public $errormsg;

    public $error;

    // constructor

    public function __construct($fieldName = 'uploadfile')
    {
        $this->fieldName = 'uploadfile';

        //$this->allowedMinetype = array();

        //$this->bannedMinetype = array();

        $this->maxImageWidth = 0;

        $this->maxImageHight = 0;

        //                $this->maxFilesize = 1048576 * 2; // 2MB

        $this->maxFilesize = 0;

        $this->addExt = 1;

        $this->mode = '';

        $this->stripSpaces = 1;

        $this->bannedChars = '';

        if (is_array($fieldName)) {
            foreach ($fieldName as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->fieldName = $fieldName;
        }

        $this->error = 0;
    }

    // set

    public function setAllowedMinetype($value)
    {
        $this->allowedMinetype = $value;
    }

    public function setMaxImageWidth($value)
    {
        $this->maxImageWidth = $value;
    }

    public function setMaxImageHight($value)
    {
        $this->maxImageHight = $value;
    }

    public function setMaxFilesize($value)
    {
        $this->maxFilesize = $value;
    }

    public function setAddExt($value)
    {
        $this->addExt = $value;
    }

    public function setMode($value)
    {
        $this->mode = $value;
    }

    // load HTTP_POST_FILES

    public function loadPostVars()
    {
        global $HTTP_POST_FILES;

        if (!isset($HTTP_POST_FILES[$this->fieldName])) {
            return false;
        }

        $this->fileName = $HTTP_POST_FILES[$this->fieldName]['tmp_name'];

        $this->filesize = $HTTP_POST_FILES[$this->fieldName]['size'];

        $this->error = $HTTP_POST_FILES[$this->fieldName]['error'];

        if (1 == $this->error) {
            $this->errormsg = _MD_XMAIL_ERRORUPLOA1;
        }

        if (2 == $this->error) {
            $this->errormsg = sprintf(_MD_XMAIL_ERRORUPLOA2, $this->maxFilesize);
        }

        if (3 == $this->error) {
            $this->errormsg = _MD_XMAIL_ERRORUPLOA3;
        }

        if (4 == $this->error) {
            $this->errormsg = _MD_XMAIL_ERRORUPLOA4;
        }

        $mimetype = new mimetype();

        $this->minetype = $mimetype->getType($HTTP_POST_FILES[$this->fieldName]['name']);

        //$this->minetype = $HTTP_POST_FILES[$this->fieldName]['type'];

        $this->originalName = $HTTP_POST_FILES[$this->fieldName]['name'];

        //echo $this->fileName."<br>".$this->filesize."<br>".$this->minetype."<br>".$this->originalName;

        $tmparr = explode('.', $this->originalName);

        array_shift($tmparr);

        $ret = [];

        foreach ($tmparr as $arr) {
            if (!preg_match("/\W/", $arr)) {
                $ret[] = $arr;
            }
        }

        $this->ext = implode('.', $ret);

        return true;
    }

    // get

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFilesize()
    {
        return $this->filesize;
    }

    public function getMinetype()
    {
        return $this->minetype;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function getExt()
    {
        return $this->ext;
    }

    // read file contents

    public function readFile()
    {
        if (!is_readable($this->fileName)) {
            return false;
        }

        return file($this->fileName);
    }

    // check

    public function isAllowedImageSize()
    {
        if (0 == $this->maxImageWidth || 0 == $this->maxImageHight) {
            return true;
        }

        $size = getimagesize($this->fileName);

        if ($size[0] > $this->maxImageWidth) {
            return false;
        }

        if ($size[1] > $this->maxImageHight) {
            return false;
        }

        return true;
    }

    public function isAllowedFileSize()
    {
        if (0 == $this->maxFilesize) {
            return true;
        }

        if ($this->filesize > $this->maxFilesize) {
            return false;
        }

        return true;
    }

    public function isAllowedMineType()
    {
        global $param;

        $mimetype = new mimetype();

        //foreach(explode(" ", $wfsConfig['selmimetype']) as $type)

        foreach (explode(' ', $param->selmimetype) as $type) {
            if ($this->minetype == $mimetype->privFindType($type)) {
                return true;
            }
        }

        return false;
    }

    public function isAllowedChars($distfilename)
    {
        if (empty($this->allowedChars)) {
            return true;
        }

        if (preg_match('/' . $this->allowedChars . '/', $distfilename)) {
            return false;
        }

        return true;
    }

    // HTML

    public function formStart($action = './', $name = '', $extra = '')
    {
        $ret = "<form enctype='multipart/form-data' method='post'";

        $ret .= " action='" . $action . "'";

        if (!empty($name)) {
            $ret .= " name='" . $name . "'" . " id='" . $name . "'";
        }

        if (!empty($extra)) {
            $ret .= ' ' . $extra;
        }

        $ret .= '>';

        return $ret;
    }

    public function formMax()
    {
        if (empty($this->maxFilesize)) {
            return '';
        }

        return "<input type='hidden' name='MAX_FILE_SIZE' value='" . $this->maxFilesize . "'>";
    }

    public function formField()
    {
        return "<input type='file' name='" . $this->fieldName . "' id='" . $this->fieldName . "'>";
    }

    public function formSubmit($value = 'UPLOAD', $name = '', $extra = '')
    {
        $ret = "<br><input type='submit' value='" . $value . "'";

        if (!empty($name)) {
            $ret .= " name='" . $name . "' id='" . $name . "'";
        }

        if (!empty($extra)) {
            $ret .= ' ' . $extra;
        }

        $ret .= '>';

        return $ret;
    }

    public function formEnd()
    {
        return '</form>';
    }

    // upload

    public function doUpload($distfilename)
    {
        global $param;

        $this->setAllowedMinetype([$param->selmimetype]);

        if (!empty($this->errormsg)) {
            echo "<script>alert('$this->errormsg')</script>";

            return false;
        }

        if (empty($this->fileName)) {
            $men_erro = _MD_XMAIL_NOTFILENAME;

            echo "<script>alert($men_erro)</script>";

            return false;
        }

        if (!$this->isAllowedImageSize()) {
            $men_erro = _MD_XMAIL_NOTSIZEIMAGE;

            echo "<script>alert($men_erro)</script>";

            return false;
        }

        //                if (!$this->isAllowedFileSize()){

        //                    echo "<script>alert('Falhou em isAllowedFileSize ')</script>";

        //                    return false;

        //                }

        if (!$this->isAllowedMineType()) {
            $men_erro = _MD_XMAIL_FILENOTALLOW;

            echo "<script>alert('$men_erro')</script>";

            return false;
        }

        //                if (!$this->isAllowedChars($distfilename)) {

        //                    echo "<script>alert('Falhou em isallowedchars :$distfilename')</script>";

        //                    return false;

        //                }

        if (!empty($this->ext) && $this->addExt) {
            $distfilename .= '.' . $this->ext;
        }

        if ($this->stripSpaces) {
            $distfilename = preg_replace("/\s/", '', $distfilename);
        }

        if (!move_uploaded_file($this->fileName, $distfilename)) {
            $men_erro = sprintf(_MD_XMAIL_FALHAMOVED, $distfilename);

            echo "<script>alert('$men_erro')</script>";

            return false;
        }

        if (!empty($this->mode) && is_numeric($this->mode)) {
            chmod($distfilename, octdec($this->mode));
        }

        $this->fileName = $distfilename;

        return $distfilename;
    }

    public function doUploadToRandumFile($distpath, $prefix = '')
    {
        if (!is_dir($distpath) && !is_writable($distpath)) {
            return false;
        }

        if (!empty($this->ext) && $this->addExt) {
            $ext = '.' . $this->ext;
        } else {
            $ext = '';
        }

        for ($i = 0; $i < 10; $i++) {
            $distfilename = $distpath . '/' . $prefix . mt_rand(100000, 999999);

            if (!file_exists($distfilename . $ext)) {
                touch($distfilename . $ext);

                return $this->doUpload($distfilename);
            }
        }

        return false;
    }

    public function doUploadImage($distpath, $filename = '', $exti = '0')
    {
        global $wfsConfig, $xoopsModule;

        //if (!$this->isAllowedMineType()) return false;

        //if (is_file($distpath."/".$this->originalName)) return false;

        if (empty($filename)) {
            $filename = $this->originalName;
        }

        $ext = '';

        $this->setAddExt($exti);

        return $this->doUpload($distpath . '/' . $filename . $ext);
    }
}
