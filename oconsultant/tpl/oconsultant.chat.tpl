<!-- BEGIN: MAIN -->
<!DOCTYPE html>
<html lang="{PHP.usr.lang}">
<head>
    <title>{HEADER_TITLE}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="generator" content="Portal30 http://portal30.ru" />
    {PHP.R.code_basehref}
    <meta name="robots" content="noindex, nofollow" />
    {HEADER_HEAD}
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png" />
    <link rel="stylesheet" type="text/css" href="{PHP.cfg.modules_dir}/oconsultant/tpl/css/bootstrap.min.css?{OC_VERSION}" />
    <link rel="stylesheet" type="text/css" href="{PHP.cfg.modules_dir}/oconsultant/tpl/css/chat.css?{OC_VERSION}" />
    <script type="text/javascript" language="javascript" src="js/jquery.min.js?{OC_VERSION}"></script>
    <script type="text/javascript" language="javascript" src="{PHP.cfg.modules_dir}/oconsultant/js/bootstrap.2.3.1.min.js?{OC_VERSION}"></script>
</head>
<!-- Copyright (c) Portal30.Ru. All Rights Reserved. -->
<body>
<div class="container-fluid">
    <div id="oc_header" class="row-fluid">
        <div id="interlocutor-avatar" <!-- IF {AGENT} == 1 -->style="height: 76px;"<!--ENDIF -->>
            <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/operator.png" title="" />
        </div>
        <div id="header-right">
            <div class="pull-right">
                <a class="closethread btn btn-small tooltipC" href="#" title="{PHP.L.oc.chat_window_close_title}"><i class="icon-remove"></i></a>
            </div>
            <div id="main-title">
               <a onclick="window.open('{PHP.cfg.mainurl}');return false;" href="{PHP.cfg.mainurl}">{MAIN_TITLE}</a>
            </div>
            <div>
                {PHP.L.oc.chat_window_product_name}
            </div>
            <div id="panel" class="block">
                <!-- IF {AGENT} == 1 -->
                    {PHP.L.oc.chat_window_chatting_with}: <strong>{USER_MANE}</strong>

                    <!-- IF 0 == 1 AND {CANPOST} == 1 -->
                    <!-- Redirect visitor to another operator -->
                    <!-- History Params -->
                    <!-- ENDIF -->
                <!-- ENDIF -->

                <!-- IF {USER} == 1 -->
                    <!-- IF {CANCHANGENAME} == 1 -->
                    <div id="changename1" style="display:{DISPL1};">
                        {PHP.L.oc.chat_client_name}:
                        <div class="input-append">
                            <input id="uname" class="input-small" type="text"
                                   placeholder="{PHP.L.oc.form_field_name}" value="{USER_NAME}" />
                            <button class="btn applyName tooltipC" type="button"
                                    title="{PHP.L.oc.chat_client_changename}"><i class="icon-ok"></i></button>
                        </div>
                    </div>
                    <div id="changename2" style="display:{DISPL2};">
                        <a id="unamelink" href="#" class="tooltipC" title="{PHP.L.oc.chat_client_changename}">
                            {USER_NAME}</a>
                        <button class="btn changeName tooltipC" type="button"
                                title="{PHP.L.oc.chat_client_changename}"><i class="icon-edit"></i></button>
                    </div>
                    <!-- ELSE -->
                     {PHP.L.oc.chat_client_name}&nbsp;{USER_MANE}
                    <!-- ENDIF -->

                    <a href="{MAILLINK}" target="_blank" title="{PHP.L.oc.chat_window_toolbar_mail_history}" id="mailThread"
                        class="btn tooltipC"><i class="icon-envelope"></i></a>
                <!-- ENDIF -->

                <a id="togglesound" href="#" title="Turn off sound" class="btn isound tooltipC"><i class="icon-volume-off"></i></a>

                <a id="refresh" href="#" title="{PHP.L.oc.chat_window_toolbar_refresh}" class="btn tooltipC"><i class="icon-refresh"></i></a>

                <!-- IF {SSLLINK} -->
                    <a href="{SSLLINK}" title="SSL" class="btn tooltipC" ><i class="icon-briefcase"></i></a>
                <!-- ENDIF -->
            </div>
            <div style="clear: right; height: 1px"></div>
            <div id="status" class="">
                <div id="engineinfo" style="display:none;"></div>
                <div id="typingdiv" style="display:none;">{PHP.L.oc.typing_remote}</div>
            </div>

        </div>
    </div>
    <div class="clearfix"></div>


    <div id="chat-history" class="row-fluid">
        <table cellspacing="0" cellpadding="0"  style="width: 100%; border: none;">
            <tr>
                <td style="width:20px" class="text-right">
                   <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/{PHP.usr.lang}/history.gif" />
                </td>
                <td class="chat-cont">
                    <iframe id="chatwnd" width="100%" height="100%" src="<!-- IF {NEEDIFRAMESRC} -->{PHP.cfg.modules_dir}/oconsultant/tpl/blank.html<!-- ENDIF -->" frameborder="0" style="overflow:auto;">
                        Sorry, your browser does not support iframes; try a browser that supports W3 standards.
                    </iframe>
                </td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <!-- IF {CANPOST} -->
    <div class="row-fluid" style="margin-top: 5px">
        <table cellspacing="0" cellpadding="0" style="width: 100%; border: none">
            <tr>
                <td style="width:20px" class="text-right">
                    <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/{PHP.usr.lang}/message.gif" />
                </td>
                <td class="chat-cont" style="height: 78px">
                    <textarea id="msgwnd" class="message" tabindex="0"></textarea>
                </td>
                <td valign="middle" id="avatarwnd">
                    <!-- IF {MY_AVATAR} -->
                    <div>
                    <img src="{MY_AVATAR}" class="thumbnail" />
                    </div>
                    <!-- ENDIF -->
                </td>
            </tr>
        </table>
    </div>
    <!-- ENDIF -->


    <!-- IF {AGENT} == 1 AND {CANPOST} == 1 -->
    <div class="row-fluid" style="margin-top: 10px">
        <table cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td width="20"></td>
                <td>{PREDEFINEDANSWERS}</td>
                <td width="20"></td>
                <td>
                    <span class="grey">{PHP.L.oc.redirect_user_to_url}:</span>
                    <div class="input-append">
                        <input id="redirurl" type="text" class="field" name="new_url" value="" placeholder="{PHP.L.oc.redirect_user_to_url}" />
                        <button id="redirecttourl" class="btn" type="button" title="{PHP.L.oc.redirect_user_to_url}"><i class="icon-share-alt"></i></button>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- ENDIF -->

    <!-- IF {CANPOST} == 1 -->
    <div class="row-fluid text-right" style="margin-top: 10px">
        <a href="" class="btn btn-primary btn-small postmessage" id="sendmessage"
           title="{PHP.L.oc.chat_window_send_message}"><i class="icon-comment icon-white"></i> {SEND_BTN}</a>
    </div>
    <!-- ENDIF -->

    <div id="row-fluid" style="margin-top: 15px">
        <div class="small grey text-center">Powered by <a id="poweredByLink" href="http://portal30.ru" title="Portal30" target="_blank">portal30.ru</a></div>
    </div>

</div>

<script type="text/javascript" language="javascript"
        src="{PHP.cfg.modules_dir}/oconsultant/js/{PHP.oc_jsver}/common.js?{OC_VERSION}"></script>
<script type="text/javascript" language="javascript"
        src="{PHP.cfg.modules_dir}/oconsultant/js/{PHP.oc_jsver}/brws.js?{OC_VERSION}"></script>
<script type="text/javascript" language="javascript"><!--
    var threadParams = {
        chatUrl:"{SERVERLINK}",
        wroot:"{PHP.cfg.modules_dir}/oconsultant/",
        frequency: {PHP.cfg.oconsultant.updatefrequency_chat},
        <!-- IF {USER} == 1 -->user: "true", <!-- ENDIF -->
        threadid:{CT_CHATTHREADID},
        token:{CT_TOKEN},
        cssfile:"{PHP.cfg.modules_dir}/oconsultant/tpl/css/chat.css",
        ignorectrl:{IGNORECTRL},
        localized: {
            new_msg: '{PHP.L.oc.new_message}'
        },
        x: '{PHP.sys.xk}'
    };
    //-->
</script>
<script type="text/javascript" language="javascript"
        src="{PHP.cfg.modules_dir}/oconsultant/js/{PHP.oc_jsver}/chat.js?{OC_VERSION}"></script>
</body>
</html>
<!-- END: MAIN -->