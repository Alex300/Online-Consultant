/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
var oc_timer = '';

function oc_update(){
   jQuery.post(updaterOptions.url, { x:updaterOptions.x || 0 }, function(data) {
        if (data){
            if (data.error){
                //jQuery('#ostatus_'+onlineId).html(data.error);
            }else if(data.iid > 0){
                oc_showInviteDialog(data.itext);
            }
        }
    }, 'json');
}

/**
 * Показать диалог приглашения в чат 
 */
var oc_invDialog = false;
function oc_showInviteDialog(msg){
    msg = msg || updaterOptions.localized.invite_msg;
   clearInterval(oc_timer);
   //alert(updaterOptions.localized.invite_msg);
   if (!jQuery.ui){
        return false;
   } 
   var invAccepted = false;
   if(!oc_invDialog){
       var html = $('#invite_dlg').html();
       // Если в шаблоне нет div с id="invite_dlg" То создадим содержимое диалога
       if(!html){
         divElement = document.createElement("div");
         divElement.setAttribute('id', 'invite_dlg');
         divElement.innerHTML = '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td valign="middle"><img src="'+ updaterOptions.wroot +'/tpl/img/operator.png" /></td><td style="vertical-align: middle"><p>'+ msg +'</p></td></tr></table>';
         document.body.insertBefore(divElement, document.body.firstChild);
       }
       $('#invite_dlg').dialog({
            title: updaterOptions.localized.invite_title,
            buttons: [
                {text: updaterOptions.localized.accept,
                click: function() { 
                    invAccepted = true;
                    oc_responseInvite(1);
                    $(this).dialog("close");
                }},
                {text: updaterOptions.localized.reject,
                click: function() { $(this).dialog("close"); }}
            ],
            minWidth: 400,
            show: "blind",
            hide: 'blind',
            resizable: false,
            autoOpen: false,
            close: function(event, ui) {
                oc_timer = setInterval(oc_update, updaterOptions.invfrequency*1000);
                if(!invAccepted){
                    oc_responseInvite(2);
                }
            }
        });
        oc_invDialog = true;
   }
   if (!$('#invite_dlg').dialog("isOpen")) $('#invite_dlg').dialog("open");
   //}
}

/**
 * Открыть окно чата
 */
function oc_openChat(inv){
    inv = inv || '';

    if(navigator.userAgent.toLowerCase().indexOf('opera') != -1 && window.event.preventDefault){
        window.event.preventDefault();
    }

    if (inv != '') inv = '&inv=1';
    // TODO FIX может не работать с ЧПУ
    this.newWindow = window.open(updaterOptions.chatUrl+inv+'&url='+escape(document.location.href)+'&referrer='+escape(document.referrer), 'oconsultant', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,width=640,height=480,resizable=1');
    this.newWindow.opener=window;
    this.newWindow.focus();
}

/**
 * Посетитель ответил на приглашение
 */
function oc_responseInvite(answer){
    // Открыть чат
    if (answer == 1){
        oc_openChat(1);
    }
    jQuery.post(updaterOptions.url, { x:updaterOptions.x || 0, act:'ansver', answer: answer }, function(data) {
        //...
    }, 'json');
}

jQuery(document).ready(function() {
    if (jQuery.ui){
        oc_update();
        // устанавливаем таймер опроса
        oc_timer = setInterval(oc_update, updaterOptions.invfrequency*1000);
    }
    if(updaterOptions.updOperStatus == 1){
        jQuery.post('index.php?e=oconsultant&m=client&a=operator_status', {
            locinfo: updaterOptions.locinfo,
            x:updaterOptions.x || 0
        }, function(data) {
            if (data){
                if (data.error){
                    //jQuery('#ostatus_'+onlineId).html(data.error);
                }else if(data.button != ''){
                    $('.oc_button_cont').html(data.button);
                }
            }
        }, 'json');
    }

    jQuery(document).on('click', '#oc_button', function(){ oc_openChat(); return false });
});