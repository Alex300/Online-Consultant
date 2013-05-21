<?php
/**
 * Installs Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage install
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('oconsultant', 'module');
global $db, $db_oc_responses, $db_oc_message, $db_oc_thread, $db_oc_revision, $db_oc_invite, $db_online;

/**
 * Установить быстрые ответы
 * @global CotDb $db
 */
function oc_install_PredefResponses(){
	global $db, $db_oc_responses;
	$all_locales = oc_get_available_locales();
	if (count($all_locales) > 0){
		foreach ($all_locales as $locale){
			$query = "SELECT COUNT(*) FROM $db_oc_responses WHERE locale='$locale'";
			$res = $db->query($query);
			$cnt = $res->fetchColumn();
			if ($cnt > 0) continue;

			include(cot_langfile('oconsultant', 'module', $locale, $locale));
			if (!isset($L['oc']['chat_predefined_answers']) || $L['oc']['chat_predefined_answers'] == '')
				continue;
			$query = "INSERT INTO $db_oc_responses (vcvalue,locale,groupid) VALUES ";
			$i = 0;
			foreach (explode("\n", $L['oc']['chat_predefined_answers']) as $answer)
			{
				if ($i > 0)
					$query .= ", ";
				$query .= "('" . $db->prep($answer) . "','$locale', NULL)";
				$i++;
			}
			if ($i > 0)
				$db->query($query);
		}
	}
}

//проверка на наличие записей в таблице
if ($db->query("SELECT COUNT(*) FROM `{$db_oc_revision}`")->fetchColumn() == 0){
    $db->insert($db_oc_revision, array('id' => 0));
}
// Предопределенные ответы операторов
oc_install_PredefResponses();

// Дополнительные поля в $db_online
if (!$db->fieldExists($db_online, "online_location_code")){
	$db->query("ALTER TABLE $db_online ADD `online_location_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
}
if (!$db->fieldExists($db_online, "online_title")){
	$db->query("ALTER TABLE $db_online ADD `online_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
}
if (!$db->fieldExists($db_online, "online_breadcrumb")){
	$db->query("ALTER TABLE $db_online ADD `online_breadcrumb` text COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
}
if (!$db->fieldExists($db_online, "online_uri")){
	$db->query("ALTER TABLE $db_online ADD `online_uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
}
if (!$db->fieldExists($db_online, "online_user_agent")){
	$db->query("ALTER TABLE $db_online ADD `online_user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
}