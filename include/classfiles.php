<?php
/*
* $Id: include/classfiles.php
* Module: XMAIL
* Version: v2.0
* Release Date: 18 Março 2005
* Author: Claudia Antonini Vitiello Callegari   Gilberto G. de Oliveira (Giba)
* License: GNU
*/

class classfiles
{
    public $fileid;

    public $filerealname;

    public $date;

    public $ext;

    public $minetype;

    public $filedescript;

    public $uid;

    public $dir_upload;

    public function __construct()
    {
        $this->fileid;

        $this->filerealname = '';

        $this->date = 0;

        $this->ext = '';

        $this->minetype = '';

        $this->filedescript = '';

        $this->uid = 0;

        $this->dir_upload = '';
    }

    public function incluir()
    {
        global $xoopsDB;

        if ($this->validar('I')) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_files');

            $sql .= ' (filerealname,date,ext,minetype,filedescript,uid,dir_upload)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->filerealname . "',";

            $sql .= "'" . $this->date . "',";

            $sql .= "'" . $this->ext . "',";

            $sql .= "'" . $this->minetype . "',";

            $sql .= "'" . $this->filedescript . "',";

            $sql .= "'" . $this->uid . "',";

            $sql .= "'" . $this->dir_upload . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
  

        return false;
    }

    public function validar($opt)
    {
        global $men_erro;

        return true;
    }

    public function alterar()
    {
        global $xoopsDB;

        if ($this->validar('A')) {
            $sql = 'UPDATE ' . $xoopsDB->prefix('xmail_files');

            $sql .= " SET filerealnamer = $this->filerealname,
                       date=$this->date ,
                       ext='$this->ext' ,
                       minetype='$this->minetype',
                       filedescript='$this->filedescript',
                       uid='$this->uid' ,
                       dir_upload='$this->upload' ";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                //                    echo $sql;

                return false;
            }

            return true;
        }
  

        return false;
    }

    public function excluir($id_men = 0)
    {
        global $men_erro, $xoopsDB;

        $sql = 'SELECT * from ' . $xoopsDB->prefix('xmail_men_anexo') . ' where fileid=' . $this->fileid;

        if ($id_men > 0) {
            // se passado o $id_men quer verificar se ha vinculo em outras mensagens com excessão

            // da que foi informada

            $sql .= " and idmen!='$id_men'  ";
        }

        $result = $xoopsDB->queryF($sql);

        if ($xoopsDB->getRowsNum($result) > 0) {
            // para excluir o arquivo  não deve estar vinculado a nenhuma mensagem

            //  echo "<script> alert('Vinculado em outra mensagem ')</script> ";

            return false;
        }  

        //localizar o arquivo no banco de dados

        $sql = 'select * from ' . $xoopsDB->prefix('xmail_files') . ' where fileid=' . $this->fileid;

        $result = $xoopsDB->queryF($sql);

        if ($result) {
            // excluir fisicamente do diretório

            $cat_data = $xoopsDB->fetchArray($result);

            if (!unlink(XOOPS_ROOT_PATH . '/' . $cat_data['dir_upload'] . '/' . $cat_data['filerealname'])) {
                echo "<script> alert('Não conseguiu excluir do diretório ')</script> ";
            }

            // excluir do banco de dados

            $sql = 'DELETE from ' . $xoopsDB->prefix('xmail_files') . ' where fileid=' . $this->fileid;

            $result = $xoopsDB->queryF($sql);

            if ($result) {
                return true;
            }  

            // echo "<script> alert('Não conseguiu excluir do banco sql:$sql  ')</script> ";

            return false;
        }
    }

    public function busca()
    {
        global $xoopsDB;

        $sql = 'SELECT * from ' . $xoopsDB->prefix('xmail_files') . ' where fileid=' . $this->fileid;

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        $cat_data = $xoopsDB->fetchArray($result);

        $this->filerealname = $cat_data['filerealname'];

        $this->date = $cat_data['date'];

        $this->ext = $xoopsDB->getRowsNum($ext);

        $this->minetype = $cat_data['minetype'];

        $this->filedescript = $cat_data['filedescript'];

        $this->uid = $cat_data['uid'];

        $this->dir_upload = $cat_data['dir_upload'];

        return true;
    }

    public function exibe_anexos($id_men)
    {
        global $xoopsDB;

        $sql = 'SELECT files.* ,anexo.idmen  FROM ' . $xoopsDB->prefix('xmail_files') . ' as files ,' . $xoopsDB->prefix('xmail_men_anexo') . " as anexo
            where files.fileid=anexo.fileid and anexo.idmen='$id_men' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        if ($xoopsDB->getRowsNum($result) > 0) {
            echo "<table border=1 cellpadding=0 cellspacing=0 width='100%' >\n";

            echo "<tr><td  colspan='7' align='center'  ><b>" . _MD_XMAIL_ANEXOS . ' </b></td></tr> ';

            echo "	<tr class='head' >\n";

            echo '		<td><b>' . _MD_XMAIL_ID_MEN . "</b></td>\n";

            echo '		<td><b>' . _MD_XMAIL_ID_ARQ . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_NOMEARQ . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_TIPO . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_FILEDESCRIPT . " </b></td>\n";

            echo '		<td><b>' . _MD_XMAIL_SIZE . "</b></td>\n";

            echo '		<td><b>' . _MD_XMAIL_OPT . "</b></td>\n";

            echo "	</tr>\n";

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $filename = XOOPS_ROOT_PATH . '/' . $cat_data['dir_upload'] . '/' . $cat_data['filerealname'];

                $icon = get_icon($filename);

                $iconshow = '<img src=' . XOOPS_URL . '/modules/xmail/images/icon/' . $icon . " align='middle'>";

                if (is_file($filename)) {
                    $size = Prettysize(filesize($filename));
                } else {
                    $size = '0';
                }

                echo "	<tr class='even' >\n";

                echo '		<td>' . $cat_data['idmen'] . "</td>\n";

                echo '		<td>' . $cat_data['fileid'] . "</td>\n";

                echo '		<td >' . $iconshow . '&nbsp;' . $cat_data['filerealname'] . "</td>\n";

                echo '		<td >' . $cat_data['minetype'] . "</td>\n";

                echo '		<td >' . $cat_data['filedescript'] . "</td>\n";

                echo '		<td>' . $size . "</td>\n";

                echo "		<td><a href='gerencia.php?op=exc_anexo&id_men=" . $cat_data['idmen'] . '&fileid=' . $cat_data['fileid'] . "'>" . _MD_XMAIL_EXC . "</a></td>\n";

                echo "	</tr>\n";
            }

            echo '</table>';
        }
    }

    public function exibe_files($id_men)
    {
        global $xoopsDB, $xoopsUser, $isadmin;

        // pegar todos arquvos anexos

        $arq_anexos = $this->files_anexos($id_men);

        $sql = 'SELECT files.*  FROM ' . $xoopsDB->prefix('xmail_files') . ' as files ';

        if (!empty($arq_anexos)) {
            $sql .= " where  !(fileid in $arq_anexos )  ";
        }

        if (!$isadmin) {
            $sql .= ' and files.uid=' . $xoopsUser->getVar('uid');
        }

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        if ($xoopsDB->getRowsNum($result) > 0) {
            echo "<table border=1 cellpadding=0 cellspacing=0 width='100%' >\n";

            echo "<tr><td  colspan='7' align='center'  ><b>" . _MD_XMAIL_DISPONIVEIS . '</b>  </td></tr> ';

            echo "	<tr class='head' >\n";

            echo '		<td><b>' . _MD_XMAIL_ID_ARQ . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_NOMEARQ . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_TIPO . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_FILEDESCRIPT . " </b></td>\n";

            echo '		<td><b>' . _MD_XMAIL_SIZE . "</b></td>\n";

            echo "		<td align='center' ><b>" . _MD_XMAIL_DIR . "</b></td>\n";

            echo '		<td><b>' . _MD_XMAIL_OPT . "</b></td>\n";

            echo "	</tr>\n";

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $filename = XOOPS_ROOT_PATH . '/' . $cat_data['dir_upload'] . '/' . $cat_data['filerealname'];

                $icon = get_icon($filename);

                $iconshow = '<img src=' . XOOPS_URL . '/modules/xmail/images/icon/' . $icon . " align='middle'>";

                if (is_file($filename)) {
                    $size = Prettysize(filesize($filename));
                } else {
                    $size = '0';
                }

                echo "	<tr class='even' >\n";

                echo '		<td>' . $cat_data['fileid'] . "</td>\n";

                echo '		<td >' . $iconshow . '&nbsp;' . $cat_data['filerealname'] . "</td>\n";

                echo '		<td >' . $cat_data['minetype'] . "</td>\n";

                echo '		<td >' . $cat_data['filedescript'] . "</td>\n";

                echo '		<td>' . $size . "</td>\n";

                echo '		<td>' . $cat_data['dir_upload'] . "</td>\n";

                echo "		<td><a href='gerencia.php?op=anexar&id_men=" . $id_men . '&fileid=' . $cat_data['fileid'] . "'>" . _MD_XMAIL_ANEXAR . "</a></td>\n";

                echo "	</tr>\n";
            }

            echo '</table>';
        }
    }

    public function files_anexos($id_men)
    {
        global $xoopsDB;

        // pegar todos arquvos anexos na mensagem $id_men

        // retornar string  tipo exemplo: (1,2,3,4)

        $sql = 'SELECT * from ' . $xoopsDB->prefix('xmail_men_anexo') . " as anexo
            where idmen='$id_men'  ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        $arqs = '';

        if ($xoopsDB->getRowsNum($result) > 0) {
            $arqs = '(';

            $primeiro = true;

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                if (!$primeiro) {
                    $arqs .= ',';
                } else {
                    $primeiro = false;
                }

                $arqs .= $cat_data['fileid'];
            }

            $arqs .= ')';
        }

        return $arqs;
    }

    public function array_anexos($id_men)
    {
        global $xoopsDB;

        // pegar todos arquvos anexos na mensagem $id_men

        // retornar matriz

        $sql = 'SELECT files.* ,anexo.idmen  FROM ' . $xoopsDB->prefix('xmail_files') . ' as files  INNER JOIN ' . $xoopsDB->prefix('xmail_men_anexo') . " as anexo
             on files.fileid=anexo.fileid  where  anexo.idmen='$id_men' ";

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        $arqs = [];

        if ($xoopsDB->getRowsNum($result) > 0) {
            $i = 0;

            while (false !== ($cat_data = $xoopsDB->fetchArray($result))) {
                $arqs[$i]['file'] = trim(XOOPS_ROOT_PATH . '/' . $cat_data['dir_upload'] . '/' . $cat_data['filerealname']);

                $arqs[$i]['fileid'] = $cat_data['fileid'];

                $arqs[$i]['filerealname'] = $cat_data['filerealname'];

                $i++;
            }
        }

        return $arqs;
    }

    // function getMinetype extraída do módulo WFsection (wfsfiles.php)

    public function getMinetype($format = 'S')
    {
        $myts = MyTextSanitizer::getInstance();

        $smiley = 0;

        switch ($format) {
            case 'S':
            case 'Show':
                $filemimetype = htmlspecialchars($this->minetype, $smiley);
                break;
            case 'E':
            case 'Edit':
                $filemimetype = htmlspecialchars($this->minetype, ENT_QUOTES | ENT_HTML5);
                break;
            case 'P':
            case 'Preview':
                $filemimetype = htmlspecialchars($this->minetype, $smiley);
                break;
            case 'F':
            case 'InForm':
                $filemimetype = htmlspecialchars($this->minetype, ENT_QUOTES | ENT_HTML5);
                break;
        }

        return $filemimetype;
    }
} // fecha class / close class

class men_anexo
{
    public $fileid;

    public $idmen;

    public function __construct()
    {
        $this->fileid;

        $this->idmen = '';
    }

    public function incluir()
    {
        global $xoopsDB;

        if ($this->validar('I')) {
            $sql = 'INSERT INTO ' . $xoopsDB->prefix('xmail_men_anexo');

            $sql .= ' (fileid,idmen)';

            $sql .= ' VALUES (';

            $sql .= "'" . $this->fileid . "',";

            $sql .= "'" . $this->idmen . "')";

            $result = $xoopsDB->queryF($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
  

        return false;
    }

    public function validar($opt)
    {
        global $men_erro, $xoopsDB;

        return true;
    }

    public function excluir($idmen, $fileid = 0)
    {
        global $xoopsDB;

        $this->idmen = $idmen;

        $sql = 'DELETE FROM ' . $xoopsDB->prefix('xmail_men_anexo');

        $sql .= " WHERE idmen='$idmen' ";

        if ($fileid > 0) {
            $sql .= " and fileid='$fileid' ";
        }

        $result = $xoopsDB->queryF($sql);

        if (!$result) {
            return false;
        }

        return true;
    }
}  // fecha classe


