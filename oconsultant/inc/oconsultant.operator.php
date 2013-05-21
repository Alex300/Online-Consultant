<?php
defined('COT_CODE') or die('Wrong URL.');

cot_block($usr['auth_write'] || $usr['isadmin']);  // только для операторов

/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage Operator
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
class OperatorController{

    /**
     * Панель оператора
     */
    public function indexAction() {
        global $L, $cfg, $out;

        $sys['sublocation'] = $L['oc']['topMenu_admin'];
        $out['subtitle'] = $L['oc']['topMenu_admin'];
        $out['canonical_uri'] = cot_url('oconsultant', array());

        $crumbs = array(
            $L['oc']['title']
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        // Все глобальные переменные определяем до инициализации шаблона
        $t = new XTemplate(cot_tplfile('oconsultant'));

        $t->parse('MAIN.MENU');

        oc_checkJsFiles(true);

        $t->assign(array(
            'PAGE_TITLE'        => $L['oc']['topMenu_admin'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            'USERS_SUBTITLE'    => $L['use_subtitle'],
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');

    }

    public function usersAction(){
        global $L, $cfg, $out, $sys, $oc_jsver, $cot_modules ;

        cot_rc_link_file($cfg['modules_dir'].'/oconsultant/tpl/users.css', 'css');    // без консолидации

        //var_dump($cfg['oconsultant']);

        $sys['sublocation'] = $L['oc']['clients_title'];
        $out['subtitle'] = $L['oc']['clients_title'];
        $out['canonical_uri'] = cot_url('oconsultant', array('a'=>'users'));

        // Подключаем JS
        $jsVars = '
        var localized = {
            "open":"'.$L['oc']['pending_table_speak'].'",
            "view_thread":"'.$L['oc']['pending_table_view'].'",
            "ban":"'.$L['oc']['pending_table_ban'].'",
            "popup_notify":"'.$L['oc']['pending_popup_notification'].'",
            "invite":"'.$L['oc']['invite_user_to_chat'].'",
            "page":"'.$L['Page'].'",
            "cancel":"'.$L['Cancel'].'",
            "submit":"'.$L['Submit'].'",
            "path": "'.$L['Path'].'"
        };
        var updaterOptions = {
            url:"'.cot_url('oconsultant', "m=operator&a=update", '', true).'",
            wroot:"'.$cfg['modules_dir'].'/oconsultant",
            agentservl:"'.cot_url('oconsultant', "m=operator&a=open", '', true).'",
            frequency: '.(int)$cfg['oconsultant']['updatefrequency_operator'].',
            istatus: '.(isset($_GET['away']) ? 1 : 0).',
            noclients:"'.$L['oc']['clients_no_clients'].'",
            showpopup: '.$cfg['oconsultant']['enablepopupnotification'].',
            showonline: '.$cfg['oconsultant']['showonlineoperators'].',
            opcanstart: '.$cfg['oconsultant']['operatorCanStart'].',
            x: "'.$sys['xk'].'"
        };';
        cot_rc_embed_footer($jsVars);
        unset($jsVars);
        cot_rc_link_footer($cfg['modules_dir'].'/oconsultant/js/'.$oc_jsver.'/common.js?'.$cot_modules['oconsultant']['version']);
        cot_rc_link_footer($cfg['modules_dir'].'/oconsultant/js/'.$oc_jsver.'/users.js?'.$cot_modules['oconsultant']['version']);
        // /Подключаем JS

        $crumbs = array(
            array(cot_url('oconsultant'), $L['oc']['title']),
            $L['oc']['topMenu_users'],
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        $canned_messages = oc_load_canned_messages();
        $canned_messages[0] = $L['oc']['chat_window_predefined_select_answer']."...";
        // Все глобальные переменные определяем до инициализации шаблона
        $t = new XTemplate(cot_tplfile('oconsultant'));

        $t->assign(array(
            'PREDEFINEDANSWERS' => cot_selectbox(0, 'predefined', array_keys($canned_messages),
                array_values($canned_messages), false, array('id'=>'predefined')),
        ));
        $t->parse('MAIN.PENDING_USERS');

        $t->assign(array(
            'PAGE_TITLE'        => $L['oc']['clients_title'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            //'USERS_SUBTITLE'    => $L['use_subtitle'],
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }

    /**
     * Ajax. Обновить страницу очереди
     */
    public function updateAction(){
        global $L, $threadstate_to_string, $threadstate_key, $db_x, $usr;

        $is_consultant = cot_auth('oconsultant', 'any', 'W');
        if (!$is_consultant){
            ob_clean();
            oc_start_xml_output();
            echo "<error><descr>" . oc_escape_with_cdata($L['oc']['agent_not_logged_in']) . "</descr></error>";
            exit;
        }
        $threadstate_to_string = array(
            OcThread::STATE_QUEUE => "wait",
            OcThread::STATE_WAITING => "prio",
            OcThread::STATE_CHATTING => "chat",
            OcThread::STATE_CLOSED => "closed",
            OcThread::STATE_LOADING => "wait",
            OcThread::STATE_LEFT => "closed"
        );

        $threadstate_key = array(
            OcThread::STATE_QUEUE     => $L['oc']['chat_thread_state_wait'],
            OcThread::STATE_WAITING   => $L['oc']['chat_thread_state_wait_for_another_agent'],
            OcThread::STATE_CHATTING  => $L['oc']['chat_thread_state_chatting_with_agent'],
            OcThread::STATE_CLOSED    => $L['oc']['chat_thread_state_closed'],
            OcThread::STATE_LOADING   => $L['oc']['chat_thread_state_loading']
        );

        $act = cot_import('act', 'P', 'ALP');
        // Приглашаем посетителя в чат
        if($act == 'invite_visitor'){
            $online_id = cot_import('ouid', 'P', 'INT');
            if (!$online_id){
                $res = array('error' => 'Wrong UserId');
            }else{
                $res = $this->invite_visitor($online_id);
            }
            cot_sendheaders();
            echo json_encode($res);
            exit();
        }

        $since = cot_import("since", 'P', 'INT');
        if(!$since) $since = 0;
        $status = cot_import("status", 'P', 'INT');
        if($status != 1 || $status != 2) $status = 0;
        $showonline = cot_import("showonline", 'P', 'INT');
        if($showonline != 1) $showonline = 0;

        if (!isset($_SESSION['operatorgroups'])) {
            $_SESSION["{$db_x}operatorgroups"] = oc_get_operator_groupslist($usr['id']);
        }
        $groupids = $_SESSION["{$db_x}operatorgroups"];

        ob_clean();
        $ret = array('error' => '');
        if ($showonline) {
            $ret['operators'] = oc_getOnlineOperatorsArr();
        }
        $ret['whoonline'] = oc_getUsersOnLineArr($groupids, $since);
        $ret['threads'] = oc_getPendingThreadsArr($groupids, $since);

        //oc_notify_operator_alive($operator['operatorid'], $status);
        echo json_encode($ret);
        exit;
    }

    /**
     * Оператор открывает чат
     */
    function openAction(){
        global $oc_state_chatting, $usr, $oc_can_takeover, $oc_can_viewthreads, $L, $ext_display_header;

        $ext_display_header = false;

        // TODO SSL
//    if ($settings['enablessl'] == "1" && $settings['forcessl'] == "1") {
//        if (!is_secure_request()) {
//            $requested = $_SERVER['PHP_SELF'];
//            if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['QUERY_STRING']) {
//                header("Location: " . get_app_location(true, true) . "/operator/agent.php?" . $_SERVER['QUERY_STRING']);
//            } else {
//                die("only https connections are handled");
//            }
//            exit;
//        }
//    }

        $threadid = cot_import('thread', 'P', 'INT');
        if (!$threadid) $threadid = cot_import('thread', 'G', 'INT');

        //$t = new XTemplate(cot_tplfile('oconsultant.chat', true));

        if (!isset($_GET['token'])) {
            $thread = OcThread::getById($threadid);
            if (!$thread || !isset($thread->ltoken)) {
                die("wrong thread");
            }

            $viewonly = cot_import('viewonly', 'G', 'ALP');
            $viewonly = ($viewonly == 'true') ? true : false;

            $forcetake = cot_import('force', 'G', 'BOL');

            // TODO врежме просмотра
            if (!$viewonly && $thread->istate == OcThread::STATE_CHATTING && $usr['id'] != $thread->agentId) {
                if (!cot_auth('oconsultant', 'any', $oc_can_takeover)) {
                    $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

                    cot_error("Cannot take over");
                    // Error and message handling
                    cot_display_messages($t);
                    $t->assign(array(
                        'TITLE' => $L['oc']['chat_window_title'],
                        'HEADER_TITLE' => $L['oc']['chat_window_title'],
                    ));
                    $t->parse('MAIN');
                    return $t->text('MAIN');
                    exit;
                }

                if ($forcetake == false) {
                    $t = new XTemplate(cot_tplfile('oconsultant.dialog'));
                    $userName = htmlspecialchars($thread->userName);
                    $agentName = htmlspecialchars($thread->agentName);
                    $t->assign(array(
                        'TITLE' => $L['oc']['confirm_take_head'],
                        'HEADER_TITLE' => $L['oc']['confirm_take_head'],
                        'CONFIRM_TEXT' => str_replace(array('{0}', '{1}'), array($userName, $agentName),
                            $L['oc']['confirm_take_message']),
                    ));

                    $t->parse('MAIN.CONFIRM_TAKE');
                    $t->assign(array(
                        'BUTTON_TEXT' => $L['oc']['confirm_yes'],
                        'BUTTON_LINK' => cot_url('oconsultant', array('m'=>'operator', 'a'=>'open', 'thread'=>$threadid,
                           'force'=>1 ))
                    ));
                    $t->parse('MAIN.BUTTONS.BUTTON');
                    if(cot_auth('oconsultant', 'any', $oc_can_viewthreads)){
                        $t->assign(array(
                            'BUTTON_TEXT' => $L['oc']['view_tread'],
                            'BUTTON_LINK' => cot_url('oconsultant', array('m'=>'operator', 'a'=>'open', 'thread'=>$threadid,
                                 'viewonly'=>'true' ))
                        ));
                        $t->parse('MAIN.BUTTONS.BUTTON');
                    }
                    $t->parse('MAIN.BUTTONS');

                    $t->parse('MAIN');
                    return $t->text('MAIN');
                    exit;
                }
            }

            if (!$viewonly) {
                $thread->take();
            } else if (!cot_auth('oconsultant', 'any', $oc_can_viewthreads)) {
                $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

                cot_error("Cannot view threads");
                // Error and message handling
                cot_display_messages($t);
                $t->assign(array(
                    'TITLE' => $L['oc']['chat_window_title'],
                    'HEADER_TITLE' => $L['oc']['chat_window_title'],
                ));
                $t->parse('MAIN');
                return $t->text('MAIN');
                exit;
            }

            $token = $thread->ltoken;
            cot_redirect(cot_url('oconsultant', "m=operator&a=open&thread=$threadid&token=$token", '', true));
            exit;
        }

        $token = cot_import("token", "G", 'TXT');

        $thread = OcThread::getById($threadid);
        if (!$thread || !isset($thread->ltoken) || $token != $thread->ltoken) {
            die("wrong thread");
        }

        if ($thread->agentId != $usr['id'] && !cot_auth('oconsultant', 'any', $oc_can_viewthreads)) {
            $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

            cot_error("Cannot view threads");
            // Error and message handling
            cot_display_messages($t);
            $t->assign(array(
                'TITLE' => $L['oc']['chat_window_title'],
                'HEADER_TITLE' => $L['oc']['chat_window_title'],
            ));
            $t->parse('MAIN');
            return $t->text('MAIN');
            exit;
        }
        unset($t);
        $t = new XTemplate(cot_tplfile('oconsultant.chat'));
        $t->assign(OcThread::generateTagsForOperator($thread));

        //start_html_output();

        $pparam = cot_import("act", "G", "ALP");
        // TODO проверить, мож еще и POST $pparam
        if ($pparam == "redirect") {
            setup_redirect_links($threadid, $token);
            expand("../styles", getchatstyle(), "redirect.tpl");
        } else {
            //expand("../styles", getchatstyle(), "chat.tpl");
            $t->parse('MAIN.CHAT');
            $t->parse('MAIN.SUBMIT');

            $t->assign(array(
                'TITLE' => $L['oc']['chat_window_title'],
                'HEADER_TITLE' => $L['oc']['chat_window_title'],
            ));

            $t->parse('MAIN');

            return $t->text('MAIN');
        }

    }

    /**
     * История диалогов
     * @global CotDb $db
     * @return string
     */
    public function historyAction(){
        global $cfg, $L, $db_oc_thread, $db_oc_message, $db, $db_users, $sys, $out;

        $squery = cot_import('q', 'G', 'TXT');

        $t = new XTemplate(cot_tplfile('oconsultant'));

        $sys['sublocation'] = $L['oc']['topMenu_admin'];
        $out['subtitle'] = $L['oc']['page_analysis_search_title'];
        $out['canonical_uri'] = cot_url('oconsultant', array('a'=>'history'));

        $crumbs = array(
            array(cot_url('oconsultant'), $L['oc']['title']),
            $L['oc']['page_analysis_search_title'],
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);


        if ($squery){
            $maxrowsperpage = $cfg['maxrowsperpage'];
//            $maxrowsperpage = 2;
            list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

            $list_url_path = array('m' => 'operator', 'a' => 'history', 'q' => $squery);

            $sql = $db->query("SELECT COUNT(DISTINCT {$db_oc_thread}.dtmcreated) FROM $db_oc_thread, $db_oc_message
			WHERE {$db_oc_message}.threadid = {$db_oc_thread}.threadid AND
                (({$db_oc_thread}.userName LIKE '%%".$db->prep($squery)."%%') OR
                ({$db_oc_message}.tmessage LIKE '%%".$db->prep($squery)."%%'))
            ");
            $totallines = $sql->fetchColumn();

            $sql = "SELECT DISTINCT t.dtmcreated, t.dtmmodified, t.threadid, t.remote, t.agentName, t.agentId, t.userName,
                    t.userid, groupid, messageCount as size, u.user_name
                FROM $db_oc_thread AS t
                JOIN $db_oc_message AS m ON m.threadid = t.threadid
                LEFT JOIN $db_users AS u ON u.user_id=t.userid
                WHERE ((t.userName LIKE '%%".$db->prep($squery)."%%')
                    OR (m.tmessage LIKE '%%".$db->prep($squery)."%%'))
                ORDER BY dtmcreated DESC
                LIMIT $d, $maxrowsperpage";

            $res = $db->query($sql);

            $totalfound = $res->rowCount();

            if ($totalfound > 0){
                $jj=0;
                while ($row = $res->fetch() and ($jj < $maxrowsperpage)){
//                    var_dump($row);
//                    echo "<br /><br />";
                    $row['created'] = strtotime($row['dtmcreated']);
                    $row['modified'] = strtotime($row['dtmmodified']);
                    $operatorName = '-';
                    $operator = '-';

                    if( $row['agentName'] ) {
                        $operatorName = htmlspecialchars($row['agentName']);
                        $operator = cot_build_user($row['agentId'],htmlspecialchars($row['agentName']));
                        // TODO операторы по группам
                    } else if($row['groupid'] && $row['groupid'] != 0 && isset($page['groupName'][$row['groupid']])) {
                        $operatorName =  "- ".topage(htmlspecialchars($page['groupName'][$chatthread['groupid']]))." -";
                        $operator =  $operator;
                    }

                    $userProf = '';
                    if (((int)$row['userid']) == $row['userid'] && $row['user_name'] != ''){
                        //$userProf = sed_build_user($row['userid'],htmlspecialchars($row['userName']));
                        $userProf = cot_url('users', 'm=details&id='.$row['userid'].'&u='.htmlspecialchars($row['user_name']));
                        if (!cot_url_check($userProf)) $userProf = COT_ABSOLUTE_URL.$userProf;
                    }

                    $t->assign(array(
                        'ROW_USERNAME' => htmlspecialchars($row['userName']),
                        'ROW_USER_REAL_NAME' => $row['user_name'],
                        'ROW_USERPROFILE_URL' => $userProf,
                        'ROW_THREADID' => $row['threadid'],
                        'ROW_WIEW_THREAD_URL' => cot_url('oconsultant', '&a=thread&threadid='.$row['threadid']),
                        'ROW_ADDR' => oc_buildUserAddr($row['remote']),
                        'ROW_OPERATOR_NAME' => $operatorName,
                        'ROW_OPERATOR' => $operator,
                        'ROW_MSG_COUNT' => htmlspecialchars($row['size']),
                        'ROW_TIME_IN_CHAT' => oc_date_diff_to_text($row['modified']-$row['created']).', '.oc_date_to_text($row['created']),
                    ));
                    $t->parse('MAIN.THREAD_SERCH.SEARCH_RES.ROW');
                    $jj++;
                }
                // пагинация
                $pagenav = cot_pagenav('oconsultant', $list_url_path, $d, $totallines, $maxrowsperpage);
                if ($pagenav['total'] > 1){
                    $t->assign(array(
                        'LIST_PAGINATION' => $pagenav['main'],
                        'LIST_PAGEPREV' => $pagenav['prev'],
                        'LIST_PAGENEXT' => $pagenav['next'],
                        'LIST_CURRENTPAGE' => $pagenav['current'],
                        'LIST_TOTALLINES' => $totallines,
                        'LIST_MAXPERPAGE' => $maxrowsperpage,
                        'LIST_TOTALPAGES' => $pagenav['total'],
                        'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
                        'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
                    ));
                    $t->parse('MAIN.THREAD_SERCH.PAGINATION');
                }
            }else{
                $t->parse('MAIN.THREAD_SERCH.SEARCH_RES.NOT_FOUND');
            }

            $t->parse('MAIN.THREAD_SERCH.SEARCH_RES');
        }

        $t->assign(array(
            'PAGE_TITLE'        => $L['oc']['page_analysis_search_title'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            'QUERY' => htmlspecialchars($squery),

        ));
        $t->parse('MAIN.THREAD_SERCH');

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');

    }

    /**
     * История одного диалога
     */
    public function threadAction(){
        global $db_oc_thread, $db_groups, $L, $db_users, $db, $ext_display_header;

        $ext_display_header = false;

        $threadid = cot_import("threadid", "G", "INT");

        // TODO обработка ошибки
        if (!$threadid) return false;

        $threadMessages = OcMessage::find(array(array('threadid',$threadid)) );

        // TODO операторы по группам
        $query = "SELECT userid, userName,agentName,agentId,remote,userAgent, grp_title as groupName, u.user_name,
                dtmmodified, dtmcreated
            FROM $db_oc_thread
            LEFT JOIN $db_groups ON {$db_oc_thread}.groupid = {$db_groups}.grp_id
            LEFT JOIN $db_users AS u ON u.user_id={$db_oc_thread}.userid
            WHERE threadid = " . $threadid;
        $result = $db->query($query);
        $thread = $result->fetch();

        $thread['modified'] = strtotime($thread['dtmmodified']);
        $thread['created'] = strtotime($thread['dtmcreated']);
        $userProf = '';
        if (((int)$thread['userid'])==$thread['userid'] && $thread['user_name'] != ''){
            //$userProf = sed_build_user($row['userid'],htmlspecialchars($row['userName']));
            $userProf = sed_url('users', 'm=details&id='.$thread['userid'].'&u='.htmlspecialchars($thread['user_name']));
            if (!cot_url_check($userProf)) $userProf = COT_ABSOLUTE_URL.$userProf;
        }
        $agentProf = '';
        if ($thread['agentId'] > 0){
            $agentProf = cot_url('users', 'm=details&id='.$thread['agentId'].'&u='.$thread['agentName']);
            if (!cot_url_check($agentProf )) $agentProf  = COT_ABSOLUTE_URL.$agentProf;
        }

        $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

        $t->assign(array(
            'USER_NAME' => $thread['userName'],
            'USER_REAL_NAME' => $thread['user_name'],
            'USER_PROFILE_URL' => $userProf,
            'USER_HOST' => oc_buildUserAddr($thread['remote']),
            'USER_AGENT' =>  oc_get_useragent_version($thread['userAgent']),
            'GROUP_NAME' => htmlspecialchars($thread['groupName']),
            'AGENT_NAME' => htmlspecialchars($thread['agentName']),
            'AGENT_PROFILE_URL' => $agentProf,
            'TIME_IN_CHAT' => oc_date_diff_to_text($thread['modified']-$thread['created']).' ('.
                oc_date_to_text($thread['created']).')',
        ));
        if($threadMessages){
            foreach($threadMessages as $th_message ) {
                $t->assign(array(
                    'ROW_MESSAGE' => $th_message->toHtml(),
                ));
                $t->parse('MAIN.THREAD_LOG.ROW');
            }

        }

        $t->parse('MAIN.THREAD_LOG');

        cot_display_messages($t);
        $t->assign(array(
            'TITLE' => $L['oc']['thread_chat_log'],
            'HEADER_TITLE' => $L['oc']['thread_chat_log'],
            'SUBTITLE' => $L['oc']['thread_intro'],
        ));

        $t->parse('MAIN');
        return $t->text('MAIN');

    }

    /**
     * Статитика для операторов
     */
    public function statisticAction(){
        global $db, $db_oc_message, $sys, $L, $cfg, $out, $db_users;


        $t = new XTemplate(cot_tplfile('oconsultant'));

        $sys['sublocation'] = $L['Statistics'];
        $out['subtitle'] = $L['Statistics'];
        $out['canonical_uri'] = cot_url('oconsultant', array('m' => 'operator', 'a'=>'statistic'));

        $crumbs = array(
            array(cot_url('oconsultant'), $L['oc']['title']),
            $L['Statistics'],
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        if (isset($_GET['start'])) {
            $start = cot_import_date('start', false, false, 'G');
            $end = cot_import_date('end', false, false, 'G');
        } else {
            $curr = getdate($sys['now']);
            if ($curr['mday'] < 7) {
                // previous month
                if ($curr['mon'] == 1) {
                    $month = 12;
                    $year = $curr['year'] - 1;
                } else {
                    $month = $curr['mon'] - 1;
                    $year = $curr['year'];
                }
                $start = mktime(0, 0, 0, $month, 1, $year);
                $end = mktime(0, 0, 0, $month, date("t", $start), $year) + 24 * 60 * 60;
            } else {
                $start = mktime(0, 0, 0, $curr['mon'], 1, $curr['year']);
                //$end = time() + 24 * 60 * 60;
                $end = mktime(23, 59, 59, $curr['mon'], $curr['mday'], $curr['year']);
            }
        }

        if ($start > $end) {
            cot_error($L['oc']['statistics_wrong_dates']);
        }

        $oc_kind_agent = OcMessage::KIND_AGENT;
        $oc_kind_user = OcMessage::KIND_USER;

        if (!cot_error_found()){

            // Отчет по дням:
            $query = "SELECT DATE(dtmcreated) as date, COUNT(distinct threadid) as threads,
                SUM({$db_oc_message}.ikind = $oc_kind_agent) as agents, SUM({$db_oc_message}.ikind = $oc_kind_user) as users
              FROM {$db_oc_message}
              WHERE unix_timestamp(dtmcreated) >= $start AND unix_timestamp(dtmcreated) < $end
              GROUP BY DATE(dtmcreated) ORDER BY dtmcreated DESC";
            $res = $db->query($query);
            $totalfound = $res->rowCount();
            if ($totalfound > 0){
                while ($row = $res->fetch()) {
                    $t->assign(array(
                        'DATE_DAY' => $row['date'],
                        'DATE_TREADS' => $row['threads'],
                        'DATE_AGENTS' => $row['agents'],
                        'DATE_USERS' => $row['users'],
                    ));
                    $t->parse('MAIN.STATISTICS.RESULT.DAY.ROW');
                }

                $query = "SELECT COUNT(distinct threadid) as threads, SUM({$db_oc_message}.ikind = $oc_kind_agent) as agents,
                    SUM({$db_oc_message}.ikind = $oc_kind_user) as users
                  FROM {$db_oc_message}
                  WHERE unix_timestamp(dtmcreated) >= $start AND unix_timestamp(dtmcreated) < $end";
                $res = $db->query($query);
                $row = $res->fetch();
                $t->assign(array(
                    'DATET_TREADS' => $row['threads'],
                    'DATET_AGENTS' => $row['agents'],
                    'DATET_USERS' => $row['users'],
                ));
                $t->parse('MAIN.STATISTICS.RESULT.DAY.TOTAL');
                $t->parse('MAIN.STATISTICS.RESULT.DAY');

            }else{
                $t->parse('MAIN.STATISTICS.RESULT.DAY.NOTFOUND');
            }

            // Отчет по операторам
            $query = "SELECT user_id, user_name, COUNT(distinct threadid) as threads, SUM(ikind = $oc_kind_agent) as msgs,
                AVG(CHAR_LENGTH(tmessage)) as avglen
            FROM {$db_oc_message}, {$db_users}
            WHERE agentId = user_id AND unix_timestamp(dtmcreated) >= $start AND unix_timestamp(dtmcreated) < $end
            GROUP BY user_name";

            $res = $db->query($query);
            $totalfound = $res->rowCount();
            if ($totalfound > 0){
                while ($row = $res->fetch()) {
                    $t->assign(array(
                        'OPR_NAME' => htmlspecialchars($row['user_name']),
                        'OPR_PROF_URL' => cot_build_user($row['user_id'], htmlspecialchars($row['user_name'])),
                        'OPR_TREADS' => $row['threads'],
                        'OPR_MSGS' => $row['msgs'],
                        'OPR_AVGLEN' => $row['avglen'],
                    ));
                    $t->parse('MAIN.STATISTICS.RESULT.OPERATOR.ROW');
                }
            }else{
                $t->parse('MAIN.STATISTICS.RESULT.OPERATOR.NOTFOUND');
            }
            $t->parse('MAIN.STATISTICS.RESULT.OPERATOR');

            $t->parse('MAIN.STATISTICS.RESULT');
        }

        $t->assign(array(
            'PAGE_TITLE' => $L['Statistics'],
            'BREAD_CRUMBS'      => $breadcrumbs,
            'FORM_START_DATE' => cot_selectbox_date($start,'short', 'start'),
            'FORM_END_DATE' => cot_selectbox_date($end,'short','end'),
        ));
        $t->parse('MAIN.STATISTICS');

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }

    /**
     * @global CotDb $db
     * @return string
     */
    public function cannedAction(){
        global $sys, $L, $cfg, $out, $usr, $db_oc_responses, $db;


        $t = new XTemplate(cot_tplfile('oconsultant'));

        $sys['sublocation'] = $L['Statistics'];
        $out['subtitle'] = $L['Statistics'];
        $out['canonical_uri'] = cot_url('oconsultant', array('m' => 'operator', 'a'=>'statistic'));

        $crumbs = array(
            array(cot_url('oconsultant'), $L['oc']['title']),
            $L['oc']['canned_title'],
        );
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);


        // === locales ===
        $all_locales = oc_get_available_locales();
        $locales_with_label = array();
        foreach ($all_locales as $id) {
            $locales_with_label[$id] = oc_getLocalName($id);
        }

        $oc_lang = cot_import("oclang", "G", "ALP", 5);
        if (!$oc_lang || !in_array($oc_lang, $all_locales)) {
            $oc_lang = in_array($usr['lang'], $all_locales) ? $usr['lang'] : $all_locales[0];
        }
        $opts = array();
        foreach ($locales_with_label as $id => $loc){
            $opts[$id] = $loc;
        }
         $sel_locales = cot_selectbox($oc_lang, 'oclang', array_keys($opts), array_values($opts), false, array(
             'onchange' => 'this.form.submit();'));
        // === groups ===
        $groupid = "";
        // TODO Операторы по группам
        if ($cfg['oconsultant']['enablegroups'] == '1') {
            $groupid = sed_import("group", "G", "INT");
            if ($groupid) {
                $group = oc_group_by_id($groupid);
                if (!$group) {
                    $errors[] = getlocal("page.group.no_such");
                    $groupid = "";
                }
            }

            $link = connect();
            $allgroups = get_all_groups($link);
            mysql_close($link);
            $page['groups'] = array();
            $page['groups'][] = array('groupid' => '', 'vclocalname' => getlocal("page.gen_button.default_group"));
            foreach ($allgroups as $g) {
                $page['groups'][] = $g;
            }
//    <select name="group" onchange="this.form.submit();"><?php
//			foreach($page['groups'] as $k) {
//				echo "<option value=\"".$k["groupid"]."\"".($k["groupid"] == form_value("group") ? " selected=\"selected\"" : "").">".$k["vclocalname"]."</option>";
//			} </select>
        }

        $cannedUri = array('m'=>'operator', 'a'=>'canned', 'oclang'=>$oc_lang );
        if ($groupid > 0) $cannedUri['group'] = $groupid;

        // === delete ===
        if (isset($_GET['act']) && $_GET['act'] == 'delete') {
            $key = cot_import('key', 'G', 'INT');

            if (!$key || $key<=0) {
                cot_error("Wrong key");
            }

            if (!cot_error_found()) {
                $db->delete($db_oc_responses, "id={$key}");
//                sed_sql_query("DELETE FROM $db_oc_responses WHERE id = $key");
                cot_redirect(cot_url('oconsultant', $cannedUri, '', TRUE));
                exit;
            }
        }

        // === get messages ===
        $editUrl = array('m' => 'operator', 'a' => 'cannededit', 'oclang' => $oc_lang);
        if ($groupid > 0) $editUrl['group'] = $groupid;

        $messages = oc_load_canned_messages($oc_lang, $groupid);
        if (count($messages)>0){
            foreach($messages as $id => $msg){
                //$delUrl = array('m'=> 'operator','a'=>'canned', 'act'=>'delete', 'key'=>$id);
                $delUrl = $cannedUri;
                $delUrl['key'] = $id;
                $delUrl['act'] = 'delete';
                $t->assign(array(
                    'ROW_MSG_ID' => $id,
                    'ROW_MESSAGE' => htmlspecialchars($msg),
                    'ROW_MSG_DELETE_URL' => cot_confirm_url(cot_url('oconsultant', $delUrl), 'Online Consultant'),
                    'ROW_MSG_EDIT_URL' => cot_url('oconsultant', $editUrl + array("key"=>$id)),
                ));
                $t->parse('MAIN.CANNED.ROW');
            }
        }else{
            $t->parse('MAIN.CANNED.NOTFOUND');
        }


        $t->assign(array(
//            'SUBTITLE' => $L['oc']['canned_descr'],
            'SHOWGROUPS' => $cfg['oconsultant']['enablegroups'],
            'FORM_LOCALE' => $sel_locales,
            'EDIT_URL' => cot_url('oconsultant', $editUrl ),

            //'CREATE_CODE' => str_replace('o=', '', $editUri ),
        ));
        $t->parse('MAIN.CANNED');

        $t->assign(array(
            'PAGE_TITLE' => $L['oc']['canned_title'],
            'BREAD_CRUMBS'      => $breadcrumbs,
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }

    /**
     * Popup редактирование быстрых ответов
     * @global CotDb $db
     * @return string
     */
    public function cannededitAction(){
        global $db_oc_responses, $L, $cfg, $ext_display_header, $db;

        $ext_display_header = false;

        $res = array();

        $stringid = cot_import("key", "G", "INT");
        if ($stringid) {
            $result = $db->query("SELECT vcvalue FROM $db_oc_responses WHERE id = $stringid");
            $message = $result->fetch();
            if (!$message) {
                cot_error($L['oc']['cannededit_no_such']);
                $stringid = "";
            }else{
                $message = $message['vcvalue'];
            }
        } else {
            $message = "";
            $locale = cot_import("oclang", "G", "ALP", 5);
            $groupid = "";
            if ($cfg['oconsultant']['enablegroups'] == '1') {
                $page['groupid'] = verifyparam("group", "/^\d{0,8}$/");
            }
        }

        $title = $stringid ? $L['oc']['cannededit_title'] : $L['oc']['cannednew_title'];

        $t = new XTemplate(cot_tplfile('oconsultant.dialog'));
        $t->assign(array(
            'TITLE' => $title,
            'HEADER_TITLE' => $title,
            'SUBTITLE' => $stringid ? $L['oc']['cannededit_descr'] : $L['oc']['cannednew_descr'],
        ));
        $showForm = true;
        if (isset($_POST['message'])) {
            $message = cot_import('message', 'P', 'TXT');
            if (!$message) {
                cot_error(str_replace('{0}', $L['Message'], $L['oc']['errors_required']));
            }

            if (!cot_error_found()) {
                if ($stringid) {
                    $db->update($db_oc_responses, array('vcvalue'=>$message), "id={$stringid}");
                } else {
                    $data = array(
                        'locale' => $locale,
                        'groupid' => null,
                        'vcvalue' => $message,
                    );
                    $db->insert($db_oc_responses, $data, true);
                }
                $t->parse('MAIN.CANNED.SAVED');
                $showForm = false;
            }
        }

        $cannedUri = array('m'=>'operator', 'a'=>'cannededit', 'oclang'=>'ru' );
        if ($locale) $cannedUri['oclang'] = $locale;
        if ($stringid) $cannedUri['key'] = $stringid;
        if ($groupid && $groupid != '') $cannedUri['group'] =$groupid;

        if ($showForm){
            $t->assign(array(
                'FORM_KEY' => $stringid,
                'FORM_MESSAGE' => htmlspecialchars($message),
                'FORM_ACTION' => cot_url('oconsultant', $cannedUri),
                'LOCALE' => $locale,
            ));
            $t->parse('MAIN.CANNED.FORM');
            $t->parse('MAIN.SUBMIT');
        }
        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN.CANNED');
        $t->parse('MAIN');

        return $t->text('MAIN');
    }

    /**
     * Пригласить пользователя в чат
     * @param int $on_lineid
     * @return array
     */
    protected function invite_visitor($on_lineid){
        global $db_online, $db_oc_invite, $usr, $L, $sys, $db;

        $on_lineid = (int)$on_lineid;

        $result = array('msg' => '', 'error' => '');

        $query = "SELECT online_name, online_userid FROM $db_online WHERE online_id=$on_lineid";
        $res = $db->query($query);
        if ($res->rowCount() == 0){
            $result['error'] = $L['oc']['user_not_online'];
            return $result;
        }
        $row = $res->fetch();

        if ($row['online_userid'] > 0){
            if ($row['online_userid'] == $usr['id']){
                // Пригласить самого себя нельзя
                // Для отладки можно отключить
                return $result;
            }
            $where = "user_id=".$row['online_userid'];
        }else{
            $where = "online_id=".$on_lineid;
        }
        $message = cot_import('message', 'P', 'HTM');
        $message = trim($message);
        $query = "SELECT inv_id FROM $db_oc_invite WHERE $where";
        $res = $db->query($query);
        if ($res->rowCount() == 0){
            $query = "INSERT INTO $db_oc_invite (user_id,online_id,inv_dtsended,agentId,inv_text) VALUES
                ({$row['online_userid']}, $on_lineid, '".date('Y-m-d H:i:s', $sys['now'])."', {$usr['id']},
                    '".$db->prep($message)."' )";
            $db->query($query);
            $result['msg'] = $L['oc']['invite_sended'].': '.oc_date_to_text($sys['now']);
        }else{
            $row2 = $res->fetch();
            $query = "UPDATE $db_oc_invite SET inv_status=0, inv_dtsended='".date('Y-m-d H:i:s',
                $sys['now'])."', agentId={$usr['id']}, threadid=0, inv_text='".$db->prep($message)."'
                WHERE inv_id={$row2['inv_id']}";
            $db->query($query);
            $result['msg'] = $L['oc']['invite_sended'].': '.oc_date_to_text($sys['now']);
        }

        return $result;
    }

    // Служебные методы
//    function close_old_threads($link)
//    {
//        global $state_closed, $state_left, $state_chatting, $mysqlprefix, $settings;
//        if ($settings['thread_lifetime'] == 0) {
//            return;
//        }
//        $next_revision = next_revision($link);
//        $query = "update ${mysqlprefix}chatthread set lrevision = $next_revision, dtmmodified = CURRENT_TIMESTAMP, istate = $state_closed " .
//            "where istate <> $state_closed and istate <> $state_left and " .
//            "(ABS(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(lastpinguser)) > " . $settings['thread_lifetime'] . " and " .
//            "ABS(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(lastpingagent)) > " . $settings['thread_lifetime'] . ")";
//
//        perform_query($query, $link);
//    }
}