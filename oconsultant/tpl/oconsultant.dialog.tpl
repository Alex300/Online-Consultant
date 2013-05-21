<!-- BEGIN: MAIN -->
<!DOCTYPE html>
<html lang="{PHP.usr.lang}">
<head>
    <title>{HEADER_TITLE}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta name="generator" content="Portal30 http://portal30.ru"/>
    {PHP.R.code_basehref}
    <meta name="robots" content="noindex, nofollow"/>
    {HEADER_HEAD}
    <link rel="shortcut icon" href="favicon.ico"/>
    <link rel="apple-touch-icon" href="apple-touch-icon.png"/>

    <link rel="stylesheet" type="text/css"
          href="{PHP.cfg.modules_dir}/oconsultant/tpl/css/bootstrap.min.css?{OC_VERSION}"/>
    <link rel="stylesheet" type="text/css" href="{PHP.cfg.modules_dir}/oconsultant/tpl/css/chat.css?{OC_VERSION}"/>
    <script type="text/javascript" language="javascript" src="js/jquery.min.js?{OC_VERSION}"></script>
    <script type="text/javascript" language="javascript"
            src="{PHP.cfg.modules_dir}/oconsultant/js/bootstrap.2.3.1.min.js?{OC_VERSION}"></script>
</head>

<body>
<div class="container-fluid">

<div id="dlg_header" class="row-fluid">
    <div class="pull-right">
        <a class="btn btn-small tooltipC" href="javascript:window.close();"
           title="{PHP.L.Close}"><i class="icon-remove"></i></a>
    </div>
    <h1>
        <!-- IF {GROUPNAME} -->
        {GROUPNAME}
        <!-- ENDIF -->
        <!--{PHP.L.oc.leavemessage_title}-->
        {TITLE}
    </h1>
    {SUBTITLE}
</div>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<!-- BEGIN: MAIL -->
<div class="block">
    <form name="messageForm" method="post" action="{FORM_ACTION}">
        <input type="hidden" name="act" value="mail"/>
        <input type="hidden" name="thread" value="{CHATTHREADID}"/>
        <input type="hidden" name="token" value="{TOKEN}"/>
        {PHP.L.oc.mailthread_enter_email}:
        <input type="text" name="email" value="{FORM_EMAIL}" class="text" style=""/>
    </form>
</div>
<!-- END: MAIL -->

<!-- BEGIN: MAIL_SENT -->
{SENT_MESSAGE}
<!-- END: MAIL_SENT -->

<!-- BEGIN: LEAVE_MESSAGE -->
{PHP.L.oc.leavemessage_descr}
<form name="messageForm" method="post" class="form-inline" action="{PHP|cot_url('oconsultant', 'm=client&a=leavemsg')}">
    <input type="hidden" name="info" value="{INFO}"/>
    <input type="hidden" name="referrer" value="{REFERRER}"/>
    <!-- IF {GROUPID} -->
    <input type="hidden" name="group" value="{GROUPID}"/>
    <!-- ENDIF -->
    <table cellspacing="0" cellpadding="0" border="0" class="table margintop10">
        <tr>
            <td style="width: 105px">{PHP.L.oc.form_field_email} *:</td>
            <td>
                <input type="text" name="email" value="{USER_MAIL}" class="text"/>
            </td>
        </tr>
        <tr>
            <td>{PHP.L.oc.form_field_name} *:</td>
            <td><input type="text" name="name" value="{USER_NAME}" class="text"/></td>
        </tr>
        <tr>
            <td>{PHP.L.Message} *: &nbsp;</td>
            <td>
                <textarea name="message" style="width: 100%; height: 95px;" tabindex="0">{MESSAGE}</textarea>
            </td>
        </tr>
        <!-- BEGIN: CAPTCHA -->
        <tr>
            <td class="text">{PHP.L.oc.captcha}</td>
            <td>
                <table>
                    <tr>
                        <td style="border:none; vertical-align: middle">{VERIFYIMG}</td>
                        <td style="border:none; vertical-align: middle">{VERIFYINPUT}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- END: CAPTCHA -->
    </table>
</form>
<!-- END: LEAVE_MESSAGE -->

<!-- BEGIN: LEAVE_MESSAGESEND -->
{PHP.L.oc.leavemessage_sent_message}
<!-- END: LEAVE_MESSAGESEND -->

<!-- BEGIN: SURVEY -->
{PHP.L.oc.presurvey_intro}
<form name="messageForm" method="post" action="{PHP|cot_url('oconsultant','m=chat&a=open')}">
    <input type="hidden" name="info" value="{INFO}"/>
    <input type="hidden" name="referrer" value="{REFERRER}"/>
    <input type="hidden" name="survey" value="on"/>
    <!-- IF {SHOWEMAIL} == 0 -->
    <input type="hidden" name="email" value="{FORMEMAIL}"/>
    <!-- ENDIF -->
    <!-- IF !{GROUPS} AND {FORMGROUPID} > 0 -->
    <input type="hidden" name="group" value="{FORMGROUPID}"/>
    <!-- ENDIF -->
    <!-- IF {SHOWMESSAGE} == 0 -->
    <input type="hidden" name="message" value="{FORMMESSAGE}"/>
    <!-- ENDIF -->
    <table class="table margintop10">

        <!-- IF {GROUPS} -->
        <tr>
            <td>{PHP.L.presurvey.department}</td>
            <td>
                <select name="group" style="min-width:200px;">${page:groups}</select>
            </td>
        </tr>
        <!-- ENDIF -->

        <tr>
            <td>{PHP.L.oc.form_field_name}:</td>
            <td>
                <input type="text" name="name" value="{FORMNAME}" class="text"
                <!-- IF {SHOWNAME} == 0 -->disabled="disabled"<!-- ENDIF --> />
            </td>
        </tr>

        <!-- IF {SHOWEMAIL} == 1 -->
        <tr>
            <td>{PHP.L.oc.form_field_email}:</td>
            <td><input type="text" name="email" value="{FORMEMAIL}" class="text"/></td>
        </tr>
        <!-- ENDIF -->

        <!-- IF {SHOWMESSAGE} == 1 -->
        <tr>
            <td>{PHP.L.oc.presurvey_question}:</td>
            <td>
                <textarea name="message" class="field" tabindex="0" cols="45" rows="3"
                          style="width: 100%">{FORMMESSAGE}</textarea>
            </td>
        </tr>
        <!-- ENDIF -->

    </table>
</form>
<!-- END: SURVEY -->

<!-- BEGIN: CANNED -->
<!-- BEGIN: SAVED -->
{PHP.L.oc.cannededit_done}
<script><!--
    if (window.opener && window.opener.location) {
        window.opener.location.reload();
    }
    setTimeout((function () {
        window.close();
    }), 500);
    //-->
</script>
<!-- END: SAVED -->

<!-- BEGIN: FORM -->
<form name="messageForm" method="post" action="{FORM_ACTION}">
    {PHP.L.Message}: *<br/>
    <textarea class="wide" rows="5" style="width:95%" name="message">{FORM_MESSAGE}</textarea>
</form>
<!-- END: FORM -->
<!-- END: CANNED -->

<!-- BEGIN: THREAD_LOG -->
<table class="table margintop10 table-condensed" style="width: auto">
    <tr>
        <td>{PHP.L.Name}:</td>
        <td>
            {USER_NAME}
            <!-- IF {USER_PROFILE_URL} -->
            ( <a href="{USER_PROFILE_URL}" target="_blank">{PHP.L.oc.profile}</a>
            <!-- ENDIF -->
            <!-- IF {USER_PROFILE_URL}!='' AND {USERNAME}!={USER_REAL_NAME} -->
            <i>{ROW_USER_REAL_NAME}</i>
            <!-- ENDIF -->
            <!-- IF USER_PROFILE_URL} -->
            )
            <!-- ENDIF -->
        </td>
    </tr>
    <tr>
        <td>{PHP.L.oc.page_analysis_search_head_host}:</td>
        <td>{USER_HOST}</td>
    </tr>
    <tr>
        <td>{PHP.L.oc.page_analysis_search_head_browser}:</td>
        <td>{USER_AGENT}</td>
    </tr>
    <!-- IF {GROUP_NAME} -->
    <tr>
        <td>{PHP.L.Group}:</td>
        <td>{GROUP_NAME}</td>
    </tr>
    <!-- ENDIF -->
    <!-- IF {AGENT_NAME} -->
    <tr>
        <td>{PHP.L.oc.pending_table_head_operator}:</td>
        <td>{AGENT_NAME} ( <a href="{AGENT_PROFILE_URL}" target="_blank">{PHP.L.oc.profile}</a> )</td>
    </tr>
    <!-- ENDIF -->
    <tr>
        <td>{PHP.L.oc.page_analysis_search_head_time}:</td>
        <td>{TIME_IN_CHAT}</td>
    </tr>
</table>
<div class="message" style="background: #fff; border: #3E606F 1px solid;">
    <!-- BEGIN: ROW -->
    {ROW_MESSAGE}
    <!-- END: ROW -->
</div>
<!-- END: THREAD_LOG -->


<!-- BEGIN: CONFIRM_TAKE -->
<div class="warning">{CONFIRM_TEXT}</div>
<!-- END: CONFIRM_TAKE -->


<!-- BEGIN: BUTTONS -->
<div class="margin10">
    <!-- BEGIN: BUTTON -->
    <div class="pull-left" style="margin-left: 10px">
        <a href="{BUTTON_LINK}" class="btn">{BUTTON_TEXT}</a>
    </div>
    <!-- END: BUTTON -->
    <div class="clearfix"></div>
</div>
<!-- END: BUTTONS -->


<!-- BEGIN: SUBMIT -->
<div class="row-fluid text-right" style="margin-top: 10px">
    <a href="javascript:document.messageForm.submit();" title="{PHP.L.Submit}"
       class="btn btn-primary btn-small postmessage"><i class="icon-comment icon-white"></i> {PHP.L.Submit}</a>
</div>
<!-- END: SUBMIT -->

<div class="row-fluid small grey text-center" style="margin-top: 12px">
  Powered by <a id="poweredByLink" href="http://portal30.ru" title="Portal30" target="_blank">portal30.ru</a>
</div>

</div>

</body>
</html>
<!-- END: MAIN -->