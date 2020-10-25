<?php
// grvlog.php
//      Function: GrvLog - Grava Log em arquivos TEXTOS padrÆo ASCII

function GrvLog($LogArqnome = '', $writeStr = '')
{
    //  $LogArqnome=  nome do arquivo a ser gravado

    // $writeStr=  string a ser gravada no final do arquivo

    if ('' == $LogArqnome or '' == $writeStr) {
        echo 'Falta parametros para gravar arquivo de log ';

        echo "veja    $LogArqnome  -  $writeStr";

        return false;
    }

    $LogHand = fopen($LogArqnome, 'a+b');

    //if(!preg_match("\n$",$writeStr)) {

    //	$writeStr .= "\n";

    //}

    fwrite($LogHand, $writeStr);

    fclose($LogHand);

    return true;
}
