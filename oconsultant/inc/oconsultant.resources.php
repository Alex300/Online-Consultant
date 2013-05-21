<?php
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL.');

$R['oc_ocButtonOnLine'] = '<a id="oc_button" class="btn btn-primary">
            <i class="icon-comment icon-white"></i>
            <div class="pull-right marginleft10">
                '.$L['oc']['site_consultant'].'<br />
                <span class="small">'.$L['Online'].'</span><br />
                <span class="small">'.$L['oc']['ask_question'].'</span>
            </div>
        </a>';

$R['oc_ocButtonOffLine'] = '<a id="oc_button" class="btn">
            <i class="icon-envelope"></i>
            <div class="pull-right marginleft10">
                '.$L['oc']['site_consultant'].'<br />
                <span class="small">'.$L['Offline'].'</span><br />
                <span class="small">'.$L['oc']['leavemessage'].'</span>
            </div>
        </a>';

/**
 * Icons
 */
//$R['page_icon_file_path'] = 'images/filetypes/default/{$type}.png';
