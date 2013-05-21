<?php
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage Ajax
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * Получить список операторов он-лайн, для консоли оператора
 */
function oc_getOnlineOperatorsArr(){
    global $cot_usersonline, $db, $db_online;

    // Кастыль для ошибки в движке при вклченном кеше
    // todo Убрать после исправления https://github.com/Cotonti/Cotonti/issues/1010
    if(!isset($cot_usersonline)){
        $cot_usersonline = $db->query("SELECT DISTINCT o.online_userid FROM $db_online o WHERE o.online_name != 'v'
         ORDER BY online_name ASC")->fetchAll(PDO::FETCH_COLUMN);
    }
    // /Кастыль
	$ret = array();
	$operators = oc_operator_get_all();
	foreach ($operators as $operator) {
        if (!cot_userisonline($operator['user_id']))  continue;
        $ret[] = array(
            'name' => htmlspecialchars($operator['user_name']),
            'away' => oc_operator_is_away($operator) ? 1 : 0
        );
	}
	return $ret;
}

/**
 * Получить список список ожижающих диалогов, для консоли оператора
 * @param type $groupids
 * @param type $since
 * @return array
 */
function oc_getPendingThreadsArr($groupids, $since){
	global $cfg, $threadstate_to_string, $threadstate_key, $cot_groups, $usr, $oc_can_takeover, $oc_can_viewthreads,
           $db_users, $online_timedout, $sys, $db;
    
    // $online_timedout - выставляется движком Cotonti. Время по истечении которого пользователь считается офф лайн
    // Настраивается в /admin.php?m=config&n=edit&o=core&p=time ( Управление сайтом / Конфигурация / Время и дата )
    
	$revision = $since;

    $cond = array(
        array('lrevision', $since, '>'),
    );
    if( $cfg['oconsultant']['showThreads'] == 'online'){
        if($since <= 0){
           $cond[] = array('SQL', "(lastpinguser > '".date('Y-m-d H:i:s', $online_timedout).
                "' OR lastpingagent > '".date('Y-m-d H:i:s', $online_timedout)."')");
        }
    }
    if ($since <= 0){
        $cond[] = array('istate', OcThread::STATE_CLOSED, '<>');
        $cond[] = array('istate', OcThread::STATE_LEFT, '<>');
    }
    
    $threads = OcThread::find($cond);
    $ret = array('threads' => array());

    if($threads){
        foreach($threads as $thread){
            $state = $threadstate_to_string[$thread->istate];

            $th = array(
                'id' => $thread->threadid,
                'stateid' => $state,
                'canopen' => 'false',
                'canview' => 'false',
                'message' => ''
            );
            if($state == "closed"){
                $ret['threads'][] = $th;
                continue;
            }

            $state = $threadstate_key[$thread->istate];
            $groupTitle = '';
            if($thread->groupid) $groupTitle = $cot_groups[$thread->groupid];

            // TODO oc_operator_by_id_ , oc_get_operator_name
            $nextagent = $thread->nextagent != 0 ? oc_operator_by_id_($thread->nextagent) : null;
            $threadoperator = $nextagent ? oc_get_operator_name($nextagent)
                : ($thread->agentName ? $thread->agentName : "-");
            if ($threadoperator == "-" && $groupTitle) {
                $threadoperator = "- " . $groupTitle . " -";
            }

            if ( !($thread->istate == OcThread::STATE_CHATTING && $thread->agentId != $usr['id']
                && !cot_auth('oconsultant', 'any', $oc_can_takeover)) ) {
                $th['canopen'] = "true";
            }

            if ($thread->agentId != $usr['id'] && $thread->nextagent != $usr['id']
                        && cot_auth('oconsultant', 'any', $oc_can_viewthreads)) {
                $th['canview'] = "true";
            }

            $th['state'] = $state;
            $th['typing'] = $thread->userTyping;
            // TODO ссылка на профиль пользователя
            $th['name'] = htmlspecialchars(oc_buildUserName($thread->userName, $thread->remote, $thread->userid));
            $th['addr'] = htmlspecialchars(oc_buildUserAddr($thread->remote));
            $th['agent']= htmlspecialchars($threadoperator);
            $th['time'] = strtotime($thread->dtmcreated) . "000";
            $th['modified'] = strtotime($thread->dtmmodified)."000";

            $userAgent = oc_get_useragent_version($thread->userAgent);
            $th['useragent'] = $userAgent;

            if ($thread->shownmessageid > 0) {
                $msg = OcMessage::getById($thread->shownmessageid);
                if ($msg) {
                    $message = preg_replace("/[\r\n\t]+/", " ", $msg->tmessage);
                    $th['message'] = htmlspecialchars($message);
                }
            }

            $ret['threads'][] = $th;
            if ($thread->lrevision > $revision)	$revision = $thread->lrevision;
        }
    }

    $ret['revision'] = $revision;
    $ret['time'] = $sys['now'] . "000";

    return $ret;
}


/**
 * Получить список пользователей он-лайн, для консоли оператора
 * @return array
 */
function oc_getUsersOnLineArr(){
    global $cfg, $db_online, $db_users, $L, $db_oc_invite, $usr, $db_oc_thread,
           $oc_can_takeover, $oc_can_viewthreads, $db, $sys;

    $ret = array(
        'time' => $sys['now'] . "000",
        'users' => array(),
        'guests' => array()
    );

//    $showavatars = $cfg['plugin']['whosonline']['showavatars'];
//    $miniavatar_x = $cfg['plugin']['whosonline']['miniavatar_x'];
//    $miniavatar_y = $cfg['plugin']['whosonline']['miniavatar_y'];

    $sql1 = $db->query("SELECT DISTINCT u.*, o.*, i.inv_status, i.agentId as inv_agentId,
        i.threadid as inv_threadid, t.istate, t.agentId, t.nextagent, i.inv_dtsended, i.inv_answed
            FROM $db_online AS o 
            LEFT JOIN $db_users AS u ON u.user_id=o.online_userid
            LEFT JOIN $db_oc_invite AS i ON u.user_id=i.user_id
            LEFT JOIN $db_oc_thread AS t ON t.threadid=i.threadid
            WHERE online_name!='v' ORDER BY u.user_name ASC");

    $sql2 = $db->query("SELECT o.online_id, o.online_ip, o.online_lastseen, o.online_location, o.online_subloc,
                i.inv_status, i.agentId as inv_agentId, o.online_location_code, o.online_title,
                o.online_breadcrumb, o.online_user_agent, o.online_uri, i.threadid as inv_threadid,
                i.threadid as inv_threadid, t.istate, t.agentId, t.nextagent, i.inv_dtsended, i.inv_answed
            FROM $db_online  AS o 
            LEFT JOIN $db_oc_invite AS i ON o.online_id=i.online_id 
            LEFT JOIN $db_oc_thread AS t ON t.threadid=i.threadid
            WHERE online_name = 'v' ORDER BY online_lastseen DESC");
    
    $total1 = $sql1->rowCount();
    $total2 = $sql2->rowCount();
    
    $visitornum = 0;
    $visituser = 0;
    

    while ($row = $sql1->fetch()){
        $row['inv_sended'] = strtotime($row['inv_dtsended']);
        $row['inv_answed'] = strtotime($row['inv_answed']);
        $sublock = (!empty($row['online_subloc'])) ? " ".$cfg['separator']." ".htmlspecialchars($row['online_subloc']) :
            '';
        
        $userProf = cot_url('users', 'm=details&id='.$row['user_id'].'&u='.$row['user_name']);
        if (!cot_url_check($userProf)) $userProf = COT_ABSOLUTE_URL.$userProf;
        $userProf = '<a href="'.$userProf.'" target="_blank"><i>('.$L['oc']['profile'].')</i></a>';
        $invText = '';
        if ($row['inv_sended'] != ''){
            if ($row['inv_status'] == 0){
                $invText = $L['oc']['invite_sended'].': '.oc_date_to_text($row['inv_sended']);
            }elseif($row['inv_status'] == 1){
                $invText = $L['oc']['invite_accepted'].': '.oc_date_to_text($row['inv_answed']);
            }elseif($row['inv_status'] == 2){
                $invText = $L['oc']['invite_rejected'].': '.oc_date_to_text($row['inv_answed']);
            }
        }else{
            $row['inv_status'] = -1;
        }
        $row['online_breadcrumb'] = trim($row['online_breadcrumb']);

        $user = array(
            'ip' => $row['online_ip'],
            'id' => $row['user_id'],
            'profile' => $userProf,
            'name' => htmlspecialchars($row['user_name']),
            'maingrp' => cot_build_group($row['user_maingrp']),
            'country_flag' => cot_build_flag($row['user_country']),
            'location' => htmlspecialchars($row['user_location']),
            'online_location' => $row['online_location'].$sublock,
            'online_location_code' => htmlspecialchars($row['online_location_code']),
            'online_title' => htmlspecialchars($row['online_title']),
            'online_breadcrumb' => ($row['online_breadcrumb'] != '') ? $row['online_breadcrumb'] : '',
            'online_user_agent' => htmlspecialchars(oc_get_useragent_version($row['online_user_agent'])),
            'online_uri' => $row['online_uri'],
            'online_id' => $row['online_id'],
            'inv_status' => $row['inv_status'],
            'inv_sended' => $row['inv_sended'],
            'inv_text' => htmlspecialchars($invText),
            'inv_agentId' => $row['inv_agentId'],
            'inv_threadid' => $row['inv_threadid'],
            'thr_canopen' => 0,
            'thr_canview' => 0
        );

        if ($row['inv_threadid'] > 0){
            if ( !($row['istate'] == OcThread::STATE_CHATTING && $row['agentId'] != $usr['id']
                    && !cot_auth('oconsultant', 'any', $oc_can_takeover)) ) {
                $user['thr_canopen'] = 1;
            }
            if ($row['agentId'] != $usr['id'] && $row['nextagent'] != $usr['id']
                && cot_auth('oconsultant', 'any', $oc_can_viewthreads)) {
                $user['thr_canview'] = 1;
            }
        }
        $ret['users'][] = $user;
    }
    
    while ($row = $sql2->fetch()) {
        $visitornum++;
        $row['inv_sended'] = strtotime($row['inv_dtsended']);
        $row['inv_answed'] = strtotime($row['inv_answed']);
        $sublock = (!empty($row['online_subloc'])) ? " ".$cfg['separator']." ".htmlspecialchars($row['online_subloc']) :
            '';
        $invText = '';
        if ($row['inv_sended'] != ''){
            if ($row['inv_status'] == 0){
                $invText = $L['oc']['invite_sended'].': '.oc_date_to_text($row['inv_sended']);
            }elseif($row['inv_status'] == 1){
                $invText = $L['oc']['invite_accepted'].': '.oc_date_to_text($row['inv_answed']);
            }elseif($row['inv_status'] == 2){
                $invText = $L['oc']['invite_rejected'].': '.oc_date_to_text($row['inv_answed']);
            }
        }else{
            $row['inv_status'] = -1;
        }

        $guest = array(
            'ip' => $row['online_ip'],
            'id' => 0,
            'name' => $L['Guest'],
            'online_location' => $L[$row['online_location']].$sublock,
            'online_id' => $row['online_id'],
            'online_location_code' => htmlspecialchars($row['online_location_code']),
            'online_title' => htmlspecialchars($row['online_title']),
            'online_breadcrumb' => $row['online_breadcrumb'],
            'online_user_agent' => oc_get_useragent_version($row['online_user_agent']),
            'online_uri' => $row['online_uri'],
            'inv_status' => $row['inv_status'],
            'inv_sended' => $row['inv_sended'],
            'inv_text'   => htmlspecialchars($invText),
            'inv_agentId' => $row['inv_agentId'],
            'inv_threadid'=> $row['inv_threadid'],
            'thr_canopen' => 0,
            'thr_canview' => 0,
        );
        
        if ($row['inv_threadid'] > 0){
            if ( !($row['istate'] == OcThread::STATE_CHATTING && $row['agentId'] != $usr['id']
                    && !cot_auth('oconsultant', 'any', $oc_can_takeover)) ) {
                $guest['thr_canopen'] = 1;
            }
            if ($row['agentId'] != $usr['id'] && $row['nextagent'] != $usr['id']
                && cot_auth('oconsultant', 'any', $oc_can_viewthreads)) {
                $guest['thr_canview'] = 1;
            }
        }
        $ret['guests'][] = $guest;
    }
    return $ret;
}