<?php
/*
* $Id: include/class_aux_send.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

/*
Script gerado automaticamente pelo /gera-manutencao/gera-manutencao.php versão 1.0
* autor: Claudia Antonini Vitiello Callegari   claudia@foxbrasil.com.br
* Data: 17/01/2005 12:42:31
* classxmail_aux_send.php

*/

//   alterar  conforme necessidade montar integrigade referencial entre as 2 tabelas
//  de forma que quando eliminou todos detalhes de um lote,  eliminar o cabeçalho do lote

// montar função para selecionar os itens com junção das duas tabelas

// incluir linhas detalhes conforme $this->array_users...

class classxmail_aux_send_l
{   // registro de cabeçalho do lote
    public $lote_solicit;

    public $id_men;

    public $user_logado;

    public $dt_solicit;

    public $email_conf;

    public $mail_fromname;

    public $mail_fromemail;

    public $mail_send_to;

    public $array_users;  // matriz com os visitantes a serem inclusos na outra classe  classxmail_aux_send

    public $is_new;

    public function __construct()
    {
        $this->lote_solicit = '';

        $this->id_men;

        $this->user_logado = '';

        $this->dt_solicit;

        $this->email_conf = '';

        $this->mail_fromname = '';

        $this->mail_fromemail = '';

        $this->mail_send_to = '';

        $this->array_users = '';

        $this->is_new = 0;
    }

    // fecha function principal

    public function incluir()
    {
        global $xoopsDB, $men_erro;

        if ($this->validar('I')) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_aux_send_l');

            $sql .= '(lote_solicit,id_men,user_logado,dt_solicit,email_conf,mail_fromname,mail_fromemail,mail_send_to,is_new)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->lote_solicit . "',";

            $sql .= "'" . $this->id_men . "',";

            $sql .= "'" . $this->user_logado . "',";

            $sql .= "'" . time() . "',";

            $sql .= "'" . $this->email_conf . "',";

            $sql .= "'" . $this->mail_fromname . "',";

            $sql .= "'" . $this->mail_fromemail . "',";

            $sql .= "'" . $this->mail_send_to . "',";

            $sql .= "'" . $this->is_new . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            // incluir linhas detalhes conforme $this->array_users...

            $tem_err = 0;

            if (count($this->array_users) > 0) {
                $obj_filho = new classxmail_aux_send();

                for ($i = 0, $iMax = count($this->array_users); $i < $iMax; $i++) {
                    $obj_filho->lote_solicit = $this->lote_solicit;

                    $obj_filho->id_user = $this->array_users[$i];

                    if (!$obj_filho->incluir()) {
                        $men_erro .= sprintf(_MD_XMAIL_ERRINCLOTEUSER, $obj_filho->id_user, $obj_filho->lote_solicit) . '<br>';

                        $tem_err = 1;
                    }
                }
            }

            if ($tem_err) {
                return false;
            }
  

            return true;
        }
  

        return false;
        // fecha if validar
    }

    // fecha function incluir

    public function validar($opt)
    {
        global $men_erro;

        // inserir codigo para validar campos...

        return true;
    }

    // fecha function validar

    // function  alterar() {

    //      global  $xoopsDB, $men_erro  ;

    //      if($this->validar("A")) {

    //         $sql = "UPDATE ".$xoopsDB->prefix("xmail_aux_send_l")." SET  " ;

    //         $sql.= "lote_solicit='$this->lote_solicit',";

    //         $sql.= "user_logado='$this->user_logado',";

    //         $sql.= "email_conf='$this->email_conf',";

    //         $sql.= "mail_fromname='$this->mail_fromname',";

    //         $sql.= "mail_fromemail='$this->mail_fromemail',";

    //         $sql.= "mail_send_to='$this->mail_send_to'";

    //         $sql.= " where lote_solicit='$this->lote_solicit' ";

    //          $result= $xoopsDB->queryF($sql);

    //         if(!$result) {

    //          	return false;

    //         }

    //         return true;

    //      }else {

    //         return false;

    //

    //      } // fecha if validar

    //   } // fecha function alterar

    public function excluir()
    {
        global $xoopsDB, $men_erro, $_GET;

        // esta função será chamada automaticamente , quando se solicitar excluir todos os ítens

        //      if($this->validar("E")) {

        $sql = 'DELETE FROM  ' . $xoopsDB->prefix('xmail_aux_send_l');

        $sql .= " where lote_solicit='$this->lote_solicit' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        return true;
        //    }else {
        //         return false;
        //      } // fecha if validar
    }

    // fecha function excluir

    public function busca()
    {
        global $xoopsDB;

        $sql = 'SELECT * FROM  ' . $xoopsDB->prefix('xmail_aux_send_l');

        $sql .= " where lote_solicit='$this->lote_solicit' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
            return false;
        }

        $cat_data = $xoopsDB->fetchArray($result);

        $this->id_men = $cat_data['id_men'];

        $this->user_logado = $cat_data['user_logado'];

        $this->dt_solicit = $cat_data['dt_solicit'];

        $this->email_conf = $cat_data['email_conf'];

        $this->mail_fromname = $cat_data['mail_fromname'];

        $this->mail_fromemail = $cat_data['mail_fromemail'];

        $this->mail_send_to = $cat_data['mail_send_to'];

        $this->is_new = $cat_data['is_new'];

        // pegar array de visitantes

        $sql = ' select * from ' . $xoopsDB->prefix('xmail_aux_send');

        $sql .= " where lote_solicit='$this->lote_solicit' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        if ($xoopsDB->getRowsNum($result) > 0) {
            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $this->array_users[] = $cat_data['id_user'];
            }
        }

        return true;
    }

    // fecha function busca

    //   function  selecionar() {
    //      global  $xoopsDB ,$_GET  ;
    //      $PHP_SELF=$_SERVER["PHP_SELF"];
    //      $sql = "SELECT * FROM  ".$xoopsDB->prefix("xmail_aux_send_l");
    //      $sql.= " order by  lote_solicit  ";
    //       $result= $xoopsDB->queryF($sql);
    //      if(!$result or $xoopsDB->getRowsNum($result)==0 ) {
    //         xoops_error('Não ha registros cadastrados');
    //      }
    //      else   {
    //         echo "<table border='1' rules='cols' cellpadding='0' cellspacing='0' align='center'>";
    //         echo "	<tr class='head'> ";
    //         $reg_p_page=30;
    //         $regstart = isset($_GET['regstart']) ? intval($_GET['regstart']) : 0;
    //         $regfim = $regstart+$reg_p_page;
    //         $totreg = $xoopsDB->getRowsNum($result);
    //         $arg="" ;// exemplo: "cpf=$cpf&nome=$nome&op=enviar"; // arqumento (variaveis passadas por get )complementar para chamar a pagina
    //         $nav = new XoopsPageNav($totreg, $reg_p_page, $regstart, "regstart", $arg);
    //         echo "<td align=center >lote</td>";
    //         echo "<td align=center ></td>";
    //         echo "<td align=center ></td>";
    //         echo "<td align=center ></td>";
    //         echo "<td align=center ></td>";
    //         echo "<td align=center ></td>";
    //         echo "<td align=center > Opções </td>";
    //         echo "	</tr>";
    //         $i=0;
    //         while (false !== ($cat_data = $xoopsDB->fetcharray($result))) {
    //            if($i>=$regstart  and $i<$regfim) {
    //            if(($i%2)==0) {
    //               echo "<tr class='even' >";
    //            }else {
    //               echo "<tr  class='odd'>";
    //            }
    //            echo "<td align=center >".$cat_data['lote_solicit']."</td>";
    //            echo "<td align=center >".$cat_data['user_logado']."</td>";
    //            echo "<td align=center >".$cat_data['email_conf']."</td>";
    //            echo "<td align=center >".$cat_data['mail_fromname']."</td>";
    //            echo "<td align=center >".$cat_data['mail_fromemail']."</td>";
    //            echo "<td align=center >".$cat_data['mail_send_to']."</td>";
    //            echo "<td align='center'><a href=\"$PHP_SELF?opt=A&lote_solicit=".$cat_data['lote_solicit']. "\"><img src='images/Alterar.bmp' border='0'></a>&nbsp;";
    //            echo " <a  href=\"$PHP_SELF?opt=E&lote_solicit=" .$cat_data['lote_solicit'] . "\"><img src='images/RECYFULL.BMP' border='0'></a>";
    //            echo "  </td>";
    //            echo "  </tr>";
    //            } // fecha if da paginação
    //            $i++;
    //         }
    //         echo "</table>";
    //         echo "<p align='center' >".$nav->renderNav(4)." </p>";
    //         }
    //         echo "<p  align='center' class='footer' ><a href=\"$PHP_SELF?opt=I\"> Incluir</a>";
    //         echo"</table>\n";
    //   } // fecha function selecionar
}// fecha class

class classxmail_aux_send
{   // registros de detalhes do lote
    public $id_user;

    public $lote_solicit;

    public function __construct()
    {
        $this->id_user = '';

        $this->lote_solicit = '';
    }

    // fecha function principal

    public function incluir()
    {
        global $xoopsDB, $men_erro;

        if ($this->validar('I')) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_aux_send');

            $sql .= '(id_user,lote_solicit)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->id_user . "',";

            $sql .= "'" . $this->lote_solicit . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
  

        return false;
        // fecha if validar
    }

    // fecha function incluir

    public function validar($opt)
    {
        global $men_erro;

        // inserir codigo para validar campos...

        return true;
    }

    // fecha function validar

    // function  alterar() {

    //      global  $xoopsDB, $men_erro  ;

    //      if($this->validar("A")) {

    //         $sql = "UPDATE ".$xoopsDB->prefix("xmail_aux_send")." SET  " ;

    //         $sql.= "id_user='$this->id_user',";

    //         $sql.= "id_men='$this->id_men',";

    //         $sql.= "lote_solicit='$this->lote_solicit'";

    //         $sql.= " where id_user='$this->id_user' ";

    //          $result= $xoopsDB->queryF($sql);

    //         if(!$result) {

    //          	return false;

    //         }

    //         return true;

    //      }else {

    //         return false;

    //

    //      } // fecha if validar

    //   } // fecha function alterar

    public function excluir($opt = 0, $array_users = [])
    {
        global $xoopsDB, $men_erro, $_GET;

        // $opt -> 0 ou  1  indica se excluirá todos do lote  ou so 1 item

        //         1 excluirá todos  0 não excluirá todos

        // $array_users =>  array com id dos users a ser excluído do lote $this->lote_solicit

        // se $opt==0  e $array_users for enviado, excluirá todos users do array

        //     if($this->validar("E")) {

        $sql = 'DELETE FROM  ' . $xoopsDB->prefix('xmail_aux_send');

        if (0 == $opt) {
            if (0 == count($array_users)) {
                $sql .= " where id_user='$this->id_user'  and lote_solicit='$this->lote_solicit' ";
            } else {
                $lista_users = implode(',', $array_users);

                $lista_users = '(' . $lista_users . ')';

                $sql .= " where id_user in $lista_users  and lote_solicit='$this->lote_solicit' ";
            }
        } else {
            $sql .= " where lote_solicit='$this->lote_solicit' ";
        }

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            echo $sql;

            return false;
        }

        // verficar se não ha mais filhos  ou se ($opt==1 excluir todos filhos ) e excluir o pai também

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_aux_send') . " where lote_solicit='$this->lote_solicit' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            $men_erro = sprintf(_MD_XMAIL_ERRHEAD . ' x', $this->lote_solicit);

            return false;
        }

        if (0 == $xoopsDB->getRowsNum($result) or 1 == $opt) {
            $objlote = new classxmail_aux_send_l();

            $objlote->lote_solicit = $this->lote_solicit;

            if (!$objlote->excluir()) {
                $men_erro = sprintf(_MD_XMAIL_ERRHEAD, $this->lote_solicit);

                return false;
            }
        }

        return true;
        //      }else {
        //         return false;
        //
    }

    // fecha if validar
} // fecha function excluir

function busca()
{
    global $xoopsDB;

    $sql = 'SELECT * FROM  ' . $xoopsDB->prefix('xmail_aux_send');

    $sql .= " where id_user='$this->id_user' ";

    $result = $xoopsDB->queryF($sql);

    if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
        return false;
    }

    $cat_data = $xoopsDB->fetchArray($result);

    $this->id_user = $cat_data['id_user'];

    $this->lote_solicit = $cat_data['lote_solicit'];

    return true;
} // fecha function busca

//   function  selecionar() {
//      global  $xoopsDB ,$_GET  ;
//      $PHP_SELF=$_SERVER["PHP_SELF"];
//      $sql = "SELECT * FROM  ".$xoopsDB->prefix("xmail_aux_send");
//      $sql.= " order by  lote_solicit  ";
//       $result= $xoopsDB->queryF($sql);
//      if(!$result or $xoopsDB->getRowsNum($result)==0 ) {
//         xoops_error('Não ha registros cadastrados');
//      }
//      else   {
//         echo "<table border='1' rules='cols' cellpadding='0' cellspacing='0' align='center'>";
//         echo "	<tr class='head'> ";
//         $reg_p_page=30;
//         $regstart = isset($_GET['regstart']) ? intval($_GET['regstart']) : 0;
//         $regfim = $regstart+$reg_p_page;
//         $totreg = $xoopsDB->getRowsNum($result);
//         $arg="" ;// exemplo: "cpf=$cpf&nome=$nome&op=enviar"; // arqumento (variaveis passadas por get )complementar para chamar a pagina
//         $nav = new XoopsPageNav($totreg, $reg_p_page, $regstart, "regstart", $arg);
//         echo "<td align=center >user</td>";
//         echo "<td align=center >men</td>";
//         echo "<td align=center >lote</td>";
//         echo "<td align=center > Opções </td>";
//         echo "	</tr>";
//         $i=0;
//         while (false !== ($cat_data = $xoopsDB->fetcharray($result))) {
//            if($i>=$regstart  and $i<$regfim) {
//            if(($i%2)==0) {
//               echo "<tr class='even' >";
//            }else {
//               echo "<tr  class='odd'>";
//            }
//            echo "<td align=center >".$cat_data['id_user']."</td>";
//            echo "<td align=center >".$cat_data['id_men']."</td>";
//            echo "<td align=center >".$cat_data['lote_solicit']."</td>";
//            echo "<td align='center'><a href=\"$PHP_SELF?opt=A&id_user=".$cat_data['id_user']. "\"><img src='images/Alterar.bmp' border='0'></a>&nbsp;";
//            echo " <a  href=\"$PHP_SELF?opt=E&id_user=" .$cat_data['id_user'] . "\"><img src='images/RECYFULL.BMP' border='0'></a>";
//            echo "  </td>";
//            echo "  </tr>";
//            } // fecha if da paginação
//            $i++;
//         }
//         echo "</table>";
//         echo "<p align='center' >".$nav->renderNav(4)." </p>";
//         }
//         echo "<p  align='center' class='footer' ><a href=\"$PHP_SELF?opt=I\"> Incluir</a>";
//         echo"</table>\n";
//   } // fecha function selecionar
// }// fecha class
//
//
