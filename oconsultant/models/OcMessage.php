<?php
defined('COT_CODE') or die('Wrong URL.');
/**
 * Model class for the Messages
 *
 * @package Online Consultant
 * @subpackage DB
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 *
 * @property int $messageid
 * @property int $threadid     id Диалога
 * @property int $agentId      id Оператора
 * @property int $ikind        Тип
 * @property string $tmessage
 * @property string $dtmcreated
 * @property string $tname
 *
 * @method static OcMessage getById(int $pk)
 * @method static OcMessage[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static OcMessage[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 *
 */
class OcMessage extends OcModelAbstract{

    // === Типы сообщений ===
    const KIND_USER = 1;
    const KIND_AGENT = 2;
    const KIND_FOR_AGENT = 3;
    const KIND_INFO = 4;
    const KIND_CONN = 5;
    const KIND_EVENTS = 6;
    const KIND_AVATAR = 7;
    /**
     * redirect to new url
     */
    const KIND_REDIRECT = 8;
    /**
     * redirect to new url done
     */
    const KIND_REDIRECT_DONE = 9;
    // === /Типы сообщений ===

    protected static $kindToString = array(
        OcMessage::KIND_USER => "user",
        OcMessage::KIND_AGENT => "agent",
        OcMessage::KIND_FOR_AGENT => "hidden",
        OcMessage::KIND_INFO => "inf",
        OcMessage::KIND_CONN => "conn",
        OcMessage::KIND_EVENTS => "event",
        OcMessage::KIND_AVATAR => "avatar"
    );

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
        global $db_oc_message;

        self::$_table_name = $db_oc_message;
        self::$_primary_key = 'messageid';
        parent::__init();

    }

    /**
     * Save data
     * @param OcMessage|array|null $data
     * @return int id of saved record
     */
    public function save($data = null){
        global $sys, $usr;

        if(!$data) $data = $this->_data;

        if ($data instanceof OcThread) {
            $data = $data->toArray();
        }

        if(!$data['threadid']) {
            // Добавить новый
            if(empty($data['dtmcreated'])) $data['dtmcreated'] = date('Y-m-d H:i:s', $sys['now']);
        }
        $id = parent::save($data);

        return $id;
    }

    public function toHtml(){
        global $sys;
        if ($this->ikind == OcMessage::KIND_AVATAR) return "";

        $msgDate = explode(' ',$this->dtmcreated);
        $msgDate = $msgDate[0];
        $today = date('Y-m-d', $sys['now']);
        if($msgDate != $today){
            $message = "<span>" . cot_date("datetime_full", strtotime($this->dtmcreated)) . "</span> ";
        }else{
            $message = "<span>" . cot_date("time_full", strtotime($this->dtmcreated)) . "</span> ";
        }
        $kind = OcMessage::kindToString($this->ikind);

        if ($this->tname) $message .= "<span class='n$kind'>" . htmlspecialchars($this->tname) . "</span>: ";
        $message .= "<span class='m$kind'>" . OcMessage::prepareHtmlMessage($this->tmessage) . "</span><br/>";
        return $message;
    }

    public function toText(){
        if ($this->ikind == OcMessage::KIND_AVATAR) return "";

        $message_time = cot_date('datetime_full', strtotime($this->dtmcreated)).' ';
        if ($this->ikind == OcMessage::KIND_USER || $this->ikind == OcMessage::KIND_AGENT) {
            if ($this->tname)
                return $message_time . $this->tname . ": " . $this->tmessage . "\n";
            else
                return $message_time . $this->tmessage . "\n";
        } else if ($this->ikind == OcMessage::KIND_INFO) {
            return $message_time . $this->tmessage . "\n";
        } else {
            return $message_time . "[{$this->tmessage}]\n";
        }
    }

    public static function kindToString($kind){
        if(isset(OcMessage::$kindToString[$kind])) return OcMessage::$kindToString[$kind];

        return '';
    }

    /**
     * Подготовить html к выводу в чат
     * @param $text
     * @return mixed
     */
    protected static function prepareHtmlMessage($text){
        $escaped_text = htmlspecialchars($text);
        $text_w_links = preg_replace('/(http|ftp):\/\/\S*/', '<a href="$0" target="_blank">$0</a>', $escaped_text);
        $multiline = str_replace("\n", "<br/>", $text_w_links);
        return $multiline;
    }

}

// Class initialization for some static variables
OcMessage::__init();