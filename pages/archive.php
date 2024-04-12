<?php

$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$resend_failure = rex_request('resend-failure', 'int', 0);

if ($resend_failure > 0) {
    $query_archive = 'SELECT recipients_failure FROM ' . rex::getTablePrefix() . '375_archive WHERE id = ' . $resend_failure;
    $result_archive = rex_sql::factory();
    $result_archive->setQuery($query_archive);
    $result_recipients_failure = (string) $result_archive->getValue('recipients_failure');
    $recipients_failure = [];
    if (str_contains($result_recipients_failure, '|')) {
        $recipients_failure = preg_grep('/^\s*$/s', explode('|', $result_recipients_failure), PREG_GREP_INVERT);
    } elseif (str_contains($result_recipients_failure, ',')) {
        $recipients_failure = preg_grep('/^\s*$/s', explode(',', $result_recipients_failure), PREG_GREP_INVERT);
    } elseif (false !== filter_var($result_recipients_failure, FILTER_VALIDATE_EMAIL)) {
        $recipients_failure[] = $result_recipients_failure;
    }
    if (is_array($recipients_failure)) {
        foreach ($recipients_failure as $recipient_failure) {
            $result_resend = rex_sql::factory();
            if (false !== filter_var($recipient_failure, FILTER_VALIDATE_EMAIL)) {
                $result_resend->setQuery('SELECT id FROM ' . rex::getTablePrefix() . "375_user WHERE email = '". $recipient_failure ."';");
                $user_id = (int) $result_resend->getValue('id');
                if ($user_id > 0) {
                    $result_resend->setQuery('REPLACE INTO ' . rex::getTablePrefix() . '375_sendlist SET archive_id = '. $resend_failure .', user_id = '. $user_id);
                }
            }
        }
    }

    // Remove from failure list
    $result_archive->setQuery('UPDATE ' . rex::getTablePrefix() . "375_archive SET recipients_failure = '' WHERE id = " . $resend_failure);

    // Set correct Newsletter article Name
    $archives = MultinewsletterNewsletterManager::getArchivesToSend(true);
    $newsletter_session = rex_request::session('multinewsletter', 'array');
    $newsletter_session['newsletter']['article_id'] = 0;
    $newsletter_session['newsletter']['article_name'] = $archives[0]->subject;
    rex_request::setSession('multinewsletter', $newsletter_session);

    // Forward to send page
    header('Location: '. rex_url::backendPage('multinewsletter/newsletter'));
    exit;
}
// Eingabeformular
if ('edit' === $func) {
    $form = rex_form::factory(rex::getTablePrefix() . '375_archive', rex_i18n::msg('multinewsletter_menu_archive'), 'id = ' . $entry_id, 'post', false);

    $query_archive = 'SELECT * FROM ' . rex::getTablePrefix() . '375_archive WHERE id = ' . $entry_id;
    $result_archive = rex_sql::factory();
    $result_archive->setQuery($query_archive);
    $archive_clang_id = (int) $result_archive->getValue('clang_id');

    // Sprach ID
    $language = '(' . $archive_clang_id . ')';
    if (rex_clang::exists($archive_clang_id)) {
        $rex_clang = rex_clang::get($archive_clang_id);
        if ($rex_clang instanceof rex_clang) {
            $language = $rex_clang->getName() . ' ' . $language;
        }
    }
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_language'), $language));

    // Betreff
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_subject'), html_entity_decode((string) $result_archive->getValue('subject'))));

    // Inhalt
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_htmlbody'), '<a href="' . rex_url::currentBackendPage() . '&func=shownewsletter&shownewsletter=' . $entry_id . '" target="_blank">' . rex_i18n::msg('multinewsletter_archive_output_details') . '</a>'));

    // Empfänger
    $recipients_raw = (string) $result_archive->getValue('recipients');
    $recipients = [];
    if (str_contains($recipients_raw, '|')) {
        $recipients = preg_grep('/^\s*$/s', explode('|', $recipients_raw), PREG_GREP_INVERT);
    } elseif (str_contains($recipients_raw, ',')) {
        $recipients = preg_grep('/^\s*$/s', explode(',', $recipients_raw), PREG_GREP_INVERT);
    } else {
        $recipients[] = $recipients_raw;
    }
    $recipients_html = '<div style="font-size: 0.75em; width: 100%; max-height: 400px; overflow:auto; padding:8px;"><table width="100%"><tr>';
    if (is_array($recipients)) {
        foreach ($recipients as $key => $recipient) {
            $key = (int) $key;
            $recipients_html .= "<td width='33%'>" . $recipient . '</td>';
            if ($key > 1 && 2 === $key % 3) {
                $recipients_html .= '</tr><tr>';
            }
        }
    }
    $recipients_html .= '</tr></table></div>';
    if (is_array($recipients) && count($recipients) > 0 && !str_contains((string) $recipients[0], 'Addresses deleted')) {
        $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients_count'), (string) count($recipients)));
    }
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients'), $recipients_html));

    // Recipients with send failures
    $recipients_failure_raw = (string) $result_archive->getValue('recipients_failure');
    $recipients_failure = [];
    if (str_contains($recipients_failure_raw, '|')) {
        $recipients_failure = preg_grep('/^\s*$/s', explode('|', $recipients_failure_raw), PREG_GREP_INVERT);
    } elseif (str_contains($recipients_failure_raw, ',')) {
        $recipients_failure = preg_grep('/^\s*$/s', explode(',', $recipients_failure_raw), PREG_GREP_INVERT);
    } elseif (false !== filter_var($recipients_failure_raw, FILTER_VALIDATE_EMAIL)) {
        $recipients_failure[] = $recipients_failure_raw;
    }
    $recipients_failure_html = '<div style="font-size: 0.75em; width: 100%; max-height: 400px; overflow:auto; background-color: white; padding:8px;"><table width="100%"><tr>';
    if (is_array($recipients_failure)) {
        foreach ($recipients_failure as $key_failure => $recipient_failure) {
            $key_failure = (int) $key_failure;
            $recipients_failure_html .= "<td width='33%'>" . $recipient_failure . '</td>';
            if ($key_failure > 1 && 2 === $key_failure % 3) {
                $recipients_failure_html .= '</tr><tr>';
            }
        }
    }
    $recipients_failure_html .= '</tr></table></div>';
    if (is_array($recipients_failure) && count($recipients_failure) > 0 && isset($recipients_failure[0]) && !str_contains((string) $recipients_failure[0], 'Addresses deleted')) {
        $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients_failure_count'), (string) count($recipients_failure)));
    }

    if (is_array($recipients_failure) && count($recipients_failure) > 0) {
        $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients_failure'), $recipients_failure_html));
        $form->addRawField(raw_field('', '<a href="'. rex_url::currentBackendPage(['resend-failure' => $entry_id]) .'">'. rex_i18n::msg('multinewsletter_archive_recipients_failure_resend') .'</a>'));
    }

    // E-Mail-Adresse Absender
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_group_default_sender_email'), (string) $result_archive->getValue('sender_email')));

    // Empfänger Gruppen
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_groupname'), (string) $result_archive->getValue('group_ids')));

    // Name Absender
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_group_default_sender_name'), (string) $result_archive->getValue('sender_name')));

    // Erstellungsdatum
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_preparedate'), (string) $result_archive->getValue('setupdate')));

    // Sendedatum
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_sentdate'), (string) $result_archive->getValue('sentdate')));

    // Redaxo Benutzer vom Versand
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_redaxo_sender'), (string) $result_archive->getValue('sentby')));

    $form->addParam('entry_id', $entry_id);

    $form->show();

    echo '<br><style>#rex-375-archive-archiv-save, #rex-375-archive-archiv-apply {visibility:hidden}</style>';
}
// Newsletter anzeigen
elseif ('shownewsletter' === $func) {
    // Zuerst bisherige Ausgabe von Redaxo löschen
    ob_end_clean();
    header_remove();

    $query_archive = 'SELECT * FROM ' . rex::getTablePrefix() . '375_archive WHERE id = '. filter_input(INPUT_GET, 'shownewsletter', FILTER_VALIDATE_INT);
    $result_archive = rex_sql::factory();
    $result_archive->setQuery($query_archive);

    echo base64_decode((string) $result_archive->getValue('htmlbody'), true);
    exit;
}
// Delete entry and in case also remaining sendlist users
elseif ('delete' === $func) {
    $result = rex_sql::factory();
    $result->setQuery('DELETE FROM ' . rex::getTablePrefix() . '375_archive WHERE id = ' . $entry_id);
    $result->setQuery('DELETE FROM ' . rex::getTablePrefix() . '375_sendlist WHERE archive_id = ' . $entry_id);

    echo rex_view::success(rex_i18n::msg('multinewsletter_archive_deleted'));
    $func = '';
}

// Übersichtsliste
if ('' === $func) {
    $list = rex_list::factory('SELECT id, subject, sender_name, clang_id, sentdate FROM ' . rex::getTablePrefix() . '375_archive ORDER BY sentdate DESC');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-backup"></i>';
    $list->addColumn('', $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams('', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('subject', rex_i18n::msg('multinewsletter_archive_subject'));
    $list->setColumnParams('subject', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('sender_name', rex_i18n::msg('multinewsletter_group_default_sender_name'));
    $list->setColumnParams('sender_name', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('clang_id', rex_i18n::msg('multinewsletter_newsletter_clang'));
    $list->setColumnFormat('clang_id', 'custom', static function ($params) {
        $list_params = $params['list'];
        $clang = rex_clang::get($list_params->getValue('clang_id'));
        if ($clang instanceof rex_clang) {
            return $clang->getCode();
        }
        return $list_params->getValue('clang_id');
    });

    $list->setColumnParams('clang_id', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('sentdate', rex_i18n::msg('multinewsletter_archive_sentdate'));
    $list->setColumnParams('sentdate', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('confirm_delete_module'));

    $list->setNoRowsMessage(rex_i18n::msg('multinewsletter_group_not_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('multinewsletter_menu_archive'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
