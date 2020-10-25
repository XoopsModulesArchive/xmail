<?php
/*
* $Id: include/class_ativacao.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

class Xmail_ativacao
{
    public $id_user;

    public $dt_envio;

    public $user_logado;

    public $activation_type;

    public function __construct()
    {
        $this->id_user = '';

        $this->dt_envio = '';

        $this->user_logado = '';

        $this->activation_type = '';
    }

    // fecha function principal

    public function incluir($users)
    {
        global $xoopsDB, $men_erro;

        // $users -> objeto xoopsuser, pode ser array com vários objetos

        if (is_array($users)) {
            $men_erro = '';

            foreach ($users as $user) {
                $this->incluir($user);
            }
        } else {
            if ('xoopsuser' == !get_class($users)) {
                $men_erro .= 'Erro  function incluir deve ser classe xoopsuser <br>';

                return;
            }

            $this->id_user = $users->getVar('uid');

            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_ativacao');

            $sql .= '(id_user,dt_envio,user_logado,activation_type)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->id_user . "',";

            $sql .= "'" . $this->dt_envio . "',";

            $sql .= "'" . $this->user_logado . "',";

            $sql .= "'" . $this->activation_type . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                $men_erro .= sprintf(_MD_XMAIL_ERRATUATIVA, $this->id_user) . '<br>';
            } else {
                return true;
            }
        }
    }

    // fecha function incluir

    public function validar($opt)
    {
        global $men_erro;

        // inserir codigo para validar campos...

        if (!is_int((int)$this->id_user)) {
            $men_erro = 'Valor do id_user deve ser número inteiro ';

            return false;
        }

        if (!is_int((int)$this->dt_envio)) {
            $men_erro = 'Valor do dt_envio deve ser número inteiro ';

            return false;
        }

        if (!is_int((int)$this->user_logado)) {
            $men_erro = 'Valor do user_logado deve ser número inteiro ';

            return false;
        }

        return true;
    }

    // fecha function validar

    // function  alterar() {

    //      global  $xoopsDB, $men_erro  ;

    //      if($this->validar("A")) {

    //         $sql = "UPDATE ".$xoopsDB->prefix("xmail_ativacao")." SET  " ;

    //         $sql.= "id_user='$this->id_user',";

    //         $sql.= "dt_envio='$this->dt_envio',";

    //         $sql.= "user_logado='$this->user_logado'";

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

    public function excluir()
    {
        global $xoopsDB, $men_erro, $_GET;

        if ($this->validar('E')) {
            $sql = 'DELETE FROM  ' . $xoopsDB->prefix('xmail_ativacao');

            $sql .= " where id_user='$this->id_user' ";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
  

        return false;
        // fecha if validar
    }

    // fecha function excluir

    //  function  busca() {

    //      global  $xoopsDB  ;

    //         $sql = "SELECT * FROM  ".$xoopsDB->prefix("xmail_ativacao");

    //         $sql.= " where id_user='$this->id_user' ";

    //          $result= $xoopsDB->queryF($sql);

    //         if(!$result or $xoopsDB->getRowsNum($result)==0 ) {

    //          	return false;

    //         }

    //         $cat_data = $xoopsDB->fetcharray($result) ;

    //         $this->id_user=$cat_data["id_user"];

    //         $this->dt_envio=$cat_data["dt_envio"];

    //         $this->user_logado=$cat_data["user_logado"];

    //         return true;

    //

    //   } // fecha function busca

    public function selecionar()
    {
        global $xoopsDB, $_GET;

        $PHP_SELF = $_SERVER['PHP_SELF'];

        $sql = 'SELECT * FROM  ' . $xoopsDB->prefix('xmail_ativacao');

        $sql .= ' order by  dt_envio  ';

        $result = $xoopsDB->queryF($sql);

        if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
            xoops_error('Não ha registros cadastrados');
        } else {
            echo "<table border='1' rules='cols' cellpadding='0' cellspacing='0' align='center'>";

            echo "	<tr class='head'> ";

            $reg_p_page = 30;

            $regstart = isset($_GET['regstart']) ? (int)$_GET['regstart'] : 0;

            $regfim = $regstart + $reg_p_page;

            $totreg = $xoopsDB->getRowsNum($result);

            $arg = ''; // exemplo: "cpf=$cpf&nome=$nome&op=enviar"; // arqumento (variaveis passadas por get )complementar para chamar a pagina

            $nav = new XoopsPageNav($totreg, $reg_p_page, $regstart, 'regstart', $arg);

            echo '<td align=center >user</td>';

            echo '<td align=center >data</td>';

            echo '<td align=center >solicitante</td>';

            echo '<td align=center > Opções </td>';

            echo '	</tr>';

            $i = 0;

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                if ($i >= $regstart and $i < $regfim) {
                    if (0 == ($i % 2)) {
                        echo "<tr class='even' >";
                    } else {
                        echo "<tr  class='odd'>";
                    }

                    echo '<td align=center >' . $cat_data['id_user'] . '</td>';

                    echo '<td align=center >' . $cat_data['dt_envio'] . '</td>';

                    echo '<td align=center >' . $cat_data['user_logado'] . '</td>';

                    echo "<td align='center'><a href=\"$PHP_SELF?opt=A&id_user=" . $cat_data['id_user'] . "\"><img src='images/Alterar.bmp' border='0'></a>&nbsp;";

                    echo " <a  href=\"$PHP_SELF?opt=E&id_user=" . $cat_data['id_user'] . "\"><img src='images/RECYFULL.BMP' border='0'></a>";

                    echo '  </td>';

                    echo '  </tr>';
                } // fecha if da paginação

                $i++;
            }

            echo '</table>';

            echo "<p align='center' >" . $nav->renderNav(4) . ' </p>';
        }

        echo "<p  align='center' class='footer' ><a href=\"$PHP_SELF?opt=I\"> Incluir</a>";

        echo "</table>\n";
    }

    // fecha function selecionar

    public function get_tentativas()
    {
        global $xoopsDB;

        $sql = 'select count(id_user) as total  from ' . $xoopsDB->prefix('xmail_ativacao') . ' group by
            id_user';

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            $cat_data = $xoopsDB->fetchArray($result);

            return $cat_data['total'];
        }  

        echo "<script> alert('Err in get_tentativas(): $sql) </script>";

        return 0;
    }
}// fecha class
