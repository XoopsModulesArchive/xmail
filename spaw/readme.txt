SPAW WYSIWYG Editor 1.0.7 For xoops 2+


The SPAW editor class for xoops based on lastest SPAW edition (1.0.7 by Oct 2004).  

The class files have been kept as much as possible for further adaption from SPAW.

How to use it:
1 download the zip file and uncompress it: xoops_editor_spaw.zip. [Once you see this readme, you should have done this step; otherwise, kill me!]
2 upload the folder "spaw" to XOOPSROOT/class/
3 use it: We strongly recommend the NewBB 2.0 (http://xoops2.org or http://dev.xoops.org/modules/xfmod/project/?newbb) from which you can learn the methods of integrating almost all WYSIWYG editors to xoops.
  ANyway, an example for news module, storyform.inc.php:
==================================================================================================================
if ( is_readable(XOOPS_ROOT_PATH . "/class/spaw/formspaw.php"))	{
	include_once XOOPS_ROOT_PATH . "/class/spaw/formspaw.php";
	$editor = new XoopsFormSpaw(_NW_THESCOOP, 'hometext', $hometext, "100%", '300px');
}else{
	$editor = new XoopsFormDhtmlTextArea(_NW_THESCOOP, 'hometext', $hometext, 15, 60, 'hometext_hidden');
}
$sform->addElement($editor, true);
unset($editor);
==================================================================================================================


phppp (D.J.)
http://xoops.org.cn