<?php
// *********************************************
// *    Plugin AN Online Consultant            *
// *          Operator Groups                  *
// *    Alex & Natty studio                    *
// *        http://portal30.ru                 *
// *                                           *
// *            © Alex & Natty Studio  2011    *
// *********************************************
defined('COT_CODE') or die('Wrong URL.');

/**
 * Получить список групп оператора
 * @param type $operatorid
 * @return type 
 */
function oc_get_operator_groupslist($operatorid){
	global $cfg;
    // TODO операторы по группам
	if ($settings['enablegroups'] == '1') {
		$groupids = array(0);
		$allgroups = select_multi_assoc("select groupid from ${mysqlprefix}chatgroupoperator where operatorid = $operatorid order by groupid", $link);
		foreach ($allgroups as $g) {
			$groupids[] = $g['groupid'];
		}
		return implode(",", $groupids);
	} else {
		return "";
	}
}

/**
 * Получить группы консультанов
 * те группы, у которых права 'W' на этот плагин (auth_rights&2)
 * плюс администраторы в зависимости от настроек
 * @global CotDb $db
 * @return array|bool
 */
function oc_getOperatorsGroups(){
    global $db_groups, $db_auth, $oC_consGroups, $cfg, $db;
    
    if (!$oC_consGroups || count($oC_consGroups)==0){
        $oC_consGroups = array();
        
        $query = "SELECT auth_groupid FROM $db_auth AS a
                    LEFT JOIN $db_groups AS g ON g.grp_id=a.auth_groupid
                    WHERE grp_disabled=0 AND a.auth_code='oconsultant' AND a.auth_option='a' AND auth_rights&2
                        AND auth_groupid!=5
                    ORDER BY a.auth_groupid ASC";
        $res = $db->query($query);

        while ($row = $res->fetch()) {
            $oC_consGroups[] = (int)$row['auth_groupid'];
        }
        
        if($cfg['oconsultant']['admCons'] == 'yes'){
            $oC_consGroups[] = 5;
        }
        if (count($oC_consGroups)>0){
            sort($oC_consGroups);
        }else{
            $oC_consGroups = NULL;
            return false;
        }
    }

    return $oC_consGroups;
}
