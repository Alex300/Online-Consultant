<?PHP
/* ====================
[BEGIN_COT_EXT]
Hooks=module
[END_COT_EXT]
==================== */
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL.');

// Environment setup
$env['location'] = 'oconsultant';

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('oconsultant', 'any');

//require_once cot_incfile('oconsultant', 'module', 'chat.inc');
require_once cot_incfile('oconsultant', 'module', 'groups.inc');
require_once cot_incfile('oconsultant', 'module', 'ajax.inc');
require_once cot_incfile('oconsultant', 'module', 'operators.inc');


require_once cot_langfile('oconsultant', 'module');
// Self requirements
require_once cot_incfile('oconsultant', 'module');
//require_once cot_incfile('oconsultant', 'module', 'resources');

if (COT_AJAX && !$m) $m = 'ajax';
if (!$m) $m = 'operator';

/**
 * Отображаенть header и footer
 */
$ext_display_header = true;

// Only if the file exists...
if (file_exists(cot_incfile('oconsultant', 'module', $m))) {
    require_once cot_incfile('oconsultant', 'module', $m);

    // Пока ajax просто инклудим
    if ($m != 'ajax'){
        /* Create the controller */
        $_class = ucfirst($m).'Controller';
        $controller = new $_class();

        // TODO кеширование
        /* Perform the Request task */
        $shop_action = $a.'Action';
        if (!$a && method_exists($controller, 'indexAction')){
            $content = $controller->indexAction();
        }elseif (method_exists($controller, $shop_action)){
            $content = $controller->$shop_action();
        }else{
            // Error page
            cot_die_message(404);
            exit;
        }
    }
    //ob_clean();
    if ($ext_display_header){
        require_once $cfg['system_dir'] . '/header.php';
    }else{
        cot_sendheaders();
    }
    if (isset($content)) echo $content;
    if ($ext_display_header){
        require_once $cfg['system_dir'] . '/footer.php';
    }
}else{
    // Error page
    cot_die_message(404);
    exit;
}