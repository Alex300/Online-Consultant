<?PHP
defined('COT_CODE') or die('Wrong URL.');

/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage Client
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 */
class ClientController{

    /**
     * Клиент открывает чат
     * @global CotDb $db
     * @return string
     * @todo правиьные заголовки окна
     */
    public function openAction() {
        global $sys, $ext_display_header, $oc_namecookie, $db, $cot_modules, $usr, $cfg, $L, $db_online, $db_oc_invite;

        $ext_display_header = false;
        $sys['noindex'] = true;

        if( !isset($_GET['token']) || !isset($_GET['thread']) ) {

            $thread = NULL;
            $inv = cot_import('inv', 'G', 'BOL');  // было ли приглашение в чат
            // Для зарегов пробуем загрузить диалог, привязянных к их ID
            // Не плодим диалоги для одного пользователя
            $visitor = getVisitorFromRequest();
            if(oc_has_online_operators()){
                if($usr['id'] > 0){
                    $thread = OcThread::getByUserId($usr['id']);
                }elseif( isset($_SESSION['threadid']) ) {
                    $thread = OcThread::getById($_SESSION['threadid']);
                }else{
                    $thread = OcThread::getByUserId($visitor['id']);
                }
                if($thread){
                    $thread->reOpen();
                }
            }
            if( !$thread ) {
                $groupid = "";
                $groupname = "";
                // TODO консультанты по группам
                // $cfg['plugin']['an_o_consultant']['enablegroups']
                //		if($settings['enablegroups'] == '1') {
                //			$groupid = verifyparam( "group", "/^\d{1,8}$/", "");
                //			if($groupid) {
                //				$group = group_by_id($groupid);
                //				if(!$group) {
                //					$groupid = "";
                //				} else {
                //					$groupname = get_group_name($group);
                //				}
                //			}
                //		}

                if(isset($_POST['survey']) && $_POST['survey'] == 'on') {
                    $firstmessage = cot_import('message', 'P', 'TXT');
                    $info = cot_import('info', 'P', 'TXT');
                    $email = cot_import('email', 'P', 'TXT');
                    $referrer = urldecode(cot_import('referrer', 'P', 'TXT'));

                    if($cfg['oconsultant']['usercanchangename'] == "1" && isset($_POST['name'])) {
                        $newname = cot_import('name', 'P', 'TXT');
                        if($newname != $visitor['name']) {
                            $data = strtr(base64_encode($newname), '+/=', '-_,');
                            cot_setcookie($oc_namecookie, $data, $sys['now']+60*60*24*365);
                            $visitor['name'] = $newname;
                        }
                    }
                } else {
                    $firstmessage = NULL;
                    $info = cot_import('info', 'G', 'TXT');
                    $email = cot_import('email', 'G', 'TXT');
                    $referrer = isset($_GET['url']) ? $_GET['url'] :
                        (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
                    if(isset($_GET['referrer']) && $_GET['referrer']) {
                        $referrer .= "\n".$_GET['referrer'];
                    }
                }

                if(!oc_has_online_operators($groupid)) {
                    $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

                    if ($usr['id'] < 1 && $cfg['oconsultant']['useCaptcha'] == 1){
                        $t->assign(array(
                            "VERIFYIMG" => cot_captcha_generate(),
                            "VERIFYINPUT" => "<input name=\"rverify\" type=\"text\" id=\"rverify\" size=\"10\" maxlength=\"6\" class=\"text\" />",
                        ));
                        $t->parse("MAIN.LEAVE_MESSAGE.CAPTCHA");
                    }
                    $email = (empty($email)) ? $usr["profile"]["user_email"] : $email;
                    $t->assign(array(
                        'TITLE' => $L['oc']['leavemessage_title'],
                        'HEADER_TITLE' => $L['oc']['leavemessage_title'],
                        //'FORM_ACTION' => cot_url('oconsultant', array()),
                        'INFO' => $info,
                        'USER_NAME' => $visitor['name'],
                        'REFERRER' => $referrer,
                        'MESSAGE' => '',
                        'USER_MAIL' => $email
                    ));
                    $t->parse('MAIN.LEAVE_MESSAGE');
                    $t->parse('MAIN.SUBMIT');
                    $t->parse('MAIN');

                    return $t->text('MAIN');
                }

                // Предварительный опрос
                // если пользователь открывает диалог по приглашению оператора, Опрос не показываем
                if($cfg['oconsultant']['enablepresurvey'] == '1' && !$inv &&
                    !(isset($_POST['survey']) && $_POST['survey'] == 'on')) {

                    $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

                    $email = (empty($email)) ? $usr["profile"]["user_email"] : $email;
                    $t->assign($this->generateSurveyTags($visitor['name'], $email, $groupid, $info, $referrer));
                    $t->parse('MAIN.SURVEY');

                    $t->assign(array(
                        'TITLE' => $L['oc']['presurvey_title'],
                        'HEADER_TITLE' => $L['oc']['presurvey_title'],
                    ));
                    $t->parse('MAIN.SUBMIT');
                    $t->parse('MAIN');
                    return $t->text('MAIN');
                }

                $remoteHost = oc_get_remote_host();
                $userbrowser = $_SERVER['HTTP_USER_AGENT'];

//            $link = connect();
//            if(!check_connections_from_remote($remoteHost, $link)) {
//                mysql_close($link);
//                die("number of connections from your IP is exceeded, try again later");
//            }

                $thread = new OcThread();
                $thread->groupid = $groupid;
                $thread->userid  = $visitor['id'];
                $thread->userName= $visitor['name'];
                $thread->remote  = $remoteHost;
                $thread->referer = $referrer;
                $thread->locale  = $usr['lang'];
                $thread->userAgent = $userbrowser;
                $thread->istate  = OcThread::STATE_LOADING;
                $thread->save();

                $_SESSION['threadid'] = $thread->threadid;
                if( $referrer ) {
                    $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $referrer, $L['oc']['chat_came_from']));
                }
                $thread->postMessage(OcMessage::KIND_INFO, $L['oc']['chat_wait']);
                if($email) {
                    $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $email, $L['oc']['chat_visitor_email']));
                }
                if($info) {
                    $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $info, $L['oc']['chat_visitor_info']));
                }
                if($firstmessage) {
                    $postedid = $thread->postMessage(OcMessage::KIND_USER,$firstmessage,$visitor['name']);
                    $thread->shownmessageid = $postedid;
                    $thread->save();
                }

            }
            $threadid = $thread->threadid;
            // Если диалог открыт по приглашению, запишем в таблицу приглашений id диалога
            if ($inv == 1){
                if($usr['id'] > 0){
                    $where = "user_id=".$usr['id'];
                }else{
                    $where="online_id=(SELECT online_id FROM $db_online WHERE online_ip='{$usr['ip']}' AND online_name='v')";
                }
                $db->update($db_oc_invite, array('threadid'=>$thread->threadid), $where);
            }
            $token = $thread->ltoken;

            cot_redirect(cot_url('oconsultant',"m=client&a=open&thread=$threadid&token=$token", '', TRUE));

        }

        $t = new XTemplate(cot_tplfile('oconsultant.chat'));

        $token = cot_import('token', 'G', 'INT', 0, true); //verifyparam( "token", "/^\d{1,8}$/");
        if (!$token) $token = cot_import('token', 'P', 'INT', 0, true);
        $threadid = cot_import('thread', 'G', 'INT', 0, true); //verifyparam( "thread", "/^\d{1,8}$/");
        if (!$threadid) $threadid = cot_import('thread', 'P', 'INT', 0, true);

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $t->assign(OcThread::generateTagsForUser($thread));

        $pparam = cot_import('act', 'G', 'ALP');
        if (!$pparam) $pparam = cot_import('act', 'P', 'ALP', 0, true);
        if ($pparam != 'mailthread') $pparam = 'default';
        // Отправить историю диалога на e-mail
        if( $pparam == "mailthread" ) {
            $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

            $t->assign(array(
                'TITLE' => $L['oc']['mailthread_title'],
                'HEADER_TITLE' => $L['oc']['chat_window_title'],
                'FORM_ACTION' => cot_url('plug', 'o=an_o_consultant'),
            ));
            $t->parse('MAIN.MAIL');
            $t->parse('MAIN.SUBMIT');
            $t->parse('MAIN');
            //$res['title'] = $L['oc']['chat_window_title'];

            return $t->text('MAIN');

        } else {
            $t->parse('MAIN.CHAT');
        }

        $t->parse('MAIN.SUBMIT');

        $t->assign(array(
            'TITLE' => $L['oc']['chat_window_title'],
            'HEADER_TITLE' => $L['oc']['chat_window_title'],
            'OC_VERSION' => $cot_modules['oconsultant']['version'],
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');

        return $t->text('MAIN');
    }


    /**
     * Оставить оффлайн сообщение консультантам
     * @return string - html код
     */
    public function leavemsgAction(){
        global $L, $usr, $cfg, $db_users, $db_groups_users, $db, $ext_display_header;

        $ext_display_header = false;

        $groupid = "";
        $groupname = "";
        $group = NULL;
        // TODO клнсультанты по группам
//    if($settings['enablegroups'] == '1') {
//        $groupid = verifyparam( "group", "/^\d{1,8}$/", "");
//        if($groupid) {
//            $group = group_by_id($groupid);
//            if(!$group) {
//                $groupid = "";
//            } else {
//                $groupname = get_group_name($group);
//            }
//        }
//    }

        $email = mb_strtolower(cot_import('email', 'P', 'TXT'));
        $visitor_name = cot_import('name', 'P', 'TXT');
        $message = cot_import('message', 'P', 'TXT');
        $info = cot_import('info', 'P', 'TXT');
        $referrer = urldecode(cot_import("referrer", 'P', 'TXT'));
        $rverify  = cot_import('rverify','P','TXT');

        $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

        if( !$email ||  $email=='') {
            cot_error(str_replace('{0}',$L['oc']['form_field_email'], $L['oc']['errors_required']));
        } else {
            if (!cot_check_email($email)){
                cot_error(str_replace('{0}',$L['oc']['form_field_email'], $L['oc']['errors_wrong_field']));
            }
        }
        if( !$visitor_name ) {
            cot_error(str_replace('{0}',$L['oc']['form_field_name'], $L['oc']['errors_required']));
        }

        if( !$message ) {
            cot_error(str_replace('{0}',$L['Message'], $L['oc']['errors_required']));
        }
        if ($usr['id'] < 1 && $cfg['oconsultant']['useCaptcha'] == 1){
            if(!cot_captcha_validate($rverify)){
                cot_error($L['oc']['errors_captcha']);
            }
        }
        if (cot_error_found()){

            if ($usr['id'] < 1 && $cfg['oconsultant']['useCaptcha'] == 1){
                $t->assign(array(
                    "VERIFYIMG" => cot_captcha_generate(),
                    'VERIFYINPUT' => cot_inputbox('text', 'rverify', '', array('id' => 'rverify', 'size'=> 10,
                                                                               'class' => 'text'))
                ));
                $t->parse("MAIN.LEAVE_MESSAGE.CAPTCHA");
            }

            $t->assign(array(
                'TITLE' => $L['oc']['leavemessage_title'],
                'INFO' => $info,
                'USER_NAME' => $visitor_name,
                'REFERRER' => $referrer,
                'MESSAGE' => $message,
                'USER_MAIL' => $email
            ));
            $t->parse('MAIN.LEAVE_MESSAGE');
            $t->parse('MAIN.SUBMIT');

            // Error and message handling
            cot_display_messages($t);

            $t->parse('MAIN');

            return $t->text('MAIN');
        }

        // Сохранить сообщение
        $this->storeOffLineMessage($visitor_name, $email, $info, $message, $groupid, $referrer);

        // Рассылка уведомлений:
        $subject = str_replace('{0}', $visitor_name, $L['oc']['leavemail_subject']);
        $body = str_replace(array('{0}','{1}','{2}','{3}',), array($visitor_name,$email,$message,$info ? "$info\n" : ""),
            $L['oc']['leavemail_body']);

//    if (isset($group) && !empty($group['vcemail'])) {
//        $inbox_mail = $group['vcemail'];
//    } else {
//        $inbox_mail = $settings['email'];
//    }

        // TODO Если передан id группы, то рассылка уведомлений консультантам только внутри этой группы
        // Админам в зависимости от настроек
        $inbox_mail = array();
        if ( $cfg['oconsultant']['offLineAdminNotify'] != ''){
            $tmp = $cfg['oconsultant']['offLineNotifyEmail'];
            if(empty($tmp)) $tmp = $cfg['adminemail'];
            // todo если нет в настройках мыла, брать системное
            $inbox_mail[] = array('user_email' => $tmp);
        }

        $where = array();
        $uGroups = oc_getOperatorsGroups();
        if ( $cfg['oconsultant']['offLineConsNotify'] == 1 && $uGroups){
            $where[] = "u.user_maingrp IN (".implode(', ', $uGroups).")";
            $where[] = "m.gru_groupid IN (".implode(', ', $uGroups).")";
        }
        if ( $cfg['plugin']['oconsultant']['admCons'] == 'notify'){
            $where[] = "u.user_maingrp=".COT_GROUP_SUPERADMINS;
            $where[] = "m.gru_groupid=".COT_GROUP_SUPERADMINS;
        }

        if (count($where)>0){
            $query = "SELECT user_id, user_name, user_email
                FROM  $db_users AS u
                LEFT JOIN $db_groups_users as m ON m.gru_userid=u.user_id
                WHERE ".(implode(' OR ', $where));
            $result = $db->query($query);
            while($row = $result->fetch()){
                $inbox_mail[] = $row;
            }
        }

        if(count($inbox_mail) > 0) {
            //$inbox_mail = array_unique($inbox_mail);
            $sended = array();
            foreach($inbox_mail as $mail){
                if (isset ($mail['user_email']) && !in_array($mail['user_email'], $sended)){
                    cot_mail($mail['user_email'], $subject, $body);
                    $sended[] = $mail['user_email'];
                }
            }
        }

        $t->assign(array(
            'TITLE' => $L['oc']['leavemessage_sent_title'],
        ));
        $t->parse('MAIN.LEAVE_MESSAGESEND');
        $t->parse('MAIN');


        // Error and message handling
        cot_display_messages($t);
        return $t->text('MAIN');
    }

    /**
     * Отправить историю диалога на e-mail пользователя
     */
    public function mailthreadAction(){
        global $ext_display_header, $L, $usr, $cot_modules;

        $ext_display_header = false;

        $token = cot_import('token', 'P', 'INT');
        if (!$token) $token = cot_import('token', 'G', 'INT');
        $threadid = cot_import('thread', 'P', 'INT');
        if (!$threadid) $threadid = cot_import('thread', 'G', 'INT');
        if (!$threadid) die("wrong thread");

        $thread = OcThread::getById($threadid);
        if( !$thread || !isset($thread->ltoken) || $token != $thread->ltoken ) {
            die("wrong thread");
        }

        $email = cot_import('email', 'P', 'TXT');
        if (isset($_POST['email'])){
            if( !$email || $email == '') {
                cot_error(str_replace('{0}',$L['oc']['form_field_email'], $L['oc']['errors_required']));
            } elseif( !cot_check_email($email)) {
                cot_error(str_replace('{0}',$L['oc']['form_field_email'], $L['oc']['errors_wrong_field']));
            }
        }
        $t = new XTemplate(cot_tplfile('oconsultant.dialog'));

        // выводим ошибки
        if(!isset($_POST['email']) || cot_error_found()){
            if (!$email && isset($usr["profile"]['user_email']) && !isset($_POST['email'])){
                $email = $usr["profile"]['user_email'];
            }
            $t->assign(array(
                'TITLE' => $L['oc']['mailthread_title'],
                'HEADER_TITLE' => $L['oc']['chat_window_title'],
                'FORM_ACTION' => cot_url('oconsultant', array('m'=>'client', 'a'=>'mailthread')),
                'FORM_EMAIL' => $email,
                'CHATTHREADID' => $thread->threadid,
                'TOKEN' => $thread->ltoken,
                'OC_VERSION' => $cot_modules['oconsultant']['version'],
            ));
            $t->parse('MAIN.MAIL');
            $t->parse('MAIN.SUBMIT');

            // Error and message handling
            cot_display_messages($t);

            $t->parse('MAIN');
            return $t->text('MAIN');
        }

        $history = "";

        $msgCond = array(
            array('threadid',  $thread->threadid),
            array('ikind', OcMessage::KIND_FOR_AGENT, '!=')
        );
        $messages = OcMessage::find($msgCond);
        foreach($messages as $msg){
            if (!in_array($msg->ikind, array(OcMessage::KIND_AVATAR, OcMessage::KIND_REDIRECT,
                OcMessage::KIND_REDIRECT_DONE))){
                $history .= $msg->toText();
            }
        }


        $subject = $L['oc']['mail_user_history_subject'];
        $body = str_replace(array('{0}', '{1}'), array($thread->userName,$history),
            $L['oc']['mail_user_history_body']);

        cot_mail($email, $subject, $body);
        cot_message(str_replace('{0}', $email, $L['oc']['chat_mailthread_sent_content']));
        $t->assign(array(
            'TITLE' => $L['oc']['chat_mailthread_sent_title'],
            'USER_EMAIL' => $email,
//            'SENT_MESSAGE' => str_replace('{0}', $email, $L['oc']['chat_mailthread_sent_content']),
            'OC_VERSION' => $cot_modules['oconsultant']['version'],
        ));
        $t->parse('MAIN.MAIL_SENT');

        // Error and message handling
        cot_display_messages($t);

        $t->parse('MAIN');

        return $t->text('MAIN');
    }


    /**
     * Ajax. Статус приглашения в чат
     * todo если включен кеш и незарег, то обновить инфу кто он лайн
     */
    public function updateAction(){
        $res = array();
        $act = cot_import('act', 'P', 'ALP');
        if ($act=='ansver'){
            $answer = cot_import('answer', 'P', 'INT');
            $this->setCurrenUserInviteAnswer($answer);
            exit();
        }
        // Проверить, есть ли приглашения в чат
        $res = $this->chekInviteForCurrentUser();
        cot_sendheaders();
        echo json_encode($res);
        exit();
    }

    /**
     * Ajax. проверить статус операторов
     */
    public function operator_statusAction(){
        global $oC_hasOnlineOperators,  $oC_Button, $cfg, $usr, $sys, $db, $db_online, $R, $theme_reload;

        require_once cot_incfile('oconsultant','module','resources');
        include $cfg['themes_dir'].'/'.$cfg['defaulttheme'].'/'.$cfg['defaulttheme'].'.php';

        $locinfo = unserialize($_POST['locinfo']);
        if(is_array($locinfo) && $usr['id'] == 0){
            $data = array(
                'online_location' => cot_import($locinfo['location'], 'D', 'TXT'),
                'online_subloc' => cot_import($locinfo['subloc'], 'D', 'TXT'),
                'online_lastseen' => (int)$sys['now'],
                'online_location_code' => cot_import($locinfo['location_code'], 'D', 'ALP'),
                'online_title' =>  cot_import($locinfo['title'], 'D', 'TXT'),
                'online_uri' => $locinfo['uri'],
                'online_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'online_breadcrumb' => $locinfo['breadcrumb'],
            );
            if(!$cfg['plugin']['whosonline']['disable_guests']){
                if (empty($sys['online_location'])){
                    $data['online_ip'] = $usr['ip'];
                    $data['online_name'] = 'v';
                    $data['online_userid'] = -1;
                    $data['online_shield'] = 0;
                    $data['online_hammer'] = 0;
                    $db->insert($db_online, $data);
                }else{
                    $db->update($db_online, $data, "online_ip='".$usr['ip']."'");
                }
            }

        }

        if(empty($oC_hasOnlineOperators)){
            $oC_hasOnlineOperators = oc_has_online_operators() ? 1 : 0;
        }
        if(empty($oC_Button)){

            if($oC_hasOnlineOperators){
                $oC_Button = cot_rc('oc_ocButtonOnLine');
            }else{
                $oC_Button = cot_rc('oc_ocButtonOffLine');
            }
        }

        $res = array(
            'status' => $oC_hasOnlineOperators,
            'button' => $oC_Button
        );

        cot_sendheaders();
        echo json_encode($res);
        exit();
    }

    /**
     * Сохранить ответ пользователя
     * @param int $answer
     * @return bool
     */
    protected function  setCurrenUserInviteAnswer($answer){
        global $usr, $db_oc_invite, $db_online, $sys, $db;
        $answer = (int)$answer;
        if ($answer < 0 || $answer > 2) return false;

        if($usr['id'] > 0){
            $where[] = "user_id=".$usr['id'];
        }else{
            $where[] = "online_id=(SELECT online_id FROM $db_online WHERE online_ip='{$usr['ip']}' AND online_name='v')";
        }

        $query = "UPDATE {$db_oc_invite} SET inv_status=$answer, inv_answed='".date('Y-m-d H:i:s', $sys['now'])."'
                  WHERE ".implode(' AND ', $where);
        $db->query($query);
    }

    /**
     * Проверить, есть ли приглашение в чат для данного пользователя
     * @return int - id приглашения или 0
     */
    protected function chekInviteForCurrentUser(){
        global $usr, $db_online, $db_oc_invite, $db, $L;

        $where = array();
        $where[] = "inv_status=0";
        if($usr['id'] > 0){
            $where[] = "user_id=".$usr['id'];
        }else{
            $where[] = "online_id=(SELECT online_id FROM $db_online WHERE online_ip='{$usr['ip']}' AND online_name='v')";
        }
        $query = "SELECT inv_id, inv_text  FROM {$db_oc_invite} WHERE ".implode(' AND ', $where);
        //$res = sed_sql_query($query);
        $row = $db->query($query)->fetch(PDO::FETCH_ASSOC);
        if ($row){
            if ($row['inv_text'] == '') $row['inv_text'] = $L['oc']['invite_message'];
            $res['iid'] = $row['inv_id'];
            $res['itext'] = $row['inv_text'];
            return  $res;
        }

        return 0;
    }

    /**
     * Сохранить офф-лайн сообщение
     * @param string $name - имя пользователя
     * @param string $email - e-mail пользователя
     * @param string $info
     * @param string $message - текст сообщения
     * @param string $groupid - id группы
     * @param string $referrer
     */
    protected  function storeOffLineMessage($name, $email, $info, $message,$groupid,$referrer) {
        global $usr, $L, $cfg;

        $remoteHost = oc_get_remote_host();
        $userbrowser = $_SERVER['HTTP_USER_AGENT'];
        $visitor = getVisitorFromRequest();

        // Если диалог с этим пользователем существует, то сообщение сохраним в него
        $thread = OcThread::getByUserId($visitor['id']);
        if(!$thread){
            $thread = new OcThread();
            $thread->userid = $visitor['id'];
            $thread->referer = $referrer;
            $thread->locale = $usr['lang'];
            $thread->userName = $name;
        }
        if($cfg['oconsultant']['usercanchangename']) $thread->userName = $name;
        $thread->remote = $remoteHost;
        $thread->userAgent = $userbrowser;
        $thread->istate = OcThread::STATE_LEFT;

        if( $referrer ) {
            $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $referrer, $L['oc']['chat_came_from']));
        }
        if($email) {
            $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $email, $L['oc']['chat_visitor_email']));
        }
        if($info) {
            $thread->postMessage(OcMessage::KIND_FOR_AGENT, str_replace('{0}', $info, $L['oc']['chat_visitor_info']));
        }
        $thread->postMessage(OcMessage::KIND_USER,"[offline] $message", $name);

    }
}