<?php
/*
* $Id: include/classxmail_tabperfil.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

class classxmail_tabperfil
{
    public $id_perf;

    public $descri_perf;

    public function __construct()
    {
        $this->id_perf = '';

        $this->descri_perf = '';
    }

    // fecha function principal

    public function incluir()
    {
        global $xoopsDB, $men_erro;

        if ($this->validar('I')) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_tabperfil');

            $sql .= '(descri_perf)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->descri_perf . "')";

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

    public function alterar()
    {
        global $xoopsDB, $men_erro;

        if ($this->validar('A')) {
            $sql = 'UPDATE ' . $xoopsDB->prefix('xmail_tabperfil') . ' SET  ';

            $sql .= "descri_perf='$this->descri_perf'";

            $sql .= " where id_perf='$this->id_perf' ";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
  

        return false;
        // fecha if validar
    }

    // fecha function alterar

    public function excluir()
    {
        global $xoopsDB, $men_erro, $_GET;

        if ($this->validar('E')) {
            $sql = 'DELETE FROM  ' . $xoopsDB->prefix('xmail_tabperfil');

            $sql .= " where id_perf='$this->id_perf' ";

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

    public function busca()
    {
        global $xoopsDB;

        $sql = 'SELECT * FROM  ' . $xoopsDB->prefix('xmail_tabperfil');

        $sql .= " where id_perf='$this->id_perf' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result or 0 == $xoopsDB->getRowsNum($result)) {
            return false;
        }

        $cat_data = $xoopsDB->fetchArray($result);

        $this->id_perf = $cat_data['id_perf'];

        $this->descri_perf = $cat_data['descri_perf'];

        return true;
    }

    // fecha function busca

    public function selecionar()
    {
        global $xoopsDB, $_GET;

        $PHP_SELF = $_SERVER['PHP_SELF'];

        $sql = 'SELECT * FROM  ' . $xoopsDB->prefix('xmail_tabperfil');

        $sql .= ' order by  id_perf  ';

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

            echo '<td align=center >Id</td>';

            echo '<td align=center > Descrição</td>';

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

                    echo '<td  >' . $cat_data['id_perf'] . '</td>';

                    echo '<td >' . $cat_data['descri_perf'] . '</td>';

                    echo "<td ><a href=\"$PHP_SELF?opt=A&id_perf=" . $cat_data['id_perf'] . "\"><img src='" . XOOPS_URL . "/modules/xmail/images/Alterar.bmp' border='0'></a>&nbsp;";

                    echo " <a  href=\"$PHP_SELF?opt=E&id_perf=" . $cat_data['id_perf'] . "\"><img src='" . XOOPS_URL . "/modules/xmail/images/RECYFULL.BMP' border='0'></a>";

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

    public function get_user_perf($id_user)
    {
        // retorna array com o perfil de um visitante

        global $xoopsDB;

        $retorno = [];

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_perfil_news') . ' where user_id="' . $id_user . '"';

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $retorno[] = $cat_data['id_perf'];
            }
        }

        return $retorno;
    }

    public function get_tab_perf($add = '')
    {
        // retorna array com tabela de perfil

        // $add => caracter para adicionar no final da descrição

        //       usado para adicionar <br> qdo. precisar no xoopsform....

        global $xoopsDB;

        $retorno = [];

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_tabperfil');

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $retorno[$cat_data['id_perf']] = $cat_data['descri_perf'] . (string)$add;
            }
        }

        return $retorno;
    }

    public function get_id_from_email($email)
    {
        // recupera o user_id da tabela xmail_newsletter

        // a partir do email

        global $xoopsDB;

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_newsletter') . " where user_email='$email'";

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            $cat_data = $xoopsDB->fetchArray($result);

            return $cat_data['user_id'];
        }  

        echo 'erro na query ', $sql;

        return 0;
    }

    public function exclui_user($user_id)
    {
        global $xoopsDB;

        $sql = 'delete from ' . $xoopsDB->prefix('xmail_perfil_news') . ' where user_id="' . $user_id . '"';

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            echo "Err sql : $sql";
        }
    }

    public function get_lista_perf($lista_perfil = '')
    {
        global $xoopsDB;

        $retorno = '';

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_tabperfil') . " where id_perf in ($lista_perfil) ";

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $retorno .= $cat_data['descri_perf'] . ' - ';
            }
        }

        return $retorno;
    }
} // fecha class




