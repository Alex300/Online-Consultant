<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.main
[END_COT_EXT]
==================== */
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

// === Дополнительное инфо о месте пользователя ===
$tmpLoc = array(
    'online_location_code' => $e,
    'online_title' => $out["subtitle"],
    'online_uri' => $out['uri'],
    'online_user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'online_breadcrumb' => '',
);
if ($env['location'] == 'home' && empty($out["subtitle"])) $tmpLoc['online_title'] = $L['Home'];
if ($env['location'] == 'list'){
    $tmpLoc['online_location_code']  = $c;
    $tmpLoc['online_breadcrumb'] = $catpath;
}
if ($env['location'] == 'pages' && $pag["page_id"] != ''){
    $tmpLoc['online_location_code']  = $pag["page_id"];

    $pagepath = cot_structure_buildpath('page', $pag['page_cat']);
    $page_link[] = array($pag['page_pageurl'], $pag['page_title']);
    $tmpLoc['online_breadcrumb'] = cot_breadcrumbs(array_merge($pagepath, $page_link), $cfg['homebreadcrumb']);
}
// === /Дополнительное инфо о месте пользователя ===

// ==== опрос сервера браузером посетителя ====
if($env['location'] != 'oconsultant' && $env['ext'] != 'admin'){

    require_once cot_langfile('oconsultant', 'module');
    require_once cot_incfile('oconsultant', 'module');

    $ocUpdOperStatus = 0;
    if($cfg['cache'] && $usr['id'] == 0){
        if($cfg['cache_index'] && $env['location'] == 'home') $ocUpdOperStatus = 1;
        if($cfg['cache_page'] && $env['ext'] == 'page') $ocUpdOperStatus = 1;
        if($cfg['cache_forums'] && $env['ext'] == 'forums') $ocUpdOperStatus = 1;
    }
    $jslocinfo = '';
    if ($ocUpdOperStatus == 1){
        // TODO сюда инфу о странице для передачи на сервер при включенном кешировании
        $tmp = array(
            'location' => $env['location'],
            'subloc' => (string) $sys['sublocation'],
            'location_code' => $tmpLoc['online_location_code'],
            'title' => $tmpLoc['online_title'],
            'uri' => $tmpLoc['online_uri'],
            'breadcrumb' => $tmpLoc['online_breadcrumb'],
        );
        $jslocinfo = "\nlocinfo: '".serialize($tmp)."',\n";
    }
    $ocUrl = cot_url('oconsultant', "m=client&a=update", '', true);
    if (!cot_url_check($ocUrl)) $ocUrl = $cfg['mainurl'].'/'.$ocUrl;
    $ocChatUrl = cot_url('oconsultant', "m=client&a=open", '', true);
    if (!cot_url_check($ocChatUrl)) $ocChatUrl = $cfg['mainurl'].'/'.$ocChatUrl;

    $jsVars .= '
        var updaterOptions = {
            url:"' . $ocUrl . '",
            wroot:"' .$cfg['modules_dir'].'/oconsultant",
            chatUrl:"' . $ocChatUrl . '",
            invfrequency: ' . (int)$cfg['oconsultant']['updatefrequency_browser'] . ',
            updOperStatus: '.$ocUpdOperStatus.', '.$jslocinfo.'
            localized: {
                invite_msg: "' . $L['oc']['invite_message'] . '",
                invite_title: "' . $L['oc']['invite_title'] . '",
                accept: "' . $L['oc']['invite_accept'] . '",
                reject: "' . $L['oc']['invite_reject'] . '"
            },
            x: "' . $sys['xk'] . '"
        };';
    cot_rc_embed_footer($jsVars);
    unset($jsVars);
    unset($jslocinfo);

    cot_rc_link_footer($cfg['modules_dir'].'/oconsultant/js/' . $oc_jsver . '/visitors.js?'.$cot_modules['oconsultant']['version']);
}

// Сохранить Дополнительную информацию в whosonline
if ($usr['id']>0){
    $where = "online_userid=".$usr['id'];
}else{
    $where = "online_ip='".$usr['ip']."'";
}
//var_dump($env);
//echo "<br />=======<br />";
//var_dump($out);
//echo "<br />=======<br />";
//var_dump($pag);

//TODO проделать это для форума: sublocation=sections, topics, posts и ее код
$db->update($db_online, $tmpLoc, $where);