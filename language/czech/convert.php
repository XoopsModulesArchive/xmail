<?php
// $Id: convert.php,v 1.2 2004/10/11 11:25:55 mikhail Exp $

class WfsConvert
{
    public function TextPlane($text)
    {
        $text = preg_replace("/[\s\t\n]{2,}/", ' ', $text);

        return $text;
    }

    public function TextHtml($text)
    {
        $text = preg_replace("/[\s\t\n]{2,}/", ' ', $text);

        return $text;
    }

    public function stripSpaces($text)
    {
        $ret = preg_replace("/[\s\t\n]{2,}/", ' ', $text);

        return $ret;
    }

    public function filenameForWin($text)
    {
        return $text;
    }
}
