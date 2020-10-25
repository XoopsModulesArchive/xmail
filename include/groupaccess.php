<?php
// $Id: Groupsaccess.php,v 1.4Date: 06/01/2003,
// Original Author: Halfdead
// Update Author: Catzwolf
// 				  Danielblues
//Exp $

/* -----------------------------------------------------------------------------------------
Useage:
See included Docs
------------------------------------------------------------------------------------------*/

if (!function_exists('listGroups')) {
    function listGroups($grps = '-1')
    {
        global $xoopsDB, $myts;

        $result = $xoopsDB->queryF('SELECT groupid, name FROM ' . $xoopsDB->prefix('groups') . ' ORDER BY name DESC');

        if (!is_array($grps)) {
            $grps = explode(' ', $grps);
        }

        $grouplist = "<select name='groupid[]' size='5' multiple='multiple'>";

        while (list($groupid, $name) = $xoopsDB->fetchRow($result)) {
            $grouplist .= "<option value='$groupid'";

            if (@in_array($groupid, $grps, true) || @in_array('-1', $grps, true)) {
                $grouplist .= " selected='selected'";
            }

            $grouplist .= '>' . htmlspecialchars($name, ENT_QUOTES | ENT_HTML5) . '</option>';
        }

        $grouplist .= '</select>';

        echo $grouplist;
    }
}
/*
checkAccess()

See docs for usage
*/

if (!function_exists('checkAccess')) {
    function checkAccess($grps, $time = -1, $message = '')
    {
        global $xoopsUser, $xoopsModule;

        $groupid = is_object($xoopsUser) ? $xoopsUser->getGroups() : [XOOPS_GROUP_ANONYMOUS];

        $grps = explode(' ', $grps);

        foreach ($groupid as $group) {
            //if ((in_array($group, $grps) || $xoopsUser && $xoopsUser->isAdmin() )) {

            if (in_array($group, $grps, true)) {
                return 1;
            } elseif (-1 != $time) {
                redirect_header('javascript:history.back(1);', $time, $message);

                exit();
            }
  

            return 0;
        }

        return 0;
    }

    /*
    $grps = explode(" ", $grps);

       if ( $xoopsUser && $xoopsUser->isAdmin() ) {
         return 1;

       } else {
            for ($i=0; $i<count($grps); $i++) {
                if (@in_array($grps[$i], $groupid)) {
                    return 1;
                }
            }
        }
    return 0;
    */
}

if (!function_exists('checkadminAccess')) {
    function checkadminAccess($grps)
    {
        global $xoopsUser, $xoopsModule;

        $groupid = is_object($xoopsUser) ? $xoopsUser->getGroups() : [XOOPS_GROUP_ANONYMOUS];

        $grps = explode(' ', $grps);

        for ($i = 0, $iMax = count($grps); $i < $iMax; $i++) {
            if (@in_array($grps[$i], $groupid, true)) {
                return 0;
            }
        }

        return 1;
    }
}
/*
saveAccess()

See docs for usage
*/
if (!function_exists('saveAccess')) {
    function saveAccess($grps)
    {
        if (is_array($grps)) {
            $grps = implode(' ', $grps);
        }

        return ($grps);
    }
}

if (!function_exists('getGroupIda')) {
    function getGroupIda($grps)
    {
        $ret = [];

        if (!is_array($grps)) {
            $ret = explode(' ', $grps);
        }

        return $ret;
    }
}
