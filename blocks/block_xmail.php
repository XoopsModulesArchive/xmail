<?php

function b_xmail_show_ativa($options)
{
    $bloco = [];

    $bloco['title'] = '_MB_XMAIL_TITBLOCO1';

    $bloco['content'] = '';

    $bloco['content'] .= '<table  cellspacing="0">';

    $bloco['content'] .= "<tr><td><a href='" . XOOPS_URL . "/modules/xmail/include/xmail_ativa.php'>" . _MB_XMAIL_DESCRIBLOCO1 . '</a></td></tr>';

    $bloco['content'] .= '</table>';

    return $bloco;
}

function b_xmail_edit_ativa($options)
{
}

function b_xmail_show_news($options)
{
    global $xoopsModule, $xoopsConfig;

    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $block['lang_tooltip1'] = _MB_XMAIL_BTOOLTIP1;

    $block['lang_tooltip2'] = _MB_XMAIL_BTOOLTIP2;

    $block['subscr_url'] = XOOPS_URL . '/modules/xmail/include/xnews.php?action=subscribe';

    $block['unsubscr_url'] = XOOPS_URL . '/modules/xmail/include/xnews.php?action=unsubscribe';

    $block['news_images'] = sprintf('%s/modules/xmail/language/%s/', XOOPS_URL, $xoopsConfig['language']);

    $query = 'SELECT count(user_id) as number FROM ' . $db->prefix('xmail_newsletter') . " WHERE confirmed='1'";

    if (!$result = $db->query($query)) {
        return false;
    }

    $arr = $db->fetchArray($result);

    $block['pepole_subscribed'] = htmlspecialchars(sprintf(_MB_XMAIL_SUBSCRIBED_PEOPLE, $arr['number']), ENT_QUOTES | ENT_HTML5);

    return $block;
}

function b_xmail_edit_news($options)
{
}

//-----  não pertence ao módulo   deixada para exemplo
//function Membros_Online ($options)
//{
//global $xoopsUser, $xoopsModule, $HTTP_SERVER_VARS;
//$bloco = array();
//$bloco['title'] = _XT_MO_BLOCO;
//$bloco['content'] = '';
////abaixo o valor para Coldspan nas célular que ocupam uma linha inteira
//$colspan = $options[2] + $options[4] + 1;
//$onlineHandler = xoops_getHandler('online');
//// mt_srand((double)microtime()*1000000);
//// Abaixo vai deletar TODOS as informações da tabela Online que não foram atualizadas durante um certo período
//if (mt_rand(1, 100) < 11) {
//	$onlineHandler->gc(300);
//	}
//// Início da rotina que grava as informações do user/navegação na tabela 'online'
//if (is_object($xoopsUser)) {
//		$uid = $xoopsUser->getVar('uid');
//		$uname = $xoopsUser->getVar('uname');
//	} else {
//		$uid = 0;
//		$uname = '';
//	}
//	if (is_object($xoopsModule)) {
//		$onlineHandler->write($uid, $uname, time(), $xoopsModule->getVar('mid'), $HTTP_SERVER_VARS['REMOTE_ADDR']);
//	} else {
//		$onlineHandler->write($uid, $uname, time(), 0, $HTTP_SERVER_VARS['REMOTE_ADDR']);
//	}
////Fim da Rotina
////Pega todo o conteúdo da tabela 'online'
//$onlines =& $onlineHandler->getAll();
//// Exibe o título do bloco
//$bloco['content'] .= '<table  cellspacing="0">';
//// Se não tem ninguém visitante online... então o bloco está sendo visualizado por um fantasma
//if (false !== $onlines) {
//// Conta quantos registros tem na tabela 'online'
//$total = count($onlines);
////Prepara visitantes e membros para loop pela tabela
//$visitantes = 0;
//$membros = 0;
////Início do Loop
//for ($i = 0; $i < $total; $i++) {
//if ($onlines[$i]['online_uid'] > 0) {
//				$membros++;
//			} else {
//				$visitantes++;
//			}
//		}
//// Fim do loops resultando nas variáveis $visitantes e $membros prontas para uso
////Exibe o número de pessoas online, dividido por membros, visitantes e total
//$bloco['content'] .= '<tr><td colspan="'.$colspan.'"><b>'._XT_MO_TOTAL.':</b> '.$total.'<br><b>'._XT_MO_MEMBROS.':</b> '.$membros.'<br><b>'._XT_MO_VISITANTES.':</b> '.$visitantes.'</td></tr>';
//$moduleHandler = xoops_getHandler('module');
////Agora pega apenas os membros da tabela 'online'
//$somembros = $onlineHandler->getAll(new Criteria('online_uid', 0, '>'));
//if (false !== $somembros) {
//// Conta quantos membros tem online
//$total_membros = count($somembros);
//// Gera lista de módulos ativos
//$modules = $moduleHandler->getList(new Criteria('isactive', 1));
////Início do Loop que listará os Membros Online
//for ($i = 0; $i < $total_membros; $i++) {
//if ($somembros[$i]['online_uid'] == 0) {
//	$onlineUsers[$i]['user'] = '';
//	} else {
//	$onlineUsers[$i]['user'] = new XoopsUser($somembros[$i]['online_uid']);
//	}
//	$onlineUsers[$i]['ip'] = $somembros[$i]['online_ip'];
//	$onlineUsers[$i]['updated'] = $somembros[$i]['online_updated'];
//	$onlineUsers[$i]['module'] = ($somembros[$i]['online_module'] > 0) ? $modules[$somembros[$i]['online_module']] : 'Home';
//	}
////Fim do Loop
////Verifica se quem está vendo o bloco é administrador--- ? 1 : 0
//$administrador = ($xoopsUser && $xoopsUser->isAdmin()) ? 1 : 0;
////Agora faz o Loop Listando somente o número de visitantes que serão exibidos no bloco
//for ($i = 0; $i < $options[0]; $i++) {
//if (@is_object($onlineUsers[$i]['user'])) {
//	$bloco['content'] .= '<tr>';
////Se está definido para exibir o avatar...
//if ($options[3] == 1){
//	$oavatar = $onlineUsers[$i]['user']->getVar('user_avatar') ? '<img src="'.XOOPS_UPLOAD_URL.'/'.$onlineUsers[$i]['user']->getVar('user_avatar').'" alt="" width="32">' : '&nbsp;';
//	$bloco['content'] .= '<td align="center" valign="middle" class="even">'.$oavatar.'<br><a href="'.XOOPS_URL.'/userinfo.php?uid='.$onlineUsers[$i]['user']->getVar('uid').'">'.$onlineUsers[$i]['user']->getVar('uname').'</a>';
//// Se não está definido para exibir o avatar, mostra só o nome
//	}else{
//   $bloco['content'] .= '<td align="center" valign="middle" class="even"><a href="'.XOOPS_URL.'/userinfo.php?uid='.$onlineUsers[$i]['user']->getVar('uid').'">'.$onlineUsers[$i]['user']->getVar('uname').'</a>';
//    }
////Confere se está definido para exibir o Módulo no qual o membro se encontra
//if ($options[4] == 1){
//$bloco['content'] .= '<br><small>'.$onlineUsers[$i]['module'].'</small>';
//}
////Confere se está definido para mostrar o IP e se quem está vendo o bloco é administrador
//if ($administrador == 1 && $options[1] == 1) {
//				$bloco['content'] .= '<br><samp>('.$onlineUsers[$i]['ip'].')</samp></td>';
//			}else{
//$bloco['content'] .= '</td>';
//}
////Confere se está definido para exibir a coluna de mensagem privada ou e-mail
//if ($options[2] == 1) {
////Se quem está vendo o bloco for visitante registrado...
//if ($xoopsUser != '') {
////Mostra link para MP
//$bloco['content'] .= '<td class="odd" width="20%" align="right" valign="middle"><a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/pmlite.php?send2=1&amp;to_userid='.$onlineUsers[$i]['user']->getVar('uid').'\',\'pmlite\',450,370);"><img src="'.XOOPS_URL.'/images/icons/pm_small.gif" border="0" width="27" height="17" alt=""></a></td>';
////Caso contrário...
//}else{
////Confere se o Membro online autorizou a exibição de seu e-mail...
//if ($onlineUsers[$i]['user']->getVar('user_viewemail')) {
//$bloco['content'] .= '<td class="odd" width="20%" align="right" valign="middle"><a href="mailto:'.$onlineUsers[$i]['user']->getVar('email').'"><img src="'.XOOPS_URL.'/images/icons/em_small.gif" border="0" width="16" height="14" alt=""></a></td>';
////Se não está autorizado a exibição do e-mail
//}else{
////Não exibe NADA na coluna
//$bloco['content'] .= '<td class="odd" width="20%" align="right" valign="middle">&nbsp;</td>';
//}
//}
//}
//$bloco['content'] .= '</tr>';
//}
////Fim do Loop
//}
////Se não encontrou nenhum membro online, exibe uma frase
//}else{
//$bloco['content'] .= '<tr><td>'._XT_MO_NENHUM.'</td></tr>';
//}
//}
////Exibe o Link para visualizar TODOS os visitantes online
//$bloco['content'] .= '<tr><td colspan="'.$colspan.'"><div align="right"><a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/misc.php?action=showpopups&amp;type=online\',\'Online\',420,350);">'._XT_MO_MAIS.'</a></div></td></tr></table>';
//return $bloco;
//}
//
//function Membros_Online_Edita ($options)
//{
//	$membros = "<input type='text' name='options[]' value='".intval($options[0])."'>";
//	$form = sprintf(_XT_MO_EXIBIR,$membros);
//	// Mostrar IP ?
//	$form .= "<br>"._XT_MO_IP."&nbsp;<input type='radio' id='options[1]' name='options[1]' value='1'";
//	if ( $options[1] == 1 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._YES."<input type='radio' id='options[1]' name='options[1]' value='0'";
//	if ( $options[1] == 0 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._NO."";
//	//Mostrar MP ?
//		$form .= "<br>"._XT_MO_MP."&nbsp;<input type='radio' id='options[2]' name='options[2]' value='1'";
//	if ( $options[2] == 1 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._YES."<input type='radio' id='options[2]' name='options[2]' value='0'";
//	if ( $options[2] == 0 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._NO."";
//	//Mostrar Avatar?
//		$form .= "<br>"._XT_MO_AVATAR."&nbsp;<input type='radio' id='options[3]' name='options[3]' value='1'";
//	if ( $options[3] == 1 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._YES."<input type='radio' id='options[3]' name='options[3]' value='0'";
//	if ( $options[3] == 0 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._NO."";
//	//Mostrar Módulo?
//		$form .= "<br>"._XT_MO_MODULO."&nbsp;<input type='radio' id='options[4]' name='options[4]' value='1'";
//	if ( $options[4] == 1 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._YES."<input type='radio' id='options[4]' name='options[4]' value='0'";
//	if ( $options[4] == 0 ) {
//		$form .= " checked";
//	}
//	$form .= ">&nbsp;"._NO."";
//	return $form;
//}
