<?PHP
/**
 * Online Consultant Module for Cotonti
 *      Russian Lang file
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2012
 */
defined('COT_CODE') or die('Wrong URL');

$L['info_desc'] = 'Он-лайн консультант. Чат в реальном времени с посетителями Вашего сайта';

$L['oc']['locale_name'] = 'Русский';
$L['oc']['title'] = 'Он-лайн консультант';

/**
 * Plugin Body
 */
$L['oc']['agent_not_logged_in']='Ваша сессия устарела, войдите, пожалуйста, снова';
$L['oc']['ask_question']='Задать вопрос';
$L['oc']['canned_actions']='Изменить';
$L['oc']['canned_add']='Добавить сообщение...';
$L['oc']['canned_descr']='Создавайте текстовые сообщения, которыми будете часто пользоваться в чате.';
$L['oc']['canned_group']='Для группы';
$L['oc']['canned_locale']='Для языка';
$L['oc']['canned_title']='Шаблоны ответов';
$L['oc']['cannededit_descr']='Отредактируйте существующее сообщение.';
$L['oc']['cannededit_done']='Сохранено';
$L['oc']['cannededit_no_such']='Сообщение, возможно, уже было удалено';
$L['oc']['cannededit_title']='Редактировать шаблон';
$L['oc']['cannednew_descr']='Добавить новый шаблон для быстрого ответа.';
$L['oc']['cannednew_title']='Новый шаблон';
$L['oc']['captcha'] = "* Введите код с изображения";
$L['oc']['chat_came_from'] = "Посетитель пришел со страницы {0}";
$L['oc']['chat_client_changename'] = 'Изменить имя';
$L['oc']['chat_client_name']='Вы';
$L['oc']['chat_mailthread_sent_content']='История Вашего разговора была отправлена на адрес {0}';
$L['oc']['chat_mailthread_sent_title']='Отправлено';
$L['oc']['chat_predefined_answers']="Здравствуйте! Чем я могу Вам помочь?\nПодождите секунду, я переключу Вас на другого оператора.\nВы не могли бы уточнить, что Вы имеете ввиду..\nУдачи, до свиданья!";
$L['oc']['chat_user_redirected']='Посетитель {0} перенаправлен на url {1}';
$L['oc']['chat_status_operator_changed']='Оператор {0} сменил оператора {1}';
$L['oc']['chat_status_operator_dead']='У оператора возникли проблемы со связью, мы временно перевели Вас в 
    приоритетную очередь. Приносим извинения за Ваше ожидание.';
$L['oc']['chat_status_operator_joined']='Оператор {0} включился в разговор';
$L['oc']['chat_status_operator_left']='Оператор {0} покинул диалог';
$L['oc']['chat_status_operator_redirect']='Оператор {0} переключил Вас на другого оператора, пожалуйста, подождите немного';
$L['oc']['chat_status_operator_returned']='Оператор {0} вернулся в диалог';
$L['oc']['chat_status_user_changedname']='Посетитель сменил имя {0} на {1}';
$L['oc']['chat_status_user_dead'] = 'Посетитель закрыл окно диалога';
$L['oc']['chat_status_user_left'] = 'Посетитель {0} покинул диалог';
$L['oc']['chat_status_user_reopenedthread']='Посетитель заново вошел в диалог';
$L['oc']['chat_thread_state_chatting_with_agent']='В диалоге';
$L['oc']['chat_thread_state_closed']='Закрыто';
$L['oc']['chat_thread_state_loading']='Загружается';
$L['oc']['chat_thread_state_wait']='В очереди';
$L['oc']['chat_thread_state_wait_for_another_agent']='Ожидание оператора';
$L['oc']['chat_visitor_email'] = "E-Mail: {0}";
$L['oc']['chat_visitor_info'] = "О Посетителе: {0}";
$L['oc']['chat_wait']='Пожалуйста, подождите немного, к Вам присоединится оператор..';
$L['oc']['chat_window_chatting_with']='Вы общаетесь с';
$L['oc']['chat_window_close_title']='Закрыть диалог';
$L['oc']['chat_window_predefined_select_answer']='Выберите ответ';
$L['oc']['chat_window_product_name']='Online <span class="grey">Консультант</span>';
$L['oc']['chat_window_send_message']='Отправить сообщение';
$L['oc']['chat_window_send_message_short']='Отправить ({0})';
$L['oc']['chat_window_title'] = 'Он-лайн консультант';        
$L['oc']['chat_window_toolbar_mail_history']='Отправить историю диалога по электронной почте';
$L['oc']['chat_window_toolbar_refresh']='Обновить содержимое диалога';
$L['oc']['clients_how_to']='Для ответа посетителю кликните на соответствующее имя в списке.';
$L['oc']['clients_intro']='На этой странице можно просмотреть список ожидающих ответа посетителей.';
$L['oc']['clients_no_clients']='В этой очереди ожидающих посетителей нет';
$L['oc']['clients_title']='Список ожидающих посетителей';
$L['oc']['confirm_take_head'] = 'Сменить оператора';
$L['oc']['confirm_take_message'] = 'С посетителем <span style="color:blue;">{0}</span> уже общается
    <span style="color:green;">{1}</span>.<br/>Вы уверены что хотите сменить его?';
$L['oc']['confirm_yes'] = "Да, я уверен";
$L['oc']['content_history']='Поиск по истории диалогов.';
$L['oc']['errors_captcha']='Введенные символы не соответствуют изображению.';
$L['oc']['errors_required']='Заполните поле "{0}"';
$L['oc']['errors_wrong_field']='Неправильно заполнено поле "{0}".';
$L['oc']['form_field_name']='Ваше имя';
$L['oc']['form_field_email']='Ваш e-mail';
$L['oc']['invite_accept']='Получить консультацию';
$L['oc']['invite_accepted']='Приглашение в диалог принято';
$L['oc']['invite_message']='Добрый день!<br />Чем я могу Вам помочь?';
$L['oc']['invite_reject']='Нет, спасибо';
$L['oc']['invite_rejected']='Приглашение в диалог отклонено';
$L['oc']['invite_sended']='Приглашение отправлено';
$L['oc']['invite_title']='Служба поддержки';
$L['oc']['invite_user_to_chat']='Пригласить пользователя в диалог';
$L['oc']['leavemail_body']="Ваш посетитель '{0}' оставил сообщение:\n\n{2}\n\nЕmail: {1}\n{3}\n--- \nС уважением,\nВаш Он-лайн консультант";
$L['oc']['leavemail_subject']='Вопрос от {0}';
$L['oc']['leavemessage']='Оставить сообщение';
$L['oc']['leavemessage_descr']='К сожалению, сейчас нет ни одного доступного оператора. Попробуйте обратиться позже или 
    оставьте нам свой вопрос и мы свяжемся с Вами по оставленному адресу.';
$L['oc']['leavemessage_sent_message']="Спасибо за ваш вопрос, мы постараемся ответить на него как можно быстрее.";
$L['oc']['leavemessage_sent_title']='Ваше сообщение сохранено';
$L['oc']['leavemessage_title']='Оставьте ваше сообщение';
$L['oc']['mail_user_history_body']="Здраствуйте, {0}!\n\nПо Вашему запросу, высылаем историю: \n\n{1}\n--- \nС уважением,\nОн-лайн консультант";
$L['oc']['mail_user_history_subject']='Он-лайн консультант: история диалога';
$L['oc']['mailthread_title']='Отправить историю разговора<br/>на почтовый ящик';
$L['oc']['mailthread_enter_email']='Введите Ваш E-mail';
$L['oc']['new_message']='Новое сообщение';
$L['oc']['page_analysis_full_text_search']='Поиск по имени посетителя или по тексту сообщения';
$L['oc']['page_analysis_search_head_browser']='Браузер';
$L['oc']['page_analysis_search_head_host']='Адрес посетителя';
$L['oc']['page_analysis_search_head_messages']='Сообщений посетителя';
$L['oc']['page_analysis_search_head_time']='Время в диалоге';
$L['oc']['page_analysis_search_title']='История диалогов';
$L['oc']['page_client_pending_users']='На этой странице можно просмотреть список ожидающих ответа посетителей.';
$L['oc']['page_search_intro']='На данной странице можно осуществить поиск диалогов по имени пользователя или фразе, 
    встречающейся в сообщении.';
$L['oc']['pending_popup_notification']='Новый посетитель ожидает ответа.';
$L['oc']['pending_table_ban']='Пометить посетителя как нежелательного';
//$L['oc']['pending_table_head_contactid']='Адрес посетителя';
$L['oc']['pending_table_head_etc']='Разное';
$L['oc']['pending_table_head_name']='Имя';
$L['oc']['pending_table_head_operator']='Оператор';
$L['oc']['pending_table_head_state']='Состояние';
$L['oc']['pending_table_head_total']='Общее время';
$L['oc']['pending_table_head_waittime']='Время ожидания';
$L['oc']['pending_table_speak']='Нажмите для того, чтобы обслужить посетителя';
$L['oc']['pending_table_view']='Подключиться к диалогу в режиме просмотра';
$L['oc']['presurvey_intro']='Спасибо, что связались с нами! Заполните, пожалуйста, небольшую форму и нажмите "Отправить"
    чтобы начать диалог.';
$L['oc']['presurvey_question']='Ваш вопрос';
$L['oc']['presurvey_title']='Веб Мессенджер';
$L['oc']['profile']='Профиль';
$L['oc']['redirect_user_to_url']='Перенаправить пользователя на URL';
$L['oc']['report_bydate_2']='Диалогов';
$L['oc']['report_bydate_3']='Сообщений операторов';
$L['oc']['report_bydate_4']='Сообщений посетителей';
$L['oc']['report_bydate_title']='Использование мессенджера по дням';
$L['oc']['report_byoperator_1']='Оператор';
$L['oc']['report_byoperator_2']='Диалогов';
$L['oc']['report_byoperator_3']='Сообщений';
$L['oc']['report_byoperator_4']='Средняя длина сообщения (в символах)';
$L['oc']['report_byoperator_title']='Статистика по операторам';
$L['oc']['site_consultant']='Консультант сайта';
$L['oc']['statistics_dates']='Выберите даты';
$L['oc']['statistics_description']='Различные отчеты по посетителям и использованию мессенджера.';
$L['oc']['statistics_from']='С';
$L['oc']['statistics_till']='По';
$L['oc']['statistics_wrong_dates']='Вы выбрали дату для начала отчета после даты конца';
$L['oc']['thread_chat_log']='Протокол разговора';
$L['oc']['thread_intro']='На данной странице Вы можете просмотреть диалог.';
$L['oc']['time_dateformat']='%d %B %Y,';
$L['oc']['time_locale']='ru_RU.UTF-8';
$L['oc']['time_never']='Никогда';
$L['oc']['time_timeformat']='%H:%M';
$L['oc']['time_today_at']='Сегодня в';
$L['oc']['time_yesterday_at']='Вчера в';
$L['oc']['topMenu_admin']='Операторское меню';
$L['oc']['topMenu_users']='Посетители';
$L['oc']['typing_remote']='Ваш собеседник набирает текст...';
$L['oc']['user_not_online']='Пользователь больше не он-лайн';
$L['oc']['view_tread']='Посмотреть диалог';
$L['oc']['whoonline']='Сейчас он-лайн';

/**
 * Plugin Config
 */
$L['cfg_useCaptcha'] = array('Использовать капчу?', "Разрешать гостям оставлять сообщение только после ввода
    специального кода с картинки. Капча должна быть установлена и включена на сайте.");
//$L['cfg_consGroups'] = array('Группы пользователей консультантов', 'ID групп, через запятую (5-Администраторы)');
$L['cfg_admCons'] = array('Считать администраторов консультантами?', '<b>&laquo;no&raquo;</b> - нет,
      <b>&laquo;notify&raquo;</b> - только уведомлять об офф-лайн сообщениях, <b>&laquo;yes&raquo;</b> - да');
$L['cfg_offLineConsNotify'] = array('Уведомлять консультантов об офф-лайн сообщениях?', 'Консультанты получают уведомления
    на свой e-mail');
$L['cfg_offLineAdminNotify'] = array('Уведомлять администратора об офф-лайн сообщениях?', 'Даже если он не является консультантом');
$L['cfg_offLineNotifyEmail'] = array('Отсылать администратору уведомления об оффлайн сообщениях на e-mail', "Оставить пустым для
    использования E-mail'а администратора");
$L['cfg_enablegroups'] = array('Включить функцию &laquo;Группы&raquo;', '(В разработке. Не включать пока) Позволяет группам операторов организовывать
    отдельные очереди.');
$L['cfg_enablepresurvey'] = array('Включить &laquo;Опрос перед началом диалога&raquo;', 'Предлагает посетителю заполнить
    специальную форму перед началом чата.');

$L['cfg_surveyaskgroup'] = array('Позволять посетителю выбирать группу операторов', '(В разработке. Не включать пока) Показать/спрятать выбор группы в
    диалоге перед началом чата');

$L['cfg_surveyaskmail'] = array('Спрашивать e-mail адрес', 'Показать/спрятать поле ввода адреса электронной почты');
$L['cfg_surveyaskmessage'] = array('Предлагать сразу же задать вопрос', 'Показать/спрятать поле ввода первого вопроса');
$L['cfg_usercanchangename'] = array('Разрешать посетителям менять имена', 'Возможность убрать поле смены имени из чат окна');
$L['cfg_sendmessagekey'] = array('Посылать сообщение по', '');
$L['cfg_updatefrequency_chat'] = array('Периодичность обновления сообщений в чате', 'Укажите частоту опроса сервера в 
    секундах. По умолчанию, 2 секунды.');
$L['cfg_showonlineoperators'] = array('Показывать доступных операторов на странице ожидающих посетителей', 'Может
    замедлить обновление списка');
$L['cfg_updatefrequency_operator'] = array("Периодичность обновления консоли оператора", 'Укажите частоту опроса сервера
    в секундах. По умолчанию, 2 секунды.');
$L['cfg_online_timeout'] = array("Временной интервал доступности оператора", 'Количество секунд, в течении которых 
    оператор определяется как онлайн после последнего обновления. По умолчанию, 30 секунд.');
$L['cfg_usernamepattern'] = array("Отображаемое имя посетителя", 'Укажите как отобразить имя посетителя операторам.
    Можно использовать {name}, {id} и {addr}. По умолчанию: {name}');
$L['cfg_operatorCanStart'] = array("Оператор может начать диалог?", 'Может вызвать дополнительную нагрузку на сервер');
$L['cfg_updatefrequency_browser'] = array("Периодичность опроса сервера браузером пользователя", 'Укажите частоту опроса сервера
    в секундах. По умолчанию, 120 секунд. Если включено <b>&laquo;Оператор может начать диалог&raquo;</b>');
$L['cfg_enablepopupnotification'] = array("Показывать небольшой диалог при появлении новых посетителей в очереди.",
    'Позволяет привлечь ваше внимание, если звукового и визуального оповещения недостаточно.');
$L['cfg_jQueryUIon'] = array("Включить jQueryUI",'Необходимо для работы модуля. Включите только если jQueryUI нигде
    не подключен на Вашем сайте.');
$L['cfg_showThreads'] = array("В консоли оператора отображать диалоги",'<b>&laquo;All_opened&raquo;</b> - все незакрытые,
    <b>&laquo;online&raquo;</b> - Только тех пользователей, которые он-лайн');
// History
$L['cfg_storeHistory'] = array('Сохранять историю сообщений', 'Если выключено, то при закрытии диалога история будет очищена');
$L['cfg_chatLoadHistoryCnt'] = array('Количество сообщений при повторном отрытии диалога', 'При повторном открытии диалога
    будет загружено указанное количество сообщений');
$L['cfg_chatLoadHistoryCnt_params'] = array(
    'all' => "Все сообщения",
);