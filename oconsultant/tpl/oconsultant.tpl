<!-- BEGIN: MAIN -->
<div id="breadcrumbs">{BREAD_CRUMBS}</div>

<h2 class="tags">{PAGE_TITLE}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<!-- BEGIN: MENU -->
<table id="dashboard">
    <tr>
        <td class="padding10" style="width:33%">
            <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/dash/visitors.gif" alt=""
                 style="vertical-align: middle;"/>
            <a href="{PHP|cot_url('oconsultant', 'a=users')}">{PHP.L.oc.topMenu_users}</a><br/>
            {PHP.L.oc.page_client_pending_users}
        </td>

        <td class="padding10" style="width:33%">
            <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/dash/history.gif" alt=""
                 style="vertical-align: middle;"/>
            <a href='{PHP|cot_url('oconsultant', 'a=history')}'>{PHP.L.oc.page_analysis_search_title}</a><br/>
            {PHP.L.oc.content_history}
        </td>
        <td class="padding10" style="width:33%">
            <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/dash/stat.gif" alt="" style="vertical-align: middle;"/>
            <a href='{PHP|cot_url('oconsultant', 'a=statistic')}'>{PHP.L.Statistics}</a><br/>
            {PHP.L.oc.statistics_description}</td>
    </tr>
    <tr>
        <td class="padding10">
            <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/dash/canned.gif" alt=""
                 style="vertical-align: middle;"/>
            <a href='{PHP|cot_url('oconsultant', 'a=canned')}'>{PHP.L.oc.canned_title}</a><br/>
            {PHP.L.oc.canned_descr}
        </td>
    </tr>

</table>
<!-- END: MENU -->

<!-- BEGIN: PENDING_USERS -->
<p>
    {PHP.L.oc.clients_intro}<br/>{PHP.L.oc.clients_how_to}
</p>
<div id="connstatus" style="margin: 10px 10px; color: red; text-align: right"></div>

<table id="threadlist" class="cells width100" border="0">
    <tr>
        <td class="coltop first">{PHP.L.oc.pending_table_head_name}</td>
        <td class="coltop">{PHP.L.oc.page_analysis_search_head_host}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_state}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_operator}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_total}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_waittime}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_etc}</td>
    </tr>
    <tr id="tprio" style="display: none">
        <td colspan="7"></td>
    </tr>
    <tr id="tprioend" style="display: none">
        <td colspan="7"></td>
    </tr>

    <tr id="twait" style="display: none">
        <td colspan="7"></td>
    </tr>
    <tr id="twaitend" style="display: none">
        <td colspan="7"></td>
    </tr>

    <tr id="tchat" style="display: none">
        <td colspan="7"></td>
    </tr>
    <tr id="tchatend" style="display: none">
        <td colspan="7"></td>
    </tr>

    <tr>
        <td id="statustd" colspan="7" height="30">Loading....</td>
    </tr>
</table>


<div>
    <h2>{PHP.L.oc.whoonline}</h2>
    <table id="whoonline" style="width: 100%">
        <tr></tr>
    </table>
</div>

<!-- IF {PHP.cfg.oconsultant.showonlineoperators} == 1 -->
<div id="onlineoperators"></div>
<!-- ENDIF -->

<div id="invite_dialog" style="display: none;">
    <!--<input type="text" name="inv_message" id="inv_message" value="{PHP.L.oc.invite_message}" />-->
    <textarea name="inv_message" id="inv_message"
              style="width:94%">{PHP.L.oc.invite_message|strip_tags($this))}</textarea><br/>
    {PREDEFINEDANSWERS}
</div>

<!-- END: PENDING_USERS -->

<!-- BEGIN: THREAD_SERCH -->
<p>{PHP.L.oc.page_search_intro}</p>
<form name="searchForm" method="get" action="{PHP|cot_url('oconsultant')}" class="form-inline">
    <input type="hidden" name="e" value="oconsultant"/>
    <input type="hidden" name="m" value="operator"/>
    <input type="hidden" name="a" value="history"/>
    <table class="cells">
        <tr>
            <td>
                <div class="flabel">{PHP.L.oc.page_analysis_full_text_search}:</div>
                <div class="fvaluenodesc">
                    <input type="text" name="q" size="80" value="{QUERY}" class="formauth"/>
                    <button type="submit" class="btn"><i class="icon-search"></i> {PHP.L.Search}</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<!-- BEGIN: SEARCH_RES -->
<div class="margintop10"></div>
<table class="table table-striped table-hover cells">
    <tr class="header">
        <td class="coltop">{PHP.L.oc.pending_table_head_name}</td>
        <td class="coltop">{PHP.L.oc.page_analysis_search_head_host}</td>
        <td class="coltop">{PHP.L.oc.pending_table_head_operator}</td>
        <td class="coltop">{PHP.L.oc.page_analysis_search_head_messages}</td>
        <td class="coltop">{PHP.L.oc.page_analysis_search_head_time}</td>
    </tr>
    <!-- BEGIN: ROW -->
    <tr>
        <td>
            <a href="{ROW_WIEW_THREAD_URL}" target="_blank"
               onclick="this.newWindow = window.open('{ROW_WIEW_THREAD_URL}', '', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,width=720,height=520,resizable=1');this.newWindow.focus();this.newWindow.opener=window;return false;"
               title="{PHP.L.oc.thread_chat_log}">{ROW_USERNAME}</a>
            <!-- IF {ROW_USERPROFILE_URL} -->
            <br/>( <a href="{ROW_USERPROFILE_URL}" target="_blank">{PHP.L.oc.profile}</a>
            <!-- ENDIF -->
            <!-- IF {ROW_USERPROFILE_URL} !='' AND {ROW_USERNAME} != {ROW_USER_REAL_NAME} -->
            <i>{ROW_USER_REAL_NAME}</i>
            <!-- ENDIF -->
            <!-- IF {ROW_USERPROFILE_URL} -->
            )
            <!-- ENDIF -->
        </td>
        <td class="centerall">{ROW_ADDR}</td>
        <td class="centerall">{ROW_OPERATOR}</td>
        <td class="centerall">{ROW_MSG_COUNT}</td>
        <td class="centerall">{ROW_TIME_IN_CHAT}</td>
    </tr>
    <!-- END: ROW -->

    <!-- BEGIN: NOT_FOUND -->
    <tr>
        <td colspan="5" style="padding: 10px; text-align: center">{PHP.L.Noitemsfound}</td>
    </tr>
    <!-- END: NOT_FOUND -->
</table>
<!-- END: SEARCH_RES -->

<!-- BEGIN: PAGINATION -->
<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
    <span>{PHP.L.Page} {LIST_CURRENTPAGE} {PHP.L.Of} {LIST_TOTALPAGES}</span>
    {LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}
</div>
<!-- ENDIF -->

<!-- END: PAGINATION -->

<!-- END: THREAD_SERCH -->


<!-- BEGIN: STATISTICS -->
<p>{PHP.L.oc.statistics_description}</p>
<form name="searchForm" method="get" action="{PHP|cot_url('oconsultant')}" class="form-inline">
    <input type="hidden" name="e" value="oconsultant"/>
    <input type="hidden" name="m" value="operator"/>
    <input type="hidden" name="a" value="statistic"/>
    <table class="cells">
        <tr>
            <td>
                <div class="flabel">{PHP.L.oc.statistics_dates}:</div>
                <div class="">
                    {PHP.L.oc.statistics_from}: {FORM_START_DATE} &nbsp;&nbsp;&nbsp;
                    {PHP.L.oc.statistics_till}: {FORM_END_DATE}
                    <button type="submit" class="btn"><i class="icon-search"></i> {PHP.L.Search}</button>
                </div>
            </td>
        </tr>
    </table>
</form>

<!-- BEGIN: RESULT -->
<!-- BEGIN: DAY -->
<h2>{PHP.L.oc.report_bydate_title}</h2>
<div class="margintop10"></div>
<table class="table table-striped table-hover cells">
    <tr class="header">
        <td class="coltop">{PHP.L.Date}</td>
        <td class="coltop">{PHP.L.oc.report_bydate_2}</td>
        <td class="coltop">{PHP.L.oc.report_bydate_3}</td>
        <td class="coltop">{PHP.L.oc.report_bydate_4}</td>
    </tr>
    <!-- BEGIN: ROW -->
    <tr>
        <td>{DATE_DAY}</td>
        <td class="centerall">{DATE_TREADS}</td>
        <td class="centerall">{DATE_AGENTS}</td>
        <td class="centerall">{DATE_USERS}</td>
    </tr>
    <!-- END: ROW -->
    <!-- BEGIN: TOTAL -->
    <tr>
        <td><b>{PHP.L.Total}</b></td>
        <td class="centerall">{DATET_TREADS}</td>
        <td class="centerall">{DATET_AGENTS}</td>
        <td class="centerall">{DATET_USERS}</td>
    </tr>
    <!-- END: TOTAL -->
    <!-- BEGIN: NOTFOUND -->
    <tr>
        <td colspan="4" style="padding: 10px; text-align: center">{PHP.L.Noitemsfound}</td>
    </tr>
    <!-- END: NOTFOUND -->
</table>
<!-- END: DAY -->

<!-- BEGIN: OPERATOR -->
<h2>{PHP.L.oc.report_byoperator_title}</h2>
<div class="margintop10"></div>
<table class="table table-striped table-hover cells">
    <tr class="header">
        <td class="coltop">{PHP.L.oc.report_byoperator_1}</td>
        <td class="coltop">{PHP.L.oc.report_byoperator_2}</td>
        <td class="coltop">{PHP.L.oc.report_byoperator_3}</td>
        <td class="coltop">{PHP.L.oc.report_byoperator_4}</td>
    </tr>
    <!-- BEGIN: ROW -->
    <tr>
        <td>{OPR_PROF_URL}</td>
        <td class="centerall">{OPR_TREADS}</td>
        <td class="centerall">{OPR_MSGS}</td>
        <td class="centerall">{OPR_AVGLEN}</td>
    </tr>
    <!-- END: ROW -->
    <!-- BEGIN: NOTFOUND -->
    <tr>
        <td colspan="4" style="padding: 10px; text-align: center">{PHP.L.Noitemsfound}</td>
    </tr>
    <!-- END: NOTFOUND -->
</table>
<!-- END: OPERATOR -->

<!-- END: RESULT -->

<!-- END: STATISTICS -->

<!-- BEGIN: CANNED -->
<p>{PHP.L.oc.canned_descr}</p>
<form name="cannedForm" method="get" action="{PHP|cot_url('oconsultant')}">
    <input type="hidden" name="e" value="oconsultant"/>
    <input type="hidden" name="m" value="operator"/>
    <input type="hidden" name="a" value="canned"/>

    <table class="cells">
        <tr>
            <td>{PHP.L.oc.canned_locale}: {FORM_LOCALE}</td>
            <!-- IF {SHOWGROUPS} == 1 -->
            <td>{PHP.L.oc.canned_group}: {FORM_GROUP}</td>
            <!-- ENDIF -->
        </tr>
    </table>
</form>

<div class="textright paddingtop10">
    <img src="{PHP.cfg.modules_dir}/oconsultant/tpl/img/buttons/createban.gif" border="0" alt=""
         style="vertical-align: middle;"/>
    <a href="{EDIT_URL}" target="_blank"
       onclick="this.newWindow = window.open('{EDIT_URL}', '', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,width=640,height=480,resizable=1');this.newWindow.focus();this.newWindow.opener=window;return false;">{PHP.L.oc.canned_add}</a>
</div>

<div class="margintop10"></div>
<table class="cells">
    <tr class="header">
        <td class="coltop">{PHP.L.Message}</td>
        <td class="coltop">{PHP.L.oc.canned_actions}</td>
    </tr>
    <!-- BEGIN: ROW -->
    <tr>
        <td>{ROW_MESSAGE}</td>
        <td>
            <a href="{ROW_MSG_EDIT_URL}" target="_blank"
               onclick="this.newWindow = window.open('{ROW_MSG_EDIT_URL}', '', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,width=640,height=480,resizable=1');this.newWindow.focus();this.newWindow.opener=window;return false;">
                {PHP.L.Edit}</a>,
            <a href="{ROW_MSG_DELETE_URL}" class="confirmLink">{PHP.L.Delete}</a>
        </td>
    </tr>
    <!-- END: ROW -->
    <!-- BEGIN: NOTFOUND -->
    <tr>
        <td colspan="4" style="padding: 10px; text-align: center">{PHP.L.None}</td>
    </tr>
    <!-- END: NOTFOUND -->
</table>
<!-- END: CANNED -->

<!-- END: MAIN -->