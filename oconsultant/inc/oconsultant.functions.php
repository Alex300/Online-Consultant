<?php
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage functions
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Global variables
global $db_oc_message, $db_oc_thread, $db_oc_revision, $db_oc_responses, $db_oc_invite, $db_x, $cot_modules;

$db_oc_message      = (isset($db_oc_message)) ? $db_oc_message :$db_x.'oc_message';
$db_oc_thread       = (isset($db_oc_thread)) ? $db_oc_thread :$db_x.'oc_thread';
$db_oc_revision     = (isset($db_oc_revision)) ? $db_oc_revision :$db_x.'oc_revision';
$db_oc_responses    = (isset($db_oc_responses)) ? $db_oc_responses :$db_x.'oc_responses';
$db_oc_invite       = (isset($db_oc_invite)) ? $db_oc_invite :$db_x.'oc_invite';

$oc_namecookie = "OC_Data";
$oc_usercookie = "OC_UserID";

$oc_jsver = $cot_modules['oconsultant']['version'];
//$oc_jsver = "source";
if (!isset ($oC_consGroups)) $oC_consGroups = NULL;

$oc_connection_timeout = 30; // sec
$oc_knownAgents = array("opera", "msie", "chrome", "safari", "firefox", "netscape", "mozilla");

function ocAutoLoader($class){
    global $cfg;
    $fName = $cfg['modules_dir'].DS.'oconsultant'.DS.'models'.DS.$class.'.php';

    if(file_exists($fName)){
        include($fName);
    }
    return false;
}

/**
 * Получить данные пользователя
 *
 * @return array(id, name)
 */
function getVisitorFromRequest(){
    global $usr, $L, $oc_namecookie, $oc_usercookie, $cfg, $sys;

    $userName = '';
    if (isset($_COOKIE[$oc_namecookie])) {
        $data = base64_decode(strtr($_COOKIE[$oc_namecookie], '-_,', '+/='));
        $userName = $data;
    }

    if ($userName == '') {
        $userName = cot_import('name', 'G', 'TXT');
    }
    if (!$userName || $userName == ''){
        $userName = ($usr['name'] != '') ? $usr['name'] : $L['Guest'];
    }

    if($usr['id'] > 0){
        $userId = $usr['id'];
        if(!isset($_COOKIE[$oc_usercookie]) || $_COOKIE[$oc_usercookie] != $usr['id']){
            cot_setcookie($oc_usercookie, $usr['id'], $sys['now'] + 60 * 60 * 24 * 365);
        }
    }elseif(isset($_COOKIE[$oc_usercookie])) {
        $userId = $_COOKIE[$oc_usercookie];
    } else {
        $userId = oc_getUserId();
        cot_setcookie($oc_usercookie, $userId, $sys['now'] + 60 * 60 * 24 * 365);
    }
    return array('id' => $userId, 'name' => $userName);
}

function oc_getUserId(){
    global $usr;

    return ($usr['id'] > 0) ? $usr['id'] : (time() + microtime()) . rand(0, 99999999);
}

function oc_is_mac_opera(){
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    return strstr($useragent, "opera") && strstr($useragent, "mac");
}
function oc_needsFramesrc()
{
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    return strstr($useragent, "safari/");
}
function oc_is_agent_opera95()
{
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strstr($useragent, "opera")) {
        if (preg_match("/opera[\\s\/]?(\\d+(\\.\\d+)?)/", $useragent, $matches)) {
            $ver = $matches[1];

            if ($ver >= "9.5")
                return true;
        }
    }
    return false;
}
/**
 * Получить удаленный хост
 * @return string
 */
function oc_get_remote_host(){
    $extAddr = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
        $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR']) {
        $extAddr = $_SERVER['REMOTE_ADDR'] . ' (' . $_SERVER['HTTP_X_FORWARDED_FOR'] . ')';
    }
    return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $extAddr;
}


// ==== Общие функции ====
function oc_start_xml_output()
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Content-type: text/xml; charset=utf-8");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
}

function oc_start_html_output()
{
    global $cfg;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Content-type: text/html; charset=" . $cfg['charset']);
}

/**
 * Сделать CDATA
 * @param string $text
 * @return string text 
 */
function oc_escape_with_cdata($text)
{
	return "<![CDATA[" . str_replace("]]>", "]]>]]&gt;<![CDATA[", $text) . "]]>";
}


/**
 * // TODO geolink - геолокация пользователя
 * @param $addr
 * @return string
 */
function oc_buildUserAddr($addr){
	global $cfg;

	if ($cfg['oconsultant']['geolink'] && preg_match("/(\\d+\\.\\d+\\.\\d+\\.\\d+)/", $addr, $matches)) {
		$userip = $matches[1];
		return get_popup(str_replace("{ip}", $userip, $cfg['oconsultant']['geolink']), '', htmlspecialchars($addr), "GeoLocation", "ip$userip", $settings['geolinkparams']);
	}
	return $addr;
}
/**
 * Build User Name
 * @param string $username
 * @param string $addr
 * @param string $id
 * @return string
 */
function oc_buildUserName($username, $addr, $id){
    global $cfg;

    return str_replace(array("{addr}",'{id}', '{name}'), array($addr, $id, $username),
        $cfg['oconsultant']['usernamepattern']);
}


/**
 * Версия браузера
 * @param type $userAgent
 * @return string 
 */
function oc_get_useragent_version($userAgent){
	global $oc_knownAgents;

	if (is_array($oc_knownAgents)) {
		$userAgent = strtolower($userAgent);
		foreach ($oc_knownAgents as $agent) {
			if (strstr($userAgent, $agent)) {
				if (preg_match("/" . $agent . "[\\s\/]?(\\d+(\\.\\d+(\\.\\d+(\\.\\d+)?)?)?)/", $userAgent, $matches)) {
					$ver = $matches[1];
                    if ($agent == 'opera' ) {
						if (preg_match("/version\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
							$ver = $matches[1];
						}
                    }
					if ($agent == 'safari' ) {
						if (preg_match("/version\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
							$ver = $matches[1];
						} else {
							$ver = "1 or 2 (build " . $ver . ")";
						}
						if (preg_match("/mobile\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
							$userAgent = "iPhone " . $matches[1] . " ($agent $ver)";
							break;
						}
					}

					$userAgent = ucfirst($agent) . " " . $ver;
					break;
				}
			}
		}
	}
	return $userAgent;
}

function div($a, $b){
	return ($a - ($a % $b)) / $b;
}

function oc_date_diff_to_text($seconds){
	$minutes = div($seconds, 60);
	$seconds = $seconds % 60;
	if ($minutes < 60) {
		return sprintf("%02d:%02d", $minutes, $seconds);
	} else {
		$hours = div($minutes, 60);
		$minutes = $minutes % 60;
		return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	}
}

/**
 * @param $unixtime
 * @return string
 * @todo use Cotonti date format
 */
function oc_date_to_text($unixtime){
    global $L, $usr;
	if ($unixtime < 60 * 60 * 24 * 30) return $L['oc']["time_never"];

	$then = getdate($unixtime);
	$now = getdate();

	if ($then['yday'] == $now['yday'] && $then['year'] == $now['year']) {
		$date_format = $L['oc']["time_today_at"];
	} else if (($then['yday'] + 1) == $now['yday'] && $then['year'] == $now['year']) {
		$date_format = $L['oc']["time_yesterday_at"];
	} else {
		//$date_format = $L['oc']["time_dateformat"];
        return cot_date('datetime_text', $unixtime);
	}
    $unixtime += $usr['timezone'] * 3600;
	return strftime($date_format . " " . $L['oc']["time_timeformat"], $unixtime);
}

/**
 * Получить все локализации плагина
 * @return array 
 */
function oc_get_available_locales(){
	global $cfg;

	$list = array();
    $folder = $cfg['modules_dir'].DS.'oconsultant'.DS.'lang';
    $locale_pattern = "/^oconsultant.([\w-]{2,5}).lang.php$/";

	if ($handle = opendir($folder)) {
		while (false !== ($file = readdir($handle))) {
            if ( $file=="." || $file=="..") confinue;
			if (preg_match($locale_pattern, $file, $matches)) {
				$list[] = $matches[1];
			}
		}
		closedir($handle);
	}
	sort($list);
	return $list;
}

/**
 * Получить название языка локализации
 * @param bool|string $lang  - сокращение локализации ('en', 'ru' и т.п.)
 * @return string|bool
 */
function oc_getLocalName($lang = false){
    include(cot_langfile('oconsultant', 'module', 'en', $lang ));
    
    if (isset($L['oc']['locale_name'])) return $L['oc']['locale_name'];
    
    return false;
}
/**
 * Загрузить ответы оператора по-умолчанию
 * @param string $locale - язык (en, ru...)
 * @param int $groupid = id группы
 * @return array 
 */
function oc_load_canned_messages($locale = '', $groupid = 0){
	global $usr, $db_oc_responses, $db;
    
    if ($locale=='') $locale = $usr['lang'];
    $result = array();
    $groupCond = '';
    if ($groupid > 0) {
		$groupCond = " OR groupid = $groupid";
	}
    
    $query = "SELECT id, vcvalue FROM $db_oc_responses WHERE locale = '$locale' " .
		"AND (groupid is NULL OR groupid = 0 $groupCond ) ORDER BY vcvalue";
    $res = $db->query($query);
    while($row = $res->fetch()){
        $result[$row['id']] = $row['vcvalue'];
    }
	return $result;
}

/**
 * Проверить наличие нужных JS файлов
 * @param bool $modified
 * @todo учесть $modified
 */
function oc_checkJsFiles($modified = false){
    global $oc_jsver, $cfg, $sys;

    if($oc_jsver == 'source') return;
    //$cfg['oconsultant']
    $path = $cfg['modules_dir'].DS.'oconsultant'.DS.'js'.DS.$oc_jsver;
    $source = $cfg['modules_dir'].DS.'oconsultant'.DS.'js'.DS.'source';
    if(!file_exists($path)){
        mkdir($path);
        copy($source.DS.'index.html', $path.DS.'index.html');
    }

    if(!file_exists($path)){
        cot_error("Dir '{$path}' not exists");
        return false;
    }

    $files = oc_getFilesList($source);
    foreach($files as $file){
        $path_parts = pathinfo($file);
        if(empty($path_parts['extension']) || $path_parts['extension'] != 'js') continue;

        $newFile = $path.DS.$path_parts['basename'];
        if(file_exists($newFile)){
            continue;
        }
        $sourceText = file_get_contents($file);
        $newText = "/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © 2011-".date('Y', $sys['now'])." Portal30 Studio http://portal30.ru
 */";
        $newText .= cot_rc_minify($sourceText);
        file_put_contents($newFile, $newText);
    }


    //cot_rc_minify();
}

/**
 * Files list in folder
 * @param $folder
 * @return array
 */
function oc_getFilesList($folder){
    $all_files = array();
    $fp=opendir($folder);
    while($cv_file=readdir($fp)) {
        if(is_file($folder."/".$cv_file)) {
            $all_files[]=$folder."/".$cv_file;
        }elseif($cv_file!="." && $cv_file!=".." && is_dir($folder."/".$cv_file)){
            GetListFiles($folder."/".$cv_file,$all_files);
        }
    }
    closedir($fp);
    return $all_files;
}

spl_autoload_register('ocAutoLoader');