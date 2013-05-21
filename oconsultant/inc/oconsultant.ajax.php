<?PHP
defined('COT_CODE') or die('Wrong URL.');
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @subpackage Chat
 * @todo нормальный контролер
 */

// === Диалог (thread) ===
ob_clean();
$oc_title = '';

$act = cot_import('act', 'P', 'ALP');
if (!preg_match("/^(refresh|post|rename|close|ping|redirecttourl|redirecttourldone)$/", $act)){
    die("Wrong URL.");
}

$token = cot_import("token", 'P', 'INT');
$threadid = cot_import("thread", 'P', 'INT');
$isuser = cot_import("user", 'P', 'ALP', 5);
$isuser = ($isuser == 'true') ? true : false;
$outformat = ((cot_import( "html",'P', 'ALP', 3) == 'on') ? "html" : "xml");
$istyping = cot_import( "typed", "P", "INT", 1) == 1;


// TODO демо режим

$thread = oc_thread_by_id($threadid);
if( !$thread || !isset($thread['ltoken']) || $token != $thread['ltoken'] ) {
	die("wrong thread");
}

function oc_show_ok_result($resid) {
	oc_start_xml_output();
	echo "<$resid></$resid>";
	exit;
}

function oc_show_error($message) {
	oc_start_xml_output();
	echo "<error><descr>$message</descr></error>";
	exit;
}

oc_ping_thread($thread, $isuser,$istyping);


if( $act == "ping" ) {
	oc_show_ok_result("ping");

}
