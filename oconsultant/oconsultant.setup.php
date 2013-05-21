<?php
/* ====================
[BEGIN_COT_EXT]
Code=onlineconsultant
Name=Online Consultant
Description=One-on-one chat assistance in real-time directly from your website.
Version=1.2.1
Date=25 April 2013
Author=Alex
Copyright=&copy; 2011-2013 http://portal30.ru (Portal30 Studio)
Requires_plugins=hits,whosonline
Auth_guests=R
Lock_guests=W12345A
Auth_members=R
Lock_members=A
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
useCaptcha=10:radio::0:Use captcha?
admCons=12:select:no,notify,yes:notify:Administrators - Consultants
offLineConsNotify=14:radio::1:Off line msg consultants notify
offLineAdminNotify=15:radio::1:Off line msg admin notify
offLineNotifyEmail=16:string:::Off line notyfication e-mail
enablepresurvey=20:radio::0:Enable Pre-chat survey

surveyaskmail=24:radio::0:Ask visitor e-mail
surveyaskmessage=26:radio::0:Show initial question field
usercanchangename=28:radio::1:Allows users to change their names
sendmessagekey=30:select:enter,ctrl-enter:ctrl-enter:Send messages with
updatefrequency_operator=32:string::15:Operator's console refresh time
updatefrequency_chat=34:string::5:Chat refresh time
showonlineoperators=36:radio::0:Show online operators on List of awaiting visitors page
online_timeout=38:string::30:Operator online time threshold
usernamepattern=40:string::{name}:Operator online time threshold
operatorCanStart=42:radio::0:Operator Can Start chat
updatefrequency_browser=44:string::120:Frequency of polling the server by user's browser
enablepopupnotification=46:radio::0:Enable popup nitification
jQueryUIon=48:radio::1:Turn jQueryUI on
showThreads=50:select:All_opened,online:online:Show threads
storeHistory=55:radio::1:Store History
chatLoadHistoryCnt=57:select:all,0,5,10,15,20,25,30,35,40,45,50:10:Show message count on reopen Thred
[END_COT_EXT_CONFIG]
  ==================== */

// TODO операторы по группам
//  enablegroups=18:radio::0:Enable Groups
//surveyaskgroup=22:radio::1:Allows visitor to choose department/group
/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 */
defined('COT_CODE') or die('Wrong URL.');
