<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage Chat
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
class ChatController{


    /**
     * Ajax обновление чата
     */
    public function ajxRefreshAction(){
        global $sys, $oc_connection_timeout, $usr, $db, $db_users, $cfg;

        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");

        $isuser = cot_import("user", 'P', 'ALP', 5);
        $isuser = ($isuser == 'true') ? true : false;

        $lastid = cot_import('lastid', 'P', 'INT');
        if (!$lastid) $lastid = -1;

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $istyping = cot_import( "typed", "P", "INT", 1) == 1;
        $thread->ping($isuser,$istyping);
        if( !$isuser ) $thread->checkForReasign();

        $istyping = $isuser ? $thread->agentTyping : $thread->userTyping;

        $lastPing = $isuser ? $thread->lastpingagent : $thread->lastpinguser;
        $lastPing = strtotime($lastPing);

        $istyping = abs($sys['now'] - $lastPing) < $oc_connection_timeout && $istyping == "1" ? "1" : "0";

        $agentId = $isuser ? null : $usr['id'];

        $ret = array(
            'thread' => array(
                'lastid' => $lastid,
                'typing' => $istyping,
                'canpost'=> (($isuser || $agentId != null && $agentId == $thread->agentId) ? 1 : 0)
            ),
            'messages' => array(),
            'avatar' => '',
            'error' => ''
        );

        // Получить сообщения
        $msgCond = array(
            array('threadid',  $thread->threadid),
            array('messageid', $lastid, '>'),
        );
        if($isuser){
            $msgCond[] = array('ikind', OcMessage::KIND_FOR_AGENT, '!=');
        }

        // Учесть лимит загружаемых сообщений
        $msgLimit = 0;
        $msgOffset = 0;
        if($lastid <= 0 && $cfg['oconsultant']['chatLoadHistoryCnt'] != 'all'){
            $msgCnt = OcMessage::count($msgCond);
            $msgLimit = (int)$cfg['oconsultant']['chatLoadHistoryCnt'] + 1;
            $msgOffset = $msgCnt - $msgLimit;
            if($msgOffset < 0) $msgOffset = 0;
        }
        $messages = NULL;
        if($msgLimit > 0 || $lastid > 0) $messages = OcMessage::find($msgCond, $msgLimit, $msgOffset);

        if($messages){
            foreach($messages as $msg){
                //$message = array('created' => strtotime($msg['dtmcreated']));
                $message = array();
                switch ($msg->ikind) {
                    case OcMessage::KIND_AVATAR:
                        $message['type'] = 'avatar';
                        $message['text'] = $msg->tmessage;
                        break;

                    case OcMessage::KIND_REDIRECT:
                        $message['type'] = 'redirect';
                        $message['text'] = $msg->tmessage;
                        break;

                    default:
                        $message['type'] = 'message';
                        $message['text'] = $msg->toHtml();
                        break;
                }
                if ($msg->messageid > $lastid) {
                    $lastid = $msg->messageid;
                }
                $ret['messages'][] = $message;
            }

            $ret['thread']['lastid'] = $lastid;
        }

        // Получить аватар собеседника
        $usrId = ($isuser ? $thread->agentId : $thread->userid);
        if(mb_strpos($usrId, '.') !== FALSE) $usrId = 0;
        $usrId = (int)$usrId;
        if($usrId > 0){
            $avatar = $db->query("SELECT user_avatar FROM $db_users WHERE user_id=$usrId")->fetchColumn();
            if(!empty($avatar)) $ret['avatar'] = $avatar;
        }

        echo json_encode($ret);
        exit;
    }

    /**
     * Добавить сообщение
     */
    public function ajxPostAction(){
        global $sys, $usr, $L, $oc_connection_timeout, $cfg;

        $ret = array(
            'thread' => array(
                'lastid' => '',
                'typing' => '',
                'canpost'=> '',
                'avatar' => '',
            ),
            'messages' => array(),
            'error' => ''
        );

        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");

        $isuser = cot_import("user", 'P', 'ALP', 5);
        $isuser = ($isuser == 'true') ? true : false;

        $lastid = cot_import('lastid', 'P', 'INT');
        if (!$lastid) $lastid = -1;

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $istyping = cot_import( "typed", "P", "INT", 1) == 1;
        $thread->ping($isuser,$istyping);
        if( !$isuser ) $thread->checkForReasign();

        $lastPing = $isuser ? $thread->lastpingagent : $thread->lastpinguser;
        $lastPing = strtotime($lastPing);

        $istyping = abs($sys['now'] - $lastPing) < $oc_connection_timeout && $istyping == "1" ? "1" : "0";

        $agentId = $isuser ? null : $usr['id'];

        $message = cot_import('message', 'P', 'TXT'); //getrawparam('message');

        $kind = $isuser ? OcMessage::KIND_USER : OcMessage::KIND_AGENT;
        $from = $isuser ? $thread->userName : $thread->agentName;

        if(!$isuser && $usr['id'] != $thread->agentId) {
            $ret['error'] = "cannot send";
        }

        $postedid = $thread->postMessage($kind,$message,$from,null, $isuser ? null : $usr['id']);
        if($isuser && $thread->shownmessageid == 0) {
            $thread->shownmessageid = $postedid;
            $thread->save();
        }

        $ret['thread'] = array(
                'lastid' => $lastid,
                'typing' => $istyping,
                'canpost'=> (($isuser || $agentId != null && $agentId == $thread->agentId) ? 1 : 0)
            );

        // Получить сообщения
        $msgCond = array(
            array('threadid',  $thread->threadid),
            array('messageid', $lastid, '>'),
        );
        if($isuser){
            $msgCond[] = array('ikind', OcMessage::KIND_FOR_AGENT, '!=');
        }

        // Учесть лимит загружаемых сообщений
        $msgLimit = 0;
        $msgOffset = 0;
        if($lastid <= 0 && $cfg['oconsultant']['chatLoadHistoryCnt'] != 'all'){
            $msgCnt = OcMessage::count($msgCond);
            $msgLimit = (int)$cfg['oconsultant']['chatLoadHistoryCnt'] + 1;
            $msgOffset = $msgCnt - $msgLimit;
            if($msgOffset < 0) $msgOffset = 0;
        }
        $messages = NULL;
        if($msgLimit > 0 || $lastid > 0) $messages = OcMessage::find($msgCond, $msgLimit, $msgOffset);

        if($messages){
            foreach($messages as $msg){
                //$message = array('created' => strtotime($msg['dtmcreated']));
                $message = array();
                switch ($msg->ikind) {
                    case OcMessage::KIND_AVATAR:
                        $message['type'] = 'avatar';
                        $message['text'] = $msg->tmessage;
                        break;

                    case OcMessage::KIND_REDIRECT:
                        $message['type'] = 'redirect';
                        $message['text'] = $msg->tmessage;
                        break;

                    default:
                        $message['type'] = 'message';
                        $message['text'] = $msg->toHtml();
                        break;
                }
                if ($msg->messageid > $lastid) {
                    $lastid = $msg->messageid;
                }
                $ret['messages'][] = $message;
            }

            $ret['thread']['lastid'] = $lastid;
        }

        echo json_encode($ret);
        exit;
    }

    /**
     * Меняем имя пользователя
     */
    public function ajxRenameAction(){
        global $cfg, $L, $oc_namecookie, $sys;

        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");
        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $ret = array(
            'rename' => '',
            'error'  => ''
        );

        if( $cfg['oconsultant']['usercanchangename'] != "1" ) {
            $ret['error'] = "server: forbidden to change name";
        }

        $newname = cot_import('name', 'P', 'TXT');

        //oc_rename_user($thread, $newname);

        if ($thread->userName != $newname) {
            $thread->postMessage(OcMessage::KIND_EVENTS,
                str_replace(array('{0}', '{1}'), array($thread->userName, $newname), $L['oc']['chat_status_user_changedname']));
        }
        $thread->userName = $newname;
        $thread->save();

        $data = strtr(base64_encode($newname), '+/=', '-_,');
        cot_setcookie($oc_namecookie, $data, $sys['now']+60*60*24*365);
        $ret['rename'] = 'rename';

        echo json_encode($ret);
        exit;
    }

    /**
     * Перенаправление на новый урл
     * @todo тоже самое для кол-ва сообщений. Вообще вывод сообщений надо вынести в отдельный метод а то много копипаста
     */
    public function ajxRedirecttourlAction(){
        global $L, $usr, $oc_connection_timeout, $sys, $cfg;

        list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('oconsultant', 'any');
        cot_block($usr['auth_write'] || $usr['isadmin']);  // только для операторов

        $ret = array(
            'error' => ''
        );

        $isuser = false;

        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $istyping = cot_import( "typed", "P", "INT", 1) == 1;
        $thread->ping($isuser,$istyping);
        if( !$isuser ) $thread->checkForReasign();

        $url = cot_import('url', 'P', 'TXT');
//        $from = $thread['agentName'];
        $lastid = cot_import('lastid', 'P', 'INT');
        if (!$lastid) $lastid = -1;
        if($usr['id'] != $thread->agentId) {
            $ret['error'] = "cannot send";
            echo json_encode($ret);
            exit;
        }
        // Перенаправление
        $thread->postMessage(OcMessage::KIND_REDIRECT, $url);

        // Сообщение в чат об перенаправлении
        $thread->postMessage(OcMessage::KIND_EVENTS, str_replace(array('{0}', '{1}'), array($thread->userName, $url),
            $L['oc']['chat_user_redirected']));


        // Выводим последние сообщения
        $istyping = $isuser ? $thread->agentTyping : $thread->userTyping;

        $lastPing = $isuser ? $thread->lastpingagent : $thread->lastpinguser;
        $lastPing = strtotime($lastPing);

        $istyping = abs($sys['now'] - $lastPing) < $oc_connection_timeout && $istyping == "1" ? "1" : "0";

        $agentId = $isuser ? null : $usr['id'];

        $ret = array(
            'thread' => array(
                'lastid' => $lastid,
                'typing' => $istyping,
                'canpost'=> (($isuser || $agentId != null && $agentId == $thread->agentId) ? 1 : 0)
            ),
            'messages' => array(),
            'avatar' => '',
            'error' => ''
        );

        // Получить сообщения
        $msgCond = array(
            array('threadid',  $thread->threadid),
            array('messageid', $lastid, '>'),
        );
        if($isuser){
            $msgCond[] = array('ikind', OcMessage::KIND_FOR_AGENT, '!=');
        }
        // Учесть лимит загружаемых сообщений
        $msgLimit = 0;
        $msgOffset = 0;
        if($lastid <= 0 && $cfg['oconsultant']['chatLoadHistoryCnt'] != 'all'){
            $msgCnt = OcMessage::count($msgCond);
            $msgLimit = (int)$cfg['oconsultant']['chatLoadHistoryCnt'] + 1;
            $msgOffset = $msgCnt - $msgLimit;
            if($msgOffset < 0) $msgOffset = 0;
        }
        $messages = NULL;
        if($msgLimit > 0 || $lastid > 0) $messages = OcMessage::find($msgCond, $msgLimit, $msgOffset);

        if($messages){
            foreach($messages as $msg){
                //$message = array('created' => strtotime($msg['dtmcreated']));
                $message = array();
                switch ($msg->ikind) {
                    case OcMessage::KIND_AVATAR:
                        $message['type'] = 'avatar';
                        $message['text'] = $msg->tmessage;
                        break;

                    case OcMessage::KIND_REDIRECT:
                        $message['type'] = 'redirect';
                        $message['text'] = $msg->tmessage;
                        break;

                    default:
                        $message['type'] = 'message';
                        $message['text'] = $msg->toHtml();
                        break;
                }
                if ($msg->messageid > $lastid) {
                    $lastid = $msg->messageid;
                }
                $ret['messages'][] = $message;
            }

            $ret['thread']['lastid'] = $lastid;
        }
        echo json_encode($ret);
        exit;
    }

    /**
     * Пользователь переведен на новый урл
     */
    public function ajxRedirectToUrlDoneAction(){
        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");
        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }
        $msgCond = array(
            array('threadid',  $thread->threadid),
            array('ikind', OcMessage::KIND_REDIRECT)
        );
        $messages = OcMessage::find($msgCond);

        if($messages){
            foreach($messages as $msg){
                $msg->ikind = OcMessage::KIND_REDIRECT_DONE;
                $msg->save();
            }
        }
        echo json_encode(array('error'=>''));
        exit;
    }

    /**
     * Закрыть окно диалога
     * @todo проверка и очистка истории
     */
    public function closeAction(){
        global $sys, $usr, $L, $oc_connection_timeout, $cfg;

        $token = cot_import("token", 'P', 'INT');
        $threadid = cot_import("thread", 'P', 'INT');

        if(!$threadid)  die("wrong thread");

        $isuser = cot_import("user", 'P', 'ALP', 5);
        $isuser = ($isuser == 'true') ? true : false;

        $lastid = cot_import('lastid', 'P', 'INT');
        if (!$lastid) $lastid = -1;

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $istyping = 0;
        $thread->ping($isuser,$istyping);

        $ret = array(
            'closed' => '',
            'error'  => ''
        );

        if( $isuser || $thread->agentId == $usr['id']) {
            if($cfg['oconsultant']['storeHistory'] == 0){
                $thread->cleanHistory();
            }
            $ret['closed'] = 'closed';
        }else{
            $ret['error'] = 'cannot close';
        }

        echo json_encode($ret);
        exit;
    }

    // === Служебные методы ===

    /**
     * Форма предварительного опроса
     *
     * @param string $name
     * @param string $email
     * @param int $groupid
     * @param type $info
     * @param string $referrer
     * @return array
     */
    protected function generateSurveyTags($name, $email, $groupid, $info, $referrer){
        global $cfg;

        $ret = array(
            'INFO' => $info,
            'FORMEMAIL' => $email,
            'FORMNAME' => $name,
            'FORMGROUPID' => $groupid,
            'REFERRER' => urlencode($referrer),
            'SHOWEMAIL' => $cfg['oconsultant']['surveyaskmail'],
            'SHOWMESSAGE' => $cfg['oconsultant']['surveyaskmessage'],
            'SHOWNAME' => $cfg['oconsultant']['usercanchangename'],
        );

        return $ret;

        // TODO группы консультантов
        if ($cfg['oconsultant']['enablegroups'] == '1' && $cfg['oconsultant']["surveyaskgroup"] == "1") {
//		$link = connect();
            $allgroups = get_groups($link, false);
            mysql_close($link);
            $val = "";
            foreach ($allgroups as $k) {
                $groupname = $k['vclocalname'];
                if ($k['inumofagents'] == 0) {
                    continue;
                }
                if ($k['ilastseen'] !== NULL && $k['ilastseen'] < $settings['online_timeout']) {
                    if (!$groupid) {
                        $groupid = $k['groupid']; // select first online group
                    }
                } else {
                    $groupname .= " (offline)";
                }
                $isselected = $k['groupid'] == $groupid;
                $val .= "<option value=\"" . $k['groupid'] . "\"" . ($isselected ? " selected=\"selected\"" : "") . ">$groupname</option>";
            }
            $page['groups'] = $val;
        }
    }
}