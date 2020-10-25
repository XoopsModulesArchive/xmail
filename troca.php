<?php
// Claudia Antonini Vitiello Callegari
//  fazer teste
set_time_limit(0);
// troca.php

//echo " dei return  para não executar...  ";
//return;
$origem[] = 'versão 1.11';
$destino[] = 'versão 2.0';

// extensões válidas
//$ext_valid[]='*' ; // todos arquivos
$ext_valid[] = 'php';
$ext_valid[] = 'inc';
$ext_valid[] = 'txt';

$dirpath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']);
$arq_log = 'trocas.log';
$faz_troca = true;  // indica se fará troca ou so registrará no log
$case_sensitive = true;  // indica se usará função ereg_replace (true) ou eregi_replace( false)
$tot_trocas = 0;
//   $extension='.tmp';
$extension = '';   // se definido gerará outro arquivo com a extensão definida
$gravalog = true;  // indica se gravará arquivo de log ou não

//global $tot_trocas ;
$funcao = ($case_sensitive) ? 'ereg_replace' : 'eregi_replace';

$alt = ($faz_troca) ? 'Alterou ' : 'Não alterou';

require 'grvlog.php';

if ($gravalog) {
    GrvLog($arq_log, "\n" . str_repeat('-', 80) . "\n Log gerado em " . date('d-m-Y') . ' pelo script ' . $_SERVER['PHP_SELF']) . "\n";
}
ler($dirpath, $origem, $destino);
if ($gravalog) {
    GrvLog($arq_log, " \n - Total de trocas: " . $tot_trocas);
}

function troca_var($filename, $filename_tmp, $origem = '', $destino = '')
{
    global $arq_log, $funcao, $alt, $tot_trocas, $faz_troca, $gravalog;

    global $ext_valid;

    // Abre arquivo $filename  e processa trocando conteúdo  da matriz $origem pela $destino

    // e grava novo arquivo $filename_tmp

    // $origem deve ser matriz o mesmo número de linhas que $destino

    // cada índice da $origem  deve corresponder ao índice da $destino

    if (basename($filename) == basename($_SERVER['PHP_SELF']) or basename($filename) == $arq_log or 'grvlog.php' == basename($filename)) {
        //     echo "<script>alert('Não alterar o script em execução ou o log de erros')</script> ";

        return false;
    }

    $_arq = explode('.', basename($filename));

    $ext_arq = $_arq[count($_arq) - 1];

    if (!in_array($ext_arq, $ext_valid, true) and !in_array('*', $ext_valid, true)) {
        echo "<br> Extensão não válida  $ext_arq ";

        return false;
    }

    if (empty($filename) or empty($filename_tmp) or empty($origem) or empty($destino)) {
        echo "<script>alert('variaveis vazias ')</script> ";

        echo '<b> veja as vars ', $filename, $filename_tmp, $origem, $destino, '</b>';

        return false;
    }

    if (count($origem) != count($destino)) {
        return false;
    }

    // ler o arquivo , colocando cada linha na matriz $linhas

    if ($id_arq = @fopen($filename, 'rb')) {
        while (!feof($id_arq)) {
            $linhas[] = fgets($id_arq, 300);
        }

        fclose($id_arq);
    } else {
        echo "<script>alert('Não foi possível abrir $filename ');</script>";

        return false;
    }

    //  trocar  variaveis $origem por $destino gerando arquivo $filename_tmp

    if ($id_arq = @fopen($filename_tmp, 'wb')) {
        for ($i = 0, $iMax = count($linhas); $i < $iMax; $i++) {
            for ($i2 = 0, $i2Max = count($origem); $i2 < $i2Max; $i2++) {
                $antes = $linhas[$i];

                //             $depois= eregi_replace($origem[$i2], $destino[$i2] ,$linhas[$i]);

                $depois = $funcao($origem[$i2], $destino[$i2], $linhas[$i]);

                if ($faz_troca) {
                    $linhas[$i] = $depois;
                }

                if ($antes != $depois) {
                    if ($faz_troca) {
                        $tot_trocas++;
                    }

                    $output = "\n" . $filename_tmp . "\n " . $alt . " linha $i: Antes: " . $antes . ' Depois: ' . $depois;

                    if ($gravalog) {
                        GrvLog($arq_log, $output);
                    }
                }
            }

            fwrite($id_arq, $linhas[$i]);
        }

        fclose($id_arq);
    } else {
        echo "<script>alert('Falha na geração do $filename_tmp ');</script>";

        return false;
    }

    return true;
}

function ler($file, $origem, $destino)
{
    global $extension;

    if (is_dir($file)) {
        $handle = opendir($file);

        while ($filename = readdir($handle)) {
            if ('.' != $filename && '..' != $filename) {
                ler($file . '/' . $filename, $origem, $destino);
            }
        }

        closedir($handle);

    // echo "<br> <br> ver dir ",($file);
    } else {
        if (!troca_var($file, $file . $extension, $origem, $destino)) {
            echo "falhou troca em $file ";
        }

        //  echo "<br> ver  arq ",($file);
    }
}
