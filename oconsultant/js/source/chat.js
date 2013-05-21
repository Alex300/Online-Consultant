/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 */
var FrameUtils = {
    getDocument: function(frm) {
        if (frm.contentDocument) {
            return frm.contentDocument;
        } else if (frm.contentWindow) {
            return frm.contentWindow.document;
        } else if (frm.document) {
            return frm.document;
        } else {
            return null;
        }
    },

    initFrame: function(frm) {
        frm = frm.get(0);
        var doc = this.getDocument(frm);
        doc.open();
        doc.write("<html><head>");
        doc.write('<link rel="stylesheet" type="text/css" media="all" href="'+Chat.cssfile+'">');
        doc.write('</head><body bgcolor="#FFFFFF" text="#000000" link="#C28400" vlink="#C28400" alink="#C28400">');
        doc.write("<table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td valign='top' class='message' id='content'></td></tr></table><a id='bottom'></a>");
        doc.write("</body></html>");
        doc.close();
        frm.onload = function() {
            if( frm./**/myHtml ) {
                FrameUtils.getDocument(frm).getElementById('content').innerHTML += frm.myHtml;
                FrameUtils.scrollDown(frm);
            }
        };
    },

    insertIntoFrame: function(frm, htmlcontent) {
        frm = frm.get(0);
        var vcontent = this.getDocument(frm).getElementById('content');
        if( vcontent == null ) {
            if( !frm.myHtml ) frm.myHtml = "";
            frm.myHtml += htmlcontent;
        } else {
            vcontent.innerHTML += htmlcontent;
        }
    },

    scrollDown: function(frm) {
        frm = frm.get(0);
        var vbottom = this.getDocument(frm).getElementById('bottom');
        if( myAgent == 'opera' ) {
            try {
                frm.contentWindow.scrollTo(0,this.getDocument(frm).getElementById('content').clientHeight);
            } catch(e) {}
        }
        if( vbottom ) {
            vbottom.scrollIntoView(false);
        }
    }
};


/**
 * @constructor
 */
ChatThreadUpdater = function(options){
    this._options = jQuery.extend({}, options);
    this._options.timeout = 5000;
    this.updater = {};
    this.executing = false;
    this.frequency = (this._options.frequency || 2);
    this.lastupdate = 0;
    this.cansend = true;
    this.skipNextsound = true;
    this.focused = true;
    this.titleNotyfier = {
        defaultTitle : document.title,
        len : 0
    };
    this.ownThread = this._options.message.length > 0;
    FrameUtils.initFrame(this._options.container);

    me = this;

    if( this._options.message ) {
        this._options.message.keydown(function(event) {
            me.handleKeyDown(event);
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
    }
    this.update();
}
/**
 * @type {Object}
 */
ChatThreadUpdater.prototype = {
    /**
     * Ошибка при выполнении запроса
     * @param _request
     * @param ex
     */
    handleException: function(_request, ex) {
        this.setStatus("offline, reconnecting");
        this.stopUpdate();
        var me = this;
        me.executing = false;
        this.timer = setTimeout(function() { me.update() }, 1000);
    },

    handleTimeout: function(_request) {
        this.setStatus("timeout, reconnecting");
        this.stopUpdate();
        this.timer = setTimeout(this.update.bind(this), 1000);
    },

    update: function() {
        if (this.executing) return false;
        var me = this;
        this.executing = true;
        this.updateOptions("ajxRefresh");
        this.updater = jQuery.post(this._options.url, this._options.parameters, '', 'json')
            .done(function(data) { me.requestComplete(data) })
            .fail(function() { me.handleException(); });
    },

    updateOptions: function(act) {
        this._options.url = this._options.chatUrl + '&a=' + act;
        this._options.parameters = 'thread=' + (this._options.threadid || 0) +
            '&token=' + (this._options.token || 0)+
            '&lastid=' + (this._options.lastid || 0)+
            '&x=' + (this._options.x || 0);

        if( this._options.user ) this._options.parameters += "&user=true";

        // Пользователь печатает сообщение
        if( (act == 'ajxRefresh' ) && (this._options.message.length > 0) && (this._options.message.val() != '') ) {
            this._options.parameters += "&typed=1";
        }
    },

    enableInput: function(val) {
        if( this._options.message.length > 0 ){
            if(val){
                this._options.message.removeAttr('disabled');
            }else{
                this._options.message.attr('disabled', 'disabled');
            }
        }
    },

    stopUpdate: function() {
        this.enableInput(true);
        if( this.updater ) this.updater.done = undefined;
        clearTimeout(this.timer);
    },

    requestComplete: function(data) {
        this.executing = false;
        try {
            this.enableInput(true);
            this.cansend = true;
            if( data.error == '' ) {
                this.updateContent( data );
            } else {
                this.handleError(data, 'refresh messages failed');
            }
        } catch (e) {
            alert(e.message);
        }
        var me = this;
        this.skipNextsound = false;
        this.timer = setTimeout( function() { me.update() }, this.frequency * 1000);
    },

    postMessage: function(msg) {
        if( msg == "" || !this.cansend) return false;

        this.cansend = false;
        this.stopUpdate();
        this.skipNextsound = true;
        this.updateOptions("ajxPost");
        var postOptions = this._options.parameters;
        postOptions += "&message=" + encodeURIComponent(msg);
        if( myRealAgent != 'opera' ) this.enableInput(false);

        var me = this;
        this.updater = jQuery.post(this._options.url, postOptions, '', 'json')
            .done(function(data) {
                me.requestComplete(data);
                if( me._options.message.length > 0 ) {
                    me._options.message.val('');
                    me._options.message.focus();
                }
             })
            .fail(function() { me.handleException(); });

        return false;
    },

    changeName: function(newname) {
        this.skipNextsound = true;
        var url = this._options.chatUrl + '&a=ajxRename';
        var params = 'thread=' + (this._options.threadid || 0) + '&token=' + (this._options.token || 0) + '&x=' + (this._options.x || 0);
        params += "&name=" + encodeURIComponent(newname);

        var me = this;
        jQuery.post(url, params, '', 'json')
            .done(function(data) {
                if(data.error != '') me.handleError(data);
            })
            .fail(function() { me.handleException(); });
    },

    onThreadClosed: function(data) {
        if( data.closed == 'closed' ) {
            setTimeout('window.close()', 1500);
        } else {
            this.handleError(data, 'cannot close');
        }
    },

    closeThread: function() {
        var url = this._options.chatUrl + '&a=close';
        var params = 'thread=' + (this._options.threadid || 0) + '&token=' + (this._options.token || 0) + '&x=' + (this._options.x || 0);
        if( this._options.user )  params += "&user=true";

        var me = this;
        jQuery.post(url, params, function(data){
            me.onThreadClosed(data);
        }, 'json');
    },

    showTyping: function(istyping) {
        if( jQuery("#typingdiv").length > 0 ) {
            var disp = istyping ? 'inline' : 'none';
            jQuery("#typingdiv").css('display', disp);
        }
    },

    setupAvatar: function(avatar) {
        avatar = avatar || '';
        if( this._options.avatar ) {
            if(avatar != 'no' && avatar != ''){
                this._options.avatar.html('<img src="'+avatar+'" border="0" class="thumbnail" alt=""/>');
            }else if(avatar == 'no'){
                this._options.avatar.html(' <img src="'+ Chat.webimRoot +'/tpl/img/operator.png" />');
            }
        }
    },

    /**
     * Обновить содержимое чата
     * @param data
     */
    updateContent: function(data) {
        var haveMessage = false;

        var result_div = this._options.container;
        var _lastid = data.thread.lastid;
        if( _lastid ) {
            this._options.lastid = _lastid;
        }

        var typing = data.thread.typing;
        if( typing ) {
            this.showTyping(typing == '1');
        }

        var canpost = data.thread.canpost;
        if( canpost ) {
            if( canpost == '1' && !this.ownThread || this.ownThread && canpost != '1' ) {
                window.location.href = window.location.href;
            }
        }

        var me = this;
        var avatarSetted = false;
        if(data.avatar != ''){
            me.setupAvatar(data.avatar);
            avatarSetted = true;
        }

        jQuery.each(data.messages, function(index, message) {
            if( message.type == 'message' ) {
                haveMessage = true;
                FrameUtils.insertIntoFrame(result_div, message.text );

            } else if( message.type == 'avatar' && !avatarSetted ) {
                me.setupAvatar(message.text);
            } else if( message.type == 'redirect' ) {
                if (me._options.user){
                    var newUrl = message.text;
                    window.opener.location.href = newUrl;
                    // отметить на сервере, что переход выполнен
                    me.updateOptions("ajxRedirectToUrlDone");
                    me.updater = jQuery.post(me._options.url, me._options.parameters, '', 'json')
                        .done(function(data) {
                            if(data.error != ''){ me.handleError(data) }
                        })
                        .fail(function() { me.handleException(); });
                }
            }
        });
        if(window.location.search.indexOf('trace=on')>=0) {
            var val = "updated";
            if(this.lastupdate > 0) {
                var seconds = ((new Date()).getTime() - this.lastupdate)/1000;
                val = val + ", " + seconds + " secs";
//                if(seconds > 10) {
//                    alert(val);
//                }
            }
            this.lastupdate = (new Date()).getTime();
            this.setStatus(val);
        } else {
            this.clearStatus();
        }

        if( haveMessage ) {
            FrameUtils.scrollDown(this._options.container);
            if(!this.skipNextsound) {
                var tsound = $('#togglesound');
                if(tsound.length == 0 || tsound.hasClass('isound') ) {
                    playSound(Chat.webimRoot+'/sounds/new_message.wav');
                }
                if( !this.focused ) {
                    this.notifyInTitle();
                }
            }
            if( !this.focused ) {
                window.focus();
            }
        }
    },

    isSendkey: function(ctrlpressed, key) {
        return ((key==13 && (ctrlpressed || this._options.ignorectrl)) || (key==10));
    },

    /**
     * Обработчик нажатия клавиши
     * @param k
     * @returns {boolean}
     */
    handleKeyDown: function(k) {
        if( k ){ ctrl=k.ctrlKey;k=k.which; } else { k=event.keyCode;ctrl=event.ctrlKey;	}
        if( this._options.message && this.isSendkey(ctrl, k) ) {
            var mmsg = this._options.message.val();
            if( this._options.ignorectrl ) {
                mmsg = mmsg.replace(/[\r\n]+$/,'');
            }
            this.postMessage( mmsg );
            return false;
        }
        return true;
    },

    handleError: function(data, action) {
        if( data.error != '' ) {
            this.setStatus(data.error);
        } else {
            this.setStatus(action);
        }
    },

    showStatusDiv: function(k) {
        if( jQuery("#engineinfo").length > 0 ) {
            jQuery("#engineinfo").css('display', 'inline');
            jQuery("#engineinfo").html(k);
        }
    },

    setStatus: function(k) {
        if( this.statusTimeout ) clearTimeout(this.statusTimeout);
        this.showStatusDiv(k);
        var me = this;
        this.statusTimeout = setTimeout(function() { me.clearStatus() }, 4000);
    },

    clearStatus: function() {
        jQuery("#engineinfo").hide();
    },

    // Redirect visitor to new URL
    redirecttourl: function(url){
        this.cansend = false;
        this.stopUpdate();
        this.skipNextsound = true;
        this.updateOptions("ajxRedirecttourl");

        var postOptions = this._options.parameters;
        postOptions += "&url=" + encodeURIComponent(url);
        var me = this;
        this.updater = jQuery.post(me._options.url, postOptions, '', 'json')
            .done(function(data) {
                me.requestComplete(data);
                if( me._options.message.length > 0 ) {
                    me._options.message.focus();
                }
            })
            .fail(function() { me.handleException(); });
    },

    notifyInTitle: function(){
        if( this.focused ) {
            document.title = this.titleNotyfier.defaultTitle;
            return false;
        }
        //var tit = "** Новое сообщение **";
        var tit = '*** '+this._options.localized.new_msg+' ***';
        var maxL = tit.length;

        document.title = tit.substring(0, this.titleNotyfier.len);
        if(this.titleNotyfier.len >= maxL) {
            this.titleNotyfier.len = 1;
            setTimeout("Chat.threadUpdater.notifyInTitle()", 3000);
        } else {
            this.titleNotyfier.len++;
//        if (this.titleNotyfier.len / 2 == (this.titleNotyfier.len / 2).toFixed() ) {
//            document.title='';
//        }
            setTimeout("Chat.threadUpdater.notifyInTitle()", 200);
        }
    }
}

var Chat = {
    threadUpdater : {},

    applyName: function() {
        Chat.threadUpdater.changeName($('#uname').val());
        $('#changename1').css('display', 'none');
        $('#changename2').css('display', 'inline');
        $('#unamelink').html(htmlescape($('#uname').val() ));
    },

    showNameField: function() {
        $('#changename1').css('display', 'inline');
        $('#changename2').css('display', 'none');
    }
};

jQuery(document).ready(function(){
    Chat.webimRoot = threadParams.wroot;
    Chat.cssfile = threadParams.cssfile;
    Chat.threadUpdater = new ChatThreadUpdater(jQuery.extend({},{
            ignorectrl:-1,
            container: $("#chatwnd"),
            avatar: $("#interlocutor-avatar"),
            message: $("#msgwnd")
        }, (threadParams || {}) ));

    $('.tooltipC').tooltip({
        placement: 'bottom'
    });

    // === Behaviour register ===
    /**
     * Send Message button
     */
    jQuery('.postmessage').click(function(event){
        event.preventDefault();
        var message = $('#msgwnd').val();
        if( message ) Chat.threadUpdater.postMessage(message);
        return false;
    });

    /**
     * Select predefined answer
     * todo test it
     */
    jQuery('select#predefined').change(function(){
        var message = $('#msgwnd');
        if(this.selectedIndex!=0) {
            message.val(this.options[this.selectedIndex].innerText || this.options[this.selectedIndex].innerHTML);
            // или так:
//            message.val($(this).val());
        }
        this.selectedIndex = 0;
        message.focus();
    });

    jQuery('div#changename2 a').click(function(event){
        event.preventDefault();
        Chat.showNameField();
        return false;
    });

    jQuery('div#changename2 .changeName').click(function(event){
        event.preventDefault();
        Chat.showNameField();
        return false;
    });

    /**
     * Apply User new name
     */
    jQuery('div#changename1 .applyName').click(function(event){
        event.preventDefault();
        Chat.applyName();
        return false;
    });

    jQuery('div#changename1 input#uname').keydown(function(e){
        var ev = e || event;
        if( ev.keyCode == 13 ) {
            Chat.applyName();
        }
    });

    /**
     * Refresh chat
     */
    jQuery('#refresh').click(function(event){
        event.preventDefault();
        Chat.threadUpdater.stopUpdate();
        Chat.threadUpdater.update();
    });

    jQuery('#togglesound').click(function(event){
        event.preventDefault();
        jQuery(this).tooltip('hide');
        if(jQuery(this).hasClass('isound')){
            jQuery(this).removeClass('isound').addClass('inosound').addClass('btn-danger');
            jQuery(this).attr('data-original-title', 'Turn on sound');
            jQuery(this).children('i').removeClass('icon-volume-off').addClass('icon-volume-up');
        }else{
            jQuery(this).removeClass('inosound').addClass('isound').removeClass('btn-danger');
            jQuery(this).attr('data-original-title', 'Turn off sound');
            jQuery(this).children('i').removeClass('icon-volume-up').addClass('icon-volume-off');
        }
        if( jQuery('#msgwnd').length > 0){
            jQuery('#msgwnd').focus();
        }
        return false;
    });

    /**
     * Close thread button
     */
    jQuery('.closethread').click(function(){
        Chat.threadUpdater.closeThread();
        return false;
    });

    /**
     * Redirect to Url button
     */
    jQuery('#redirecttourl').click(function(event){
        event.preventDefault();
        var redirurl = jQuery('#redirurl').val();
        if( redirurl ){
            Chat.threadUpdater.redirecttourl(redirurl);
        }
    });

    /**
     * Send History to email
     */
    jQuery('#mailThread').click(function(event){
        event.preventDefault();
        var me = jQuery(this);
        var url = me.attr('href');
        me.newWindow = window.open(url, 'ForwardMail', 'toolbar=0,scrollbars=0,location=0,statusbar=1,menubar=0,width=603,height=254,resizable=0');
        if (me.newWindow != null) {
            me.newWindow.focus();
            me.newWindow.opener=window;
        }
        return false;
    });
    // === /Behaviour register ===
});