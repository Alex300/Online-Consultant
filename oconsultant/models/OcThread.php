<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for the threads
 *
 * @package Online Consultant
 * @subpackage DB
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 *
 * @property int $threadid
 * @property int $userid
 * @property string $userName
 * @property int $istate       Статус
 * @property int $nextagent    Следующий назначенный оператор
 * @property int $groupid      Группа операторов
 * @property int $lrevision
 * @property int $ltoken
 * @property string $dtmmodified
 * @property string $remote
 * @property string $referer
 * @property string $locale
 * @property string $userAgent
 * @property int $shownmessageid    // Последнее сообщение, которое нужно отобразить
 * @property bool $agentTyping
 * @property bool $userTyping
 *
 * @method static OcThread getById(int $pk)
 * @method static OcThread[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static OcThread[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 *
 */
class OcThread extends OcModelAbstract{

    // === Статусы диалогов ===
    const STATE_QUEUE = 0;
    /**
     * Ожидает ответа оператора
     */
    const STATE_WAITING = 1;
    const STATE_CHATTING = 2;
    const STATE_CLOSED = 3;
    /**
     * Новый диалог (присваивается при создании)
     */
    const STATE_LOADING = 4;
    /**
     * Оставлено офф-лайн сообщение
     */
    const STATE_LEFT = 5;
    // === /Статусы диалогов ===

    /**
     * @var string
     */
    public static $_table_name = '';

    /**
     * @var string
     */
    public static $_primary_key = '';

    /**
     * Column definitions
     * @var array
     */
    public static $_columns = array();

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_oc_thread;

        self::$_table_name = $db_oc_thread;
        self::$_primary_key = 'threadid';
        parent::__init();

    }

    /**
     * @param mixed $data Array or Object - свойства
     *   в свойства заполнять только те поля, что есть в таблице + user_name
     */
//    public function __construct($data = false) {
//        parent::__construct($data);
//
//    }

    /**
     * Get Thread by UserId
     *
     * @static
     * @param int $id
     * @return OcThread|null
     * @todo static cache
     */
    public static function getByUserId($id){

        if(!$id) return null;

        $cond = array(
            "userid" => $id,
        );
        $res = self::fetch($cond, 1);

        return ($res) ? $res[0] : null;
    }

    /**
     * Заново открыть диалог
     */
    public function reOpen(){
        global $L;

        // Наверное позволим открывть любой диалог, даже с оффлайн сообщением
        //if ($thread['istate'] == $oc_state_closed || $thread['istate'] == $oc_state_left) return FALSE;

        $tmp = array(OcThread::STATE_CHATTING, OcThread::STATE_QUEUE, OcThread::STATE_LOADING);
        if (!in_array($this->_data['istate'], $tmp)) {
            $this->_data['istate'] = OcThread::STATE_WAITING;
            $this->_data['nextagent'] = 0;
            $this->save();
        }
        $this->postMessage(OcMessage::KIND_EVENTS, $L['oc']['chat_status_user_reopenedthread']);

        return true;    // Или return $this ???
    }

    /**
     * Save data
     * @param OcThread|array|null $data
     * @return int id of saved record
     */
    public function save($data = null){
        global $sys, $usr;

        if(!$data) $data = $this->_data;

        if ($data instanceof OcThread) {
            $data = $data->toArray();
        }

        $this->_data['dtmmodified'] = $data['dtmmodified'] = date('Y-m-d H:i:s', $sys['now']);
        $this->_data['lrevision'] = $data['lrevision'] = $this->next_revision();

        if(!$data['threadid']) {
            // Добавить новый
            $this->_data['ltoken'] = $data['ltoken'] = $this->nextToken();
            $this->_data['dtmcreated'] = $data['dtmcreated'] = date('Y-m-d H:i:s', $sys['now']);
        }
        $id = parent::save($data);

        return $id;
    }

    /**
     * Добавить сообщение
     * @param $kind
     * @param $message
     * @param null $from
     * @param null $utime
     * @param null $opid
     * @return int
     */
    public function postMessage($kind, $message, $from = null, $utime = null, $opid = null){
        global $sys;

        $time = ($utime) ? $utime : $sys['now'];
        $data = array(
            'threadid' => $this->_data['threadid'],
            'ikind' => $kind,
            'tmessage' => $message,
            'tname' => ($from) ? $from : null,
            'agentId' => ($opid) ? $opid : 0,
            'dtmcreated' => date('Y-m-d H:i:s', $time),
        );
        $message = new OcMessage($data);
        return $message->save();
    }

    protected function next_revision(){
        global $db_oc_revision, $db;

        $db->query("UPDATE {$db_oc_revision} SET id=LAST_INSERT_ID(id+1)");
        $val = $db->lastInsertId();
        return $val;
    }

    /**
     *
     * @return int
     */
    function nextToken(){
        return rand(99999, 99999999);
    }

    public function ping($isuser, $istyping){
        global $oc_connection_timeout, $L, $sys;

        if($isuser){
            $this->_data['lastpinguser'] = date('Y-m-d H:i:s', $sys['now']);
            $this->_data['userTyping'] = ($istyping ? 1 : 0);
            $lastPing = $this->lastpingagent;
        }else{
            $this->_data['lastpingagent'] = date('Y-m-d H:i:s', $sys['now']);
            $this->_data['agentTyping'] = ($istyping ? 1 : 0);
            $lastPing = $this->lastpinguser;
        }
        $lastPing = strtotime($lastPing);

        $current = $sys['now'];

        if ($this->istate == OcThread::STATE_LOADING && $isuser) {
            $this->_data['istate'] = OcThread::STATE_QUEUE;
            $this->save();
            return;
        }

        if ($lastPing > 0 && abs($current - $lastPing) > $oc_connection_timeout) {
            $this->_data[$isuser ? "lastpingagent" : "lastpinguser"] = "0";
            if (!$isuser) {
                $message_to_post = $L['oc']['chat_status_user_dead'];
                $this->postMessage(OcMessage::KIND_FOR_AGENT, $message_to_post, null, $lastPing + $oc_connection_timeout);
            } else if ($this->_data['istate'] == OcThread::STATE_CHATTING) {

                $message_to_post = $L['oc']['chat_status_operator_dead'];
                $this->postMessage(OcMessage::KIND_CONN, $message_to_post, null, $lastPing + $oc_connection_timeout);
                $this->_data['istate'] = OcThread::STATE_WAITING;
                $this->_data['nextagent'] = 0;
            }
        }
        $this->save();
    }

    /**
     * Закрыть диалог
     * @param bool $isuser
     */
    public function close($isuser = false){
        global $L;

        if ($this->_data['istate'] != OcThread::STATE_CLOSED) {
            $this->_data['istate'] = OcThread::STATE_CLOSED;

            $cond = array(
                array('threadid',  $this->_data['threadid']),
                array('ikind', OcMessage::KIND_USER),
            );
            $this->_data['messageCount'] = OcMessage::count($cond);
            $this->save();
        }

        $message = $isuser ? str_replace("{0}", $this->_data['userName'], $L['oc']['chat_status_user_left'])
            : str_replace("{0}", $this->_data['agentName'], $L['oc']['chat_status_operator_left']);
        $this->postMessage(OcMessage::KIND_EVENTS, $message);
    }

    /**
     * Оператор забирает диалог
     */
    public function take(){
        global $L, $usr;

        include cot_langfile('oconsultant', 'module', 'en', $this->_data['locale']);

        $message_to_post = "";

        if(in_array($this->_data['istate'], array(OcThread::STATE_QUEUE, OcThread::STATE_WAITING, OcThread::STATE_LOADING)) ){
            $this->_data['istate'] = OcThread::STATE_CHATTING;
            $this->_data['nextagent'] = 0;
            $this->_data['agentId'] = $usr['id'];
            $this->_data['agentName'] = $usr['name'];
            $this->save();

            if ($this->_data['istate'] == OcThread::STATE_WAITING) {
                if ($usr['name'] != $this->_data['agentName']) {
                    $message_to_post = str_replace(array('{0}', '{1}'), array($usr['name'], $this->_data['agentName']),
                        $L['oc']['chat_status_operator_changed']);
                } else {
                    $message_to_post = str_replace('{0}', $usr['name'],
                        $L['oc']['chat_status_operator_returned']);
                }
            } else {
                $message_to_post = str_replace(array('{0}'), array($usr['name']),
                    $L['oc']['chat_status_operator_joined']);
            }
        } else if ($this->_data['istate'] == OcThread::STATE_CHATTING) {
            if ($usr['id'] != $this->_data['agentId']) {
                $this->_data['istate'] = OcThread::STATE_CHATTING;
                $this->_data['nextagent'] = 0;
                $this->_data['agentId'] = $usr['id'];
                $this->_data['agentName'] = $usr['name'];
                $this->save();

                $message_to_post = str_replace(array('{0}', '{1}'), array($usr['name'], $this->_data['agentName']),
                    $L['oc']['chat_status_operator_changed']);
            }
        } else {
            die("cannot take thread");
        }

        if ($message_to_post) {
            $this->postMessage(OcMessage::KIND_EVENTS, $message_to_post);
            $this->postMessage(OcMessage::KIND_AVATAR,
                        $usr["profile"]["user_avatar"] != '' ? $usr["profile"]["user_avatar"] : "no");
        }


    }

    /**
     * Перепроверить назначенного оператора
     */
    public function checkForReasign(){
        global $L, $usr;

        list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('oconsultant', 'any');
        cot_block($usr['auth_write'] || $usr['isadmin']);  // только для операторов

        // Выводим сообщения на языке посетителя
        include cot_langfile('oconsultant', 'module', 'en', $this->_data['locale']);

        if ($this->_data['istate'] == OcThread::STATE_WAITING &&
                                            ($this->_data['nextagent'] == $usr['id']
                                                || $this->_data['agentId'] == $usr['id'])) {
            $oldName = $this->_data['agentName'];
            $this->_data['istate'] = OcThread::STATE_CHATTING;
            $this->_data['nextagent'] = 0;
            $this->_data['agentId'] = $usr['id'];
            $this->_data['agentName'] = $usr['name'];
            $this->save();

            if ($usr['name'] != $oldName) {
                $message_to_post = str_replace(array('{0}', '{1}'), array($usr['name'], $oldName),
                    $L['oc']['chat_status_operator_changed']);
            } else {
                $message_to_post = str_replace(array('{0}'), array($usr['name']),
                    $L['oc']['chat_status_operator_returned']);
            }

            $this->postMessage(OcMessage::KIND_EVENTS, $message_to_post);
            $this->postMessage(OcMessage::KIND_AVATAR, $usr["profile"]["user_avatar"] != '' ? $usr["profile"]["user_avatar"] : "no");

        }
    }

    /**
     * Clear thread history
     * @return int Number of messages removed on success or FALSE on error
     */
    public function cleanHistory(){
        global $db, $db_oc_message;

        return $db->delete($db_oc_message, "threadid=".$this->threadid);
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns thread tags for coTemplate
     *
     * @param OcThread|int $thread OcThread object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTagsForUser($thread, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L, $usr;

        static $extp_first = null, $extp_main = null;
        static $cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('oconsultant.thread.user.tags.first');
            $extp_main = cot_getextplugins('oconsultant.thread.user.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( is_object($thread) && is_array($cache[$thread->threadid]) ) {
            $temp_array = $cache[$thread->threadid];
        }elseif (is_int($thread) && is_array($cache[$thread])){
            $temp_array = $cache[$thread];
        }else{
            if (is_int($thread) && $thread > 0){
                $thread = self::getById($thread);
            }
            if ($thread->threadid > 0){

                $nameisset = ($L['Guest'] != $thread->userName);
                $params = "thread=" . $thread->threadid . "&token=" . $thread->ltoken;
                $temp_array = array(
                    'MAIN_TITLE' => $cfg['maintitle'],
                    'AGENT' => false,
                    'USER' => 1,
                    'CANPOST' => 1,
                    'CANCHANGENAME' => $cfg['oconsultant']['usercanchangename'],
                    'USER_NAME' => htmlspecialchars($thread->userName),
                    'MY_AVATAR' => ($usr['id'] > 0 && !empty($usr['profile']['user_avatar'])) ? $usr['profile']['user_avatar'] : '',
                    'DISPL1' => $nameisset ? "none" : "inline",
                    'DISPL2' => $nameisset ? "inline" : "none",
                    'MAILLINK' => cot_url('oconsultant', "m=client&a=mailthread&".$params),
                    'SERVERLINK' => cot_url('oconsultant', "m=chat"),
                    'NEEDIFRAMESRC' => oc_needsFramesrc(),
                    'ISOPERA95' => oc_is_agent_opera95(),
//       'FREQUENCY' => $cfg['oconsultant']['updatefrequency_chat'],
                    'CT_CHATTHREADID' => $thread->threadid,
                    'CT_TOKEN' => $thread->ltoken,
                );
                // TODO SSL соединение
                if ( $cfg['oconsultant']['enablessl'] == "1" && !is_secure_request()) {
                    $temp_array['SSLLINK'] = get_app_location(true, true) . "/client.php?" . $params;
                }
                if ($cfg['oconsultant']['sendmessagekey'] == 'enter') {
                    $temp_array['SEND_SHORTCUT'] = "Enter";
                    $temp_array['IGNORECTRL'] = 1;
                    $temp_array['SEND_BTN'] = str_replace('{0}', 'Enter', $L['oc']['chat_window_send_message_short']);
                } else {
                    $send_shorcut = oc_is_mac_opera() ? "&#8984;-Enter" : "Ctrl-Enter";
                    $temp_array['SEND_SHORTCUT'] = $send_shorcut;
                    $temp_array['IGNORECTRL'] = 0;
                    $temp_array['SEND_BTN'] = str_replace('{0}', $send_shorcut, $L['oc']['chat_window_send_message_short']);
                }

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $cache[$thread->threadid] = $temp_array;
            }else{
                // Диалога не существует
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

    /**
     * Returns thread tags for coTemplate
     *
     * @param OcThread|int $thread OcThread object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTagsForOperator($thread, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L, $usr;

        static $extp_first = null, $extp_main = null;
        static $cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('oconsultant.thread.user.tags.first');
            $extp_main = cot_getextplugins('oconsultant.thread.user.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( is_object($thread) && is_array($cache[$thread->threadid]) ) {
            $temp_array = $cache[$thread->threadid];
        }elseif (is_int($thread) && is_array($cache[$thread])){
            $temp_array = $cache[$thread];
        }else{
            if (is_int($thread) && $thread > 0){
                $thread = self::getById($thread);
            }
            if ($thread->threadid > 0){

                $canned_messages = array_merge(array(0 => $L['oc']['chat_window_predefined_select_answer']."..."),
                    oc_load_canned_messages($thread->locale, $thread->groupid));
                $predefinedres = cot_selectbox(false, 'predefined', $canned_messages, array(), false, array(
                    'id'=> 'predefined', 'size' => 1, 'class'=>"answer") );

                $params = "thread=" . $thread->threadid . "&token=" . $thread->ltoken;

                $temp_array = array(
                    'AGENT' => 1,
                    'USER' => 0,
                    'CANPOST' => $thread->agentId == $usr['id'],
                    'CT_CHATTHREADID' => $thread->threadid,
                    'CT_TOKEN' => $thread->ltoken,
                    'USER_MANE' => htmlspecialchars(oc_buildUserName($thread->userName, $thread->remote, $thread->userid)),
                    'MY_AVATAR' => ($usr['id'] > 0 && !empty($usr['profile']['user_avatar'])) ? $usr['profile']['user_avatar'] : '',
                    'NEEDIFRAMESRC' => oc_needsFramesrc(),
                    'ISOPERA95' => oc_is_agent_opera95(),
                    'FREQUENCY' => $cfg['oconsultant']['updatefrequency_chat'],
                    'SERVERLINK' => cot_url('oconsultant', "m=chat"),
                    'PREDEFINEDANSWERS' => $predefinedres,
                );

                // TODO SSL соединение
                if ( $cfg['oconsultant']['enablessl'] == "1" && !is_secure_request()) {
                    $temp_array['SSLLINK'] = get_app_location(true, true) . "/client.php?" . $params;
                }

                if ($cfg['oconsultant']['sendmessagekey'] == 'enter') {
                    $temp_array['SEND_SHORTCUT'] = "Enter";
                    $temp_array['IGNORECTRL'] = 1;
                    $temp_array['SEND_BTN'] = str_replace('{0}', 'Enter', $L['oc']['chat_window_send_message_short']);
                } else {
                    $send_shorcut = oc_is_mac_opera() ? "&#8984;-Enter" : "Ctrl-Enter";
                    $temp_array['SEND_SHORTCUT'] = $send_shorcut;
                    $temp_array['IGNORECTRL'] = 0;
                    $temp_array['SEND_BTN'] = str_replace('{0}', $send_shorcut, $L['oc']['chat_window_send_message_short']);
                }

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $cache[$thread->threadid] = $temp_array;
            }else{
                // Диалога не существует
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

}

// Class initialization for some static variables
OcThread::__init();