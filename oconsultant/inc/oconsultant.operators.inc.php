<?php
// *********************************************
// *    Plugin AN Online Consultant            *
// *          Operators                        *
// *    Alex & Natty studio                    *
// *        http://portal30.ru                 *
// *                                           *
// *            © Alex & Natty Studio  2011    *
// *********************************************
defined('COT_CODE') or die('Wrong URL.');
require_once cot_incfile('oconsultant', 'module', 'groups.inc');

$oc_can_takeover = 1; // Возможность Перехватывать диалоги у других операторов 
$oc_can_viewthreads = 2; // Просматривать диалоги других операторов в режиме реального времени 

/**
 * Получить всех операторов
 * @return type 
 */
function oc_operator_get_all(){
	global $db_users, $db_groups_users, $sys, $db;
    
    $operators = array();
    $uGroups = oc_getOperatorsGroups();
    
    if ( $uGroups && count($uGroups)>0){
        $orWhere[] = "u.user_maingrp IN (".implode(', ', $uGroups).")";
        $orWhere[] = "m.gru_groupid IN (".implode(', ', $uGroups).")";
    }
    
    if (count($orWhere) > 0){
        $query = "SELECT u.user_id, u.user_name, ({$sys['now_offset']}-u.user_lastlog) as time 
                  FROM $db_users as u
                  LEFT JOIN $db_groups_users as m ON m.gru_userid=u.user_id
                  WHERE (".implode(' OR ', $orWhere).")
                  ORDER BY u.user_name
        ";
        $res = $db->query($query);
        while($row = $res->fetch()){
            $operators[] = $row;
        }
    }

	return $operators;
}

/**
 * Отошел ли оператор
 * @param type $operator
 * @return type 
 */
function oc_operator_is_away($operator){
	global $cfg;
    //var_dump($operator);
    return (cot_userisonline($operator['user_id']) &&
            $operator['time'] > $cfg['oconsultant']['online_timeout']) ? "1" : "";
}

/**
 * Есть операторы online ?
 * @global type $settings
 * @global type mysqlprefix
 * @global type $mysqlprefix
 * @param type $groupid
 * @return boolean
 */
function oc_has_online_operators($groupid = ""){
	global $db_online, $db_users, $db_groups_users, $cfg, $db;
    
    $orWhere = array();
    $uGroups = oc_getOperatorsGroups();
    
    // TODO Если передан id группы, то проверить только консультантов внутри этой группы
    if ( $uGroups && count($uGroups)>0){
        $orWhere[] = "u.user_maingrp IN (".implode(', ', $uGroups).")";
        $orWhere[] = "m.gru_groupid IN (".implode(', ', $uGroups).")";
    }
    
    if (count($orWhere) > 0){
         $query = "SELECT COUNT(*) FROM $db_online AS o
                LEFT JOIN $db_users AS u ON u.user_id=o.online_userid
                LEFT JOIN $db_groups_users as m ON m.gru_userid=o.online_userid
                WHERE online_name!='v' AND (".implode(' OR ', $orWhere).")
                ORDER BY u.user_name ASC";
        
        //var_dump($query);
        //$sql = sed_sql_query($query);
        $total = $db->query($query)->fetchColumn();
        if ($total > 0) return true;
    }
    return false;
}