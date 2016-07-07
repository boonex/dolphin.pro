<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aLangContent = array(
    '_sys_module_pageac' => 'Контроль доступа к страницам',
    '_bx_pageac' => 'Контроль доступа к страницам',
    '_bx_pageac_note' => 'Замечание',
    '_bx_pageac_note_text' => '<p class="pageac_notes">Символы <b>&nbsp;&nbsp;|\\{}[]()#:^$.?+*&nbsp;&nbsp;</b> являются специальными символами в регулярных выражениях, поэтому их нужно предварять символом "обратный слеш" <b>&nbsp;\&nbsp;</b>. Вот почему, если Вы не отметили опцию "Расширенное Выражение", скрипт будет проставлять обратные слеши за Вас.</p>
<p class="pageac_notes">Например, если Вы настраиваете доступ к модулю <b>Фото</b>, Вы должны написать правило так: <br /><b>m/photos/</b></p>
<p class="pageac_notes">Если Вы знакомы с регулярными выражениями в UNIX-стиле, Вы можете отметить опцию "Расширенное Выражение" во время создания правила доступа и тем самым воспользоваться всеми возможностями регулярных выражений.</p>',
    '_bx_pageac_new_rule' => 'Новое правило доступа',
    '_bx_pageac_current_rules' => 'Текущие правила доступа',
    '_bx_pageac_forbidden_groups' => 'Запрещенные группы',
    '_bx_pageac_add_rule' => 'Добавить',
    '_bx_pageac_no_rules_admin' => 'Правила доступа ещё не определены',
    '_bx_pageac_url' => 'URL',
    '_bx_pageac_action' => 'Действие',
    '_bx_pageac_update' => 'Обновить',
    '_bx_pageac_delete' => 'Удалить',
    '_bx_pageac_visible_for' => 'Видимо для членств',
    '_bx_pageac_visible_for_all' => 'Все',
    '_bx_pageac_access_denied' => 'Доступ запрещен',
    '_bx_pageac_deny_text' => 'У Вас нет доступа к этой станице.',
    '_bx_pageac_rules_page' => 'Доступ к станице',
    '_bx_pageac_topmenu_page' => 'Доступ к главному меню',
    '_bx_pageac_membermenu_page' => 'Доступ к пользовательскому Меню',
    '_bx_pageac_page_blocks_page' => 'Доступ к блокам страниц',
    '_bx_pageac_loading' => 'Загрузка...',
    '_bx_pageac_page_url' => 'URL-шаблон доступа',
    '_bx_pageac_page_url_empty' => 'URL-шаблон доступа не может быть пустым',
    '_bx_pageac_saved' => 'Сохранено',
    '_bx_pageac_deleted' => 'Удалено',
    '_bx_pageac_page_url_descr' => 'Адрес страницы, относительный к доменному имени (например, <b>search.php</b> или <b>m/chat/</b>). Или регулярное выражение (для продвинутых пользователей)',
    '_bx_pageac_advanced' => 'Расширенное выражение',
    '_bx_pageac_advanced_descr' => 'Если отмечено, то будет сохранено как есть, без конвертации специальных символов в формат регулярных выражений'
);
