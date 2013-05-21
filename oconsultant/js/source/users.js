/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 */

var HtmlGenerationUtils = {

    popupLink: function(link, title, wndid, inner, width, height,linkclass) {
        return '<a href="'+link+'"'+(linkclass != null ? ' class="'+linkclass+'"' : '')+' target="_blank" title="'+title+'" onclick="this.newWindow = window.open(\''+link+'\', \''+
            wndid+'\', \'toolbar=0,scrollbars=0,location=0,status=1,menubar=0,width='+width+',height='+height+',resizable=1\');this.newWindow.focus();this.newWindow.opener=window;return false;">'+
            inner+'</a>';
    },

    generateOneRowTable: function(content) {
        return '<table class="inner"><tr>' + content + '</tr></table>';
    },

    viewOpenCell: function(username,servlet,id,canview,canopen,ban,message,cantakenow) {
        var cellsCount = 2;
        var link = servlet+"&thread="+id;
        var gen = '<td>';
        if(canopen || canview ) {
            gen += HtmlGenerationUtils.popupLink( (cantakenow||!canview) ? link : link+"&viewonly=true",
                (canopen ? localized.open : localized.view_thread), "ImCenter"+id, username, 640, 480, null);
        } else {
            gen += '<a href="#">' + username + '</a>';
        }
        gen += '</td>';
        if( canopen ) {
            gen += '<td class="icon">';
            gen += HtmlGenerationUtils.popupLink( link, localized.open, "ImCenter"+id, '<img src="'+webimRoot+'/tpl/img/tbliclspeak.gif" width="15" height="15" border="0" alt="'+localized.open+'">', 640, 480, null);
            gen += '</td>';
            cellsCount++;
        }
        if( canview ) {
            gen += '<td class="icon">';
            gen += HtmlGenerationUtils.popupLink( link+"&viewonly=true", localized.view_thread, "ImCenter"+id, '<img src="'+webimRoot+'/tpl/img/tbliclread.gif" width="15" height="15" border="0" alt="'+localized.view_thread+'">', 640, 480, null);
            gen += '</td>';
            cellsCount++;
        }
        if( message != "" ) {
            gen += '</tr><tr><td class="firstmessage" colspan="'+cellsCount+'"><a href="javascript:void(0)" title="'+message+'" onclick="alert(this.title);return false;">';
            gen += message.length > 30 ? message.substring(0,30) + '...' : message;
            gen += '</a></td>';
        }
        return HtmlGenerationUtils.generateOneRowTable(gen);
    },
    banCell: function(id,banid){
        return '<td class="icon">'+
            HtmlGenerationUtils.popupLink( webimRoot+'/operator/ban.php?'+(banid ? 'id='+banid : 'thread='+id), localized.ban, "ban"+id, '<img src="'+webimRoot+'/images/ban.gif" width="15" height="15" border="0" alt="'+localized.ban+'">', 720, 480, null)+
            '</td>';
    }
};



/**
 *
 * @constructor
 */
ThreadUpdater = function(options){
    this._options = jQuery.extend({}, options);
    this._options.lastrevision = 0;
    this.frequency = (this._options.frequency || 2);
    this.executing = false;
    this.threadTimers = new Object();
    this.focused = true;
    this.dlgActive = false;
    this.titleNotyfier = {
        defaultTitle : document.title,
        len : 0
    };
//    this.t = jQuery('#threadlist');
    this.delta = 0;

    me = this;

    jQuery('#predefined').change(function(){
        if (jQuery('#predefined').val() != 0){
            jQuery('#inv_message').html(jQuery('#predefined :selected').html());
        }
        return false;
    });

    jQuery(document).focus(function () {
        me.focused = true;
        document.title = me.titleNotyfier.defaultTitle;
    });
    jQuery(window).focus(function () {
        me.focused = true;
        document.title = me.titleNotyfier.defaultTitle;
    });

    jQuery(document).blur(function() {
        me.focused = false;
    });
    jQuery(window).blur(function() {
        me.focused = false;
    });

    this.update();
}
/**
 *
 * @type {Object}
 */
ThreadUpdater.prototype = {

    update: function(){
        if (this.executing) return false;
        var me = this;
        this.executing = true;

        var jqxhr = jQuery.post(this._options.url, {
                since: this._options.lastrevision,
                status: this._options.istatus,
                showonline: this._options.showonline ? 1 : 0,
                x: (this._options.x || 0)
            },
            function(data) {
                me.executing = false;
                me.updateContent(data)
            }, 'json'
        ).error(function() { me.handleError("reconnecting"); });

        this.timer = setTimeout(function() { me.update() } , this.frequency * 1000);
    },

    stopUpdate: function() {
        clearTimeout(this.timer);
    },

    setStatus: function(msg){
        $('#connstatus').html(msg);
    },

    handleError: function(s) {
        this.setStatus( s );
    },

    getTimeSince: function(srvtime) {
        var secs = Math.floor(((new Date()).getTime()-srvtime-this.delta)/1000);
        var minutes = Math.floor(secs/60);
        var prefix = "";
        secs = secs % 60;
        if( secs < 10 )
            secs = "0" + secs;
        if( minutes >= 60 ) {
            var hours = Math.floor(minutes/60);
            minutes = minutes % 60;
            if( minutes < 10 )
                minutes = "0" + minutes;
            prefix = hours + ":";
        }

        return prefix + minutes+":"+secs;
    },

    /**
     * Обновить статусное сообщение
     */
    updateQueueMessages: function() {
        function queueNotEmpty(id) {
            var startRow = jQuery('#threadlist').find('#t'+id);
            var endRow   = jQuery('#threadlist').find('#t'+id+'end');
            if( startRow.length == 0 || endRow.length == 0 ) return false;

            return startRow.get(0).rowIndex+1 < endRow.get(0).rowIndex;
        }
        var _status = jQuery("#statustd");
        if( _status.length > 0) {
            var notempty = queueNotEmpty("wait") || queueNotEmpty("prio") || queueNotEmpty("chat");
            _status.html(notempty ? "" : this._options.noclients);
            _status.css('height', (notempty ? 5 : 30) );
        }
    },


    updateTimers: function() {
        for (var i in this.threadTimers) {
            if (this.threadTimers[i] != null) {
                var value = this.threadTimers[i];

                var row = jQuery('#threadlist').find('#thr'+i);
                if(row.length == 0) row = null;
                if( row != null ) {
                    row.children('#time').html(this.getTimeSince(value[0]));
                    row.children('#wait').html((value[2]!='chat' ? this.getTimeSince(value[1]) : '-'));
                }
            }
        }
    },

    /**
     * Обновить диалог
     * @param node
     */
    updateThread: function(thread) {
        var banid = null;

        thread.ban = thread.ban || null;
        thread.canban = thread.canban || false;
        thread.message = thread.message || false;

        var row = jQuery('#threadlist').find('#thr'+thread.id);
        if(row.length == 0) row = null;

        if( thread.stateid == "closed" ) {
            if( row ) {
                row.remove();
            }
            this.threadTimers[thread.id] = null;
            return;
        }
        var etc = '<td>'+thread.useragent+'</td>';

        if(thread.ban != null) {
            etc = '<td>'+thread.reason+'</td>';
        }

        if(thread.canban) {
            etc += HtmlGenerationUtils.banCell(thread.id,thread.banid);
        }
        etc = HtmlGenerationUtils.generateOneRowTable(etc);

        var startRow = jQuery('#threadlist').find('#t'+thread.stateid);
        var endRow   = jQuery('#threadlist').find('#t'+thread.stateid+'end');

        if( row != null && (row.get(0).rowIndex <= startRow.get(0).rowIndex || row.get(0).rowIndex >= endRow.get(0).rowIndex ) ) {
            row.remove();
            this.threadTimers[thread.id] = null;
            row = null;
        }
        if( row == null ) {
            row = jQuery('<tr>').attr('id', "thr" + thread.id).addClass(((thread.ban == "blocked" && thread.stateid != "chat") ? "ban" : "in"+thread.stateid ));
            startRow.after(row);
            this.threadTimers[thread.id] = new Array(thread.time,thread.modified,thread.stateid);
            var cell = jQuery('<td>', { 'id': "name", 'class':  "visitor" });
            cell.html(HtmlGenerationUtils.viewOpenCell(thread.name,this._options.agentservl,thread.id,
                thread.canview, thread.canopen,thread.ban, thread.message, thread.stateid!='chat'));
            row.append(cell);

            cell = jQuery('<td>', { 'id': "contid", 'class':  "visitor" }).css('text-align', 'center').html(thread.addr);
            row.append(cell);

            cell = jQuery('<td>', { 'id': "state", 'class':  "visitor" }).css('text-align', 'center').html(thread.state);
            row.append(cell);

            cell = jQuery('<td>', { 'id': "op", 'class':  "visitor" }).css('text-align', 'center').html(thread.agent);
            row.append(cell);

            cell = jQuery('<td>', { 'id': "time", 'class':  "visitor" }).css('text-align', 'center').html(this.getTimeSince(thread.time));
            row.append(cell);

            cell = jQuery('<td>', { 'id': "wait", 'class':  "visitor" }).css('text-align', 'center')
                .html((thread.stateid!='chat' ? this.getTimeSince(thread.modified) : '-'));
            row.append(cell);

            cell = jQuery('<td>', { 'id': "etc", 'class':  "visitor" }).css('text-align', 'center').html(etc);
            row.append(cell);

            if( thread.stateid == 'wait' || thread.stateid == 'prio' ) return true;
        } else {
            this.threadTimers[thread.id] = new Array(thread.time, thread.modified, thread.stateid);
            row.attr('class', (thread.ban == "blocked" && thread.stateid != "chat") ? "ban" : "in"+thread.stateid);
            row.children('#name').html(HtmlGenerationUtils.viewOpenCell(thread.name,this._options.agentservl,thread.id,
                thread.canview, thread.canopen, thread.ban, thread.message, thread.stateid!='chat'));
            row.children('#contid').html(thread.addr);
            row.children('#state').html(thread.state);
            row.children('#op').html(thread.agent);
            row.children('#time').html(this.getTimeSince(thread.time));
            row.children('#wait').html(thread.stateid!='chat' ? this.getTimeSince(thread.modified) : '-');
            row.children('#etc').html(etc);
        }
        return false;
    },

    /**
     * Обновить список диалогов
     * @param root
     */
    updateThreads: function(data) {
        var newAdded = false;

        var _time = data.time;
        var _revision = data.revision;

        if( _time ) this.delta = (new Date()).getTime() - _time;
        if( _revision ) this._options.lastrevision = _revision;

        var me = this;

        jQuery.each(data.threads, function(key, thread) {
            if( me.updateThread(thread) ) newAdded = true;
        });

        this.updateQueueMessages();
        this.updateTimers();
        this.setStatus(this._options.istatus ? "Away" : "Up to date");

        var me = this;

        if( newAdded ) {
            playSound(webimRoot+'/sounds/new_user.wav');
            // Подсказка в заголовке окна
            if(!this.focused ) {
                this.notifyInTitle();
                window.focus();
            }
            if(updaterOptions.showpopup) {
//                alert(localized.popup_notify);
                if (!this.dlgActive){
                    this.dlgActive = true;
                    var dlgDiv = jQuery('<div>', { 'id': "dlgDiv", 'class':  "visitor" }).css('text-align', 'center')
                        .html(localized.popup_notify);
                    jQuery(dlgDiv).dialog({
                            title: localized.popup_notify,
                            buttons: [
                                {text: 'Ok',
                                    click: function() { jQuery(this).dialog("close"); }}
                            ],
                            show: "blind",
                            hide: 'blind',
                            resizable: false,
                            modal: true,
                            close: function(event, ui) {
                                me.dlgActive = false;
                            }
                    });
                }
            }
        }
    },

    notifyInTitle: function(){
        if(this.focused) {
            document.title = this.titleNotyfier.defaultTitle;
            return false;
        }
        var tit = '*** '+localized.popup_notify+' ***';
        var maxL = tit.length;

        document.title = tit.substring(0, this.titleNotyfier.len);
        if(this.titleNotyfier.len >= maxL) {
            this.titleNotyfier.len = 1;
            setTimeout("tUpdater.notifyInTitle()", 3000);
        } else {
            this.titleNotyfier.len++;
            setTimeout("tUpdater.notifyInTitle()", 200);
        }
    },

    updateOperators: function(data){
        var div = jQuery('#onlineoperators');
        if (!div) return;

        var names = [];
        var me = this;
        jQuery.each(data, function(key, operator){
            var isAway = operator.away == 1;
            names[key] =
                '<img src="'+me._options.wroot+'/tpl/img/op'+(isAway ? 'away' : 'online')+
                    '.gif" width="12" height="12" border="0" alt="'+localized.view_thread+'"> '+ operator.name;
        });
        div.html(names.join(', '));
    },

    updateWhoOnline: function(data){
        var oUsers = '';

        jQuery.each(data.users, function(key, user){
            var invStClass = '';

            // Использовать Он лайн ид
            var thropen = '';
            if (user.inv_threadid > 0){
                if( user.thr_canopen == 1 ) {
                    //alert(this._options.agentservl);
                    thropen += HtmlGenerationUtils.popupLink( me._options.agentservl+"&thread="
                        +user.inv_threadid, localized.open, "ImCenter"+user.inv_threadid,
                        '<img src="'+webimRoot+'/tpl/img/tbliclspeak.gif" width="15" height="15" border="0" alt="'+localized.open+'">', 640, 480, null);
                }
                if( user.thr_canview == 1 ) {
                    if (thropen != '') thropen += ' ';
                    thropen += HtmlGenerationUtils.popupLink( me._options.agentservl+"&thread="+user.inv_threadid+"&viewonly=true",
                        localized.view_thread, "ImCenterWO"+user.inv_threadid,
                        '<img src="'+webimRoot+'/tpl/img/tbliclread.gif" width="15" height="15" border="0" alt="'+localized.view_thread+'">', 640, 480, null);
                }
                if (thropen != '') thropen += ' ';
            }
            if (user.online_uri != ''){
                online_title = '<a href="'+user.online_uri+'" title="Open URL" target="_blank">'+user.online_title+'</a>';
                online_url = '<a href="'+user.online_uri+'" title="Open URL" target="_blank">'+user.online_uri+'</a>';
            }
            var wopen = '';
            if( me._options.opcanstart ) {
                var link = me._options.agentservl+'&act=invite_visitor&uid='+user.online_id;
                wopen += '<a href="javascript:void(0)" title="'+localized.invite+'" onclick="tUpdater.inviteUser('+user.online_id+'); return false;"><img src="'+webimRoot+'/tpl/img/tbliclspeak.gif" width="15" height="15" border="0" alt="'+localized.invite+'"></a>'
            }

            if (user.inv_status == '' || user.inv_status == -1){
                invStClass = '';
            }else if (user.inv_status == 2){
                invStClass = 'who_rejected';
            }else if(user.inv_status == 0){
                invStClass = 'who_sended';
            }else if(user.inv_status == 1){
                invStClass = 'who_accepted';
            }

            oUsers += '<tr>';
            oUsers += '<td class="who_id">'+user.id+' '+wopen+'</td>';
            oUsers += '<td class="who_name">'+user.name+' '+user.profile +'</td>';
            oUsers += '<td class="who_grp">'+user.maingrp+'</td>';
            oUsers += '<td class="who_locat">'+user.country_flag+' '+user.location+'</td>';
            oUsers += '<td class="who_onlineloc">'+user.online_location+'</td>';
            // oUsers += '<td id="ostatus_'+onlineUserId+'" class="'+invStClass+'">' + thropen +
            //     jQuery(node).children('inv_text').text()+'</td>';
            oUsers += '<td class="who_ip">'+user.ip+'</td>';
            oUsers += '</tr><tr>';
            oUsers += '<td></td>';
            oUsers += '<td colspan="3"><div class="who_otitle"><i>'+localized.page+':</i> '
                +online_title+'</div></td>';
            oUsers += '<td><div class="who_uri"><i>uri:</i> '+online_url+'</div>';
            if (user.online_breadcrumb != '') oUsers += '<i>'+localized.path+':</i> ' + user.online_breadcrumb;
            oUsers += '</td>';
            oUsers += '<td>'+user.online_user_agent+'</td>';
            oUsers += '</tr><tr>';
            oUsers += '<td></td>';
            oUsers += '<td colspan="6"  id="ostatus_'+user.online_id+'" class="who_bborder '+invStClass+'">' + thropen +
                user.inv_text+'</td>';
            oUsers += '</tr>';
        });


        jQuery.each(data.guests, function(key, user){
            var invStClass = '';
            var online_url = '';

            if (user.online_uri != ''){
                online_title = '<a href="'+user.online_uri+'" title="Open URL" target="_blank">'
                    +user.online_title+'</a>';
                online_url = '<a href="'+user.online_uri+'" title="Open URL" target="_blank">'
                    +user.online_uri+'</a>';
            }
            var wopen = '';
            if( me._options.opcanstart ) {
                var link = me._options.agentservl+'&act=invite_visitor&uid='+user.online_id;
                wopen += '<a href="javascript:void(0)" title="'+localized.invite+'" onclick="tUpdater.inviteUser('+user.online_id+'); return false;"><img src="'+webimRoot+'/tpl/img/tbliclspeak.gif" width="15" height="15" border="0" alt="'+localized.invite+'"></a>'
            }

            var thropen = '';
            if (user.inv_threadid > 0){
                if( user.thr_canopen == 1 ) {
                    thropen += HtmlGenerationUtils.popupLink( me._options.agentservl+"&thread="
                        +user.inv_threadid, localized.open, "ImCenter"+user.inv_threadid,
                        '<img src="'+webimRoot+'/tpl/img/tbliclspeak.gif" width="15" height="15" border="0" alt="'+localized.open+'">', 640, 480, null);
                }
                if( user.thr_canview == 1 ) {
                    if (thropen != '') thropen += ' ';
                    thropen += HtmlGenerationUtils.popupLink( me._options.agentservl
                        +"&thread="+user.inv_threadid+"&viewonly=true", localized.view_thread,
                        "ImCenterWO"+user.inv_threadid,
                        '<img src="'+webimRoot+'/tpl/img/tbliclread.gif" width="15" height="15" border="0" alt="'+localized.view_thread+'">', 640, 480, null);
                }
                if (thropen != '') thropen += ' ';
            }
            invStClass = '';
            if (user.inv_status == '' || user.inv_status == -1){
                invStClass = '';
            }else if (user.inv_status == 2){
                invStClass = 'who_rejected';
            }else if(user.inv_status == 0){
                invStClass = 'who_sended';
            }else if(user.inv_status == 1){
                invStClass = 'who_accepted';
            }
            oUsers += '<tr>';
            oUsers += '<td class="who_id">'+user.id+' '+wopen+'</td>';
            oUsers += '<td class="who_name">'+user.name+'</td>';
            oUsers += '<td class="who_grp"></td>';
            oUsers += '<td class="who_locat"></td>';
            oUsers += '<td class="who_onlineloc">'+user.online_location+'</td>';
            oUsers += '<td id="ostatus_'+user.online_id+'" class="'+invStClass+'">'
                +thropen+ user.inv_text+'</td>';
            oUsers += '<td class="who_ip">'+user.ip+'</td>';
            oUsers += '</tr><tr>';
            oUsers += '<td></td>';
            oUsers += '<td colspan="3" class="who_bborder"><div class="who_otitle"><i>'+localized.page+':</i> '+online_title+'</div></td>';
            oUsers += '<td colspan="2" class="who_bborder"><div class="who_uri"><i>uri:</i> '+online_url+'</div>';
            if (user.online_breadcrumb != '') oUsers += '<i>'+localized.path+':</i> ' + user.online_breadcrumb;
            oUsers += '</td>';
            oUsers += '<td class="who_bborder">'+user.online_user_agent+'</td>';
            oUsers += '</tr>';
        });

        jQuery('#whoonline').html(oUsers)
    },

    inviteUser: function(onlineId){
        var me = this;

        jQuery('#invite_dialog').dialog({
                title: localized.invite,
                buttons: [
                    {text: localized.submit,
                        click: function() {
                            var msg = jQuery('#inv_message').val();
                            jQuery(this).dialog("close");
                            // Отправить приглашение
                            jQuery('#ostatus_'+onlineId).html('<img src="'+webimRoot+'/tpl/img/ajax-loader.gif" border="0" />');
                            jQuery('#ostatus_'+onlineId).removeClass('who_rejected who_accepted who_sended');
                            jQuery.post(me._options.url, { act: "invite_visitor", message: msg,
                                    ouid: onlineId, x:me._options.x || 0 },
                              function(data) {
                                if (data){
                                    if (data.error){
                                        jQuery('#ostatus_'+onlineId).html(data.error);
                                    }else if(data.msg){
                                        jQuery('#ostatus_'+onlineId).html(data.msg);
                                        jQuery('#ostatus_'+onlineId).addClass('who_sended');
                                    }else{
                                        jQuery('#ostatus_'+onlineId).html('');
                                    }
                                }else{
                                    jQuery('#ostatus_'+onlineId).html('');
                                }
                            }, 'json');
                        }},
                    {text: localized.cancel,
                        click: function() { jQuery(this).dialog("close"); }}
                ],
                width: 'auto',
                show: "blind",
                hide: "blind"
            }
        );

    },

    updateContent: function(data) {
        if(data.error != ''){
            this.setStatus(data.error);
        }else{
            this.updateThreads(data.threads);
            this.updateOperators(data.operators);
            this.updateWhoOnline(data.whoonline);
        }
//        var el = jQuery(root).children().first().get(0);
//        if( el.tagName == 'update' ) {
//            for( var i = 0; i < el.childNodes.length; i++ ) {
//                var node = el.childNodes[i];
//                if (node.tagName == 'threads') {
//                    this.updateThreads(node);
//                }else if (node.tagName == 'operators') {
//                    this.updateOperators(node);
//                }else if (node.tagName == 'whoonline') {    // by Alex
//                    this.updateWhoOnline(node);
//                }
//            }
//        } else if( el.tagName == 'error' ) {
//
//            this.setStatus(jQuery(el).find("descr").text() );
//        } else {
//            this.setStatus( "reconnecting" );
//        }
    }
}

var webimRoot = "";
var tUpdater = '';
jQuery(document).ready(function(){
    webimRoot = updaterOptions.wroot;
    tUpdater = new ThreadUpdater(updaterOptions);

});