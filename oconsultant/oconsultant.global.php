<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
[END_COT_EXT]
==================== */
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL.');

// Действия выполняются если не обратилист к сайту по AJAX
if (!COT_AJAX){
    require_once cot_langfile('oconsultant', 'module');

    require_once cot_incfile('oconsultant', 'module');
    require_once cot_incfile('oconsultant', 'module', 'resources');
    require_once cot_incfile('oconsultant', 'module', 'operators.inc');

    $e = cot_import('e', 'G', 'ALP');

    // раз в сутки очищать устаревшие приглашения в чат для незарегов
    // TODO     и закрывать диалоги, срок которых больше заданного периода
    if (file_exists($cfg['modules_dir'].'/oconsultant/inc/procesed.txt')){
        $ocLastProc = implode('', file($cfg['modules_dir'].'/oconsultant/inc/procesed.txt'));
    }else{
        $ocLastProc = 0;
    }
    if ($ocLastProc <= ($sys['now']-86400)){
        //require_once $cfg['plugins_dir'].DS.'an_o_consultant'.DS.'inc'.DS.'/an_o_consultant.inc.php';
        //$query = "DELETE FROM $db_oc_invite WHERE user_id<1 AND inv_dtsended<'".date('Y-m-d H:i:s', $sys['now']-86400)."'";
        $db->delete($db_oc_invite, "user_id<1 AND inv_dtsended<'".date('Y-m-d H:i:s', $sys['now']-86400)."'");
        $fp=fopen($cfg['modules_dir'].'/oconsultant/inc/procesed.txt','w');
        fwrite($fp, $sys['now']);
        fclose($fp);
    }

    $ocShowBtn = true;
    if($e == 'oconsultant') $ocShowBtn = false;

    // Код кнопки консультанта {PHP.oC_button}
    if ($env['ext'] != 'admin' && $ocShowBtn ){
        $oC_hasOnlineOperators = oc_has_online_operators() ? 1 : 0;
        if($oC_hasOnlineOperators){
            $oC_button = cot_rc('oc_ocButtonOnLine');
        }else{
            $oC_button = cot_rc('oc_ocButtonOffLine');
        }
        $oC_button = "<span class=\"oc_button_cont\">{$oC_button}</span>";
    }
}
