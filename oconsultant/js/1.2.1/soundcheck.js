/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © 2011-2013 Portal30 Studio http://portal30.ru
 */
Behaviour.register({'a#check-nv':function(el){el.onclick=function(){playSound(wroot+'/sounds/new_user.wav');};},'a#check-nm':function(el){el.onclick=function(){playSound(wroot+'/sounds/new_message.wav')};}});