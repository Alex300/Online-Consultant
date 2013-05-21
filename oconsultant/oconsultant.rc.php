<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=rc
[END_COT_EXT]
==================== */
/**
 * Online Consultant Module for Cotonti
 *   Безусловная загрузка JS and CSS resources. Котороые разрешены к консолидации
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

// Загрузка JS CSS
// для фронтенд в глобал еще не определена $env
global $env, $cfg, $cache;

//Включить ли jQueryUI
if ($cfg['oconsultant']['jQueryUIon'] == 1 && !defined('COT_ADMIN')) {
    $jsFunc = 'cot_rc_link_footer';
    if($cfg['headrc_consolidate'] && $cache) $jsFunc = 'cot_rc_add_file';

    cot_rc_add_file($cfg['modules_dir'].'/oconsultant/tpl/cupertino/jquery-ui-1.10.3.custom.min.css', 'css');
    $jsFunc($cfg['modules_dir'].'/oconsultant/js/jquery-ui-1.10.3.custom.min.js');
}