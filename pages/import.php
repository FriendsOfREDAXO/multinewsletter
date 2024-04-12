<?php
$messages = [];

$import_action = filter_input(INPUT_POST, 'import_action');

// Wenn Formular schon ausgefüllt wurde
if (false !== $import_action && '' !== $import_action) {
    $import_file_raw = rex_request::files('newsletter_file');
    $import_filename = is_array($import_file_raw) && array_key_exists('tmp_name', $import_file_raw) ? $import_file_raw['tmp_name'] : '';
    if ('' !== $import_filename && file_exists($import_filename)) {
        $csv_users = [];
        $csv_handle = fopen($import_filename, "r");
        while (($data = fgetcsv($csv_handle, 100000, ";")) !== FALSE) {
            $csv_users[] = $data;
        }
        fclose($csv_handle);

        if (count($csv_users) > 0) {
            $fields = [
                'email' => -1,
                'grad' => -1,
                'firstname' => -1,
                'lastname' => -1,
                'title' => -1,
                'clang' => -1,
                'clang_id' => -1,
                'status' => -1,
                'createip' => -1,
                'send_group' => -1,
                'group_ids' => -1,
            ];
            // Überschriften auslesen
            if(is_array($csv_users[0])) {
                foreach ($csv_users[0] as $id => $name) {
                    $fields[$name] = $id;
                }
            }
            // Spalte "email" muss existieren
            if ($fields['email'] > -1) {
                $multinewsletter_list = new MultinewsletterUserList([]);
                foreach ($csv_users as $csv_user) {
                    if (false !== filter_var(trim($csv_user[$fields['email']]), FILTER_VALIDATE_EMAIL)) {
                        $multinewsletter_user = MultinewsletterUser::initByMail(strtolower($csv_user[$fields['email']]));
                        if (!$multinewsletter_user instanceof MultinewsletterUser) {
                            $multinewsletter_user = new MultinewsletterUser(0);
                            $multinewsletter_user->email = filter_var(trim($csv_user[$fields['email']]), FILTER_VALIDATE_EMAIL);
                        }

                        // Sprache
                        $user_clang_id = 0;
                        if ($fields['clang'] > -1 && array_key_exists($csv_user[$fields['clang_id']], rex_clang::getAll())) {
                            $user_clang_id = $csv_user[$fields['clang']];
                        } elseif ($fields['clang_id'] > -1 && array_key_exists($csv_user[$fields['clang_id']], rex_clang::getAll())) {
                            $user_clang_id = $csv_user[$fields['clang_id']];
                        } else {
                            // Default langugage
                            $user_clang_id = MultinewsletterNewsletter::getFallbackLang(rex_clang::getStartId());
                        }
                        if (false !== filter_var($user_clang_id, FILTER_VALIDATE_INT)) {
                            // Falls ID der Sprache im CSV festgelegt wurde
                            $multinewsletter_user->clang_id = filter_var($user_clang_id, FILTER_VALIDATE_INT);
                        } else {
                            // Falls Name der Sprache, statt ID in CSV festgelegt wurde
                            foreach (rex_clang::getAll() as $clang_id => $clang_name) {
                                if ($clang_name === $user_clang_id) {
                                    $multinewsletter_user->clang_id = $clang_id;
                                    break;
                                }
                            }
                        }

                        // Akademischer Grad
                        if ($fields['grad'] > -1 && '' !== $csv_user[$fields['grad']]) {
                            $multinewsletter_user->grad = $csv_user[$fields['grad']];
                        }
                        // Vorname
                        if ($fields['firstname'] > -1 && '' !== $csv_user[$fields['firstname']]) {
                            $multinewsletter_user->firstname = trim($csv_user[$fields['firstname']]);
                        }
                        // Nachname
                        if ($fields['lastname'] > -1 && '' !== $csv_user[$fields['lastname']]) {
                            $multinewsletter_user->lastname = trim($csv_user[$fields['lastname']]);
                        }
                        // Anrede
                        if ($fields['title'] > -1 && false !== filter_var($csv_user[$fields['title']], FILTER_VALIDATE_INT)) {
                            $multinewsletter_user->title = filter_var($csv_user[$fields['title']], FILTER_VALIDATE_INT);
                        }
                        // Status
                        if ($fields['status'] > -1 && false !== filter_var($csv_user[$fields['status']], FILTER_VALIDATE_INT)) {
                            $multinewsletter_user->status = filter_var($csv_user[$fields['status']], FILTER_VALIDATE_INT);
                        }
                        // IP Adresse (erstellt)
                        if ($fields['createip'] > -1 && false !== filter_var($csv_user[$fields['createip']], FILTER_VALIDATE_IP)) {
                            $multinewsletter_user->createip = filter_var($csv_user[$fields['createip']], FILTER_VALIDATE_IP);
                        } else {
                            $multinewsletter_user->createip = rex_request::server('REMOTE_ADDR', 'string');
                        }
                        // Erstellungsdatum
                        if ('' === $multinewsletter_user->createdate) {
                            $multinewsletter_user->createdate = date("Y-m-d H:i:s");
                        }
                        // IP Adresse (update)
                        $multinewsletter_user->updateip = rex_request::server('REMOTE_ADDR', 'string');
                        // Updatedatum
                        $multinewsletter_user->updatedate = date("Y-m-d H:i:s");
                        // Subscription type
                        $multinewsletter_user->subscriptiontype = 'import';
                        // Gruppen
                        $gruppen_ids = [];
                        if ($fields['send_group'] > -1) {
                            $gruppen_ids = preg_grep('/^\s*$/s', explode('|', $csv_user[$fields['send_group']]), PREG_GREP_INVERT);
                        } elseif ($fields['group_ids'] > -1) {
                            $gruppen_ids = preg_grep('/^\s*$/s', explode('|', $csv_user[$fields['group_ids']]), PREG_GREP_INVERT);
                        }
                        $gruppen_ids = is_array($gruppen_ids) ? array_map('intval', $gruppen_ids) : [];
                        foreach ($gruppen_ids as $gruppen_id) {
                            $orig_group_ids = $multinewsletter_user->group_ids;
                            if (!in_array($gruppen_id, $orig_group_ids, true)) {
                                $orig_group_ids[] = $gruppen_id;
                            }
                            $multinewsletter_user->group_ids = $orig_group_ids;
                        }

                        $multinewsletter_list->users[$multinewsletter_user->email] = $multinewsletter_user;
                    }
                }

                if (count($multinewsletter_list->users) > 0) {
                    $counter = 0;
                    foreach ($multinewsletter_list->users as $user) {
                        if ('delete' === $import_action) {
                            if ($user->id > 0) {
                                $user->delete();
                                ++$counter;
                            }
                        } elseif ('add_new' === $import_action) {
                            if (0 === $user->id) {
                                $user->save();
                                ++$counter;
                            }
                        } else { // import_action == overwrite
                            $user->save();
                            ++$counter;
                        }
                    }

                    // Ergebnis ausgeben
                    if ('delete' === $import_action) {
                        $messages[] = rex_i18n::msg('multinewsletter_import_success_delete', $counter);
                    } elseif ('add_new' === $import_action) {
                        $messages[] = rex_i18n::msg('multinewsletter_import_success_add', $counter);
                    } else { // import_action == overwrite
                        $messages[] = rex_i18n::msg('multinewsletter_import_success_overwrite', $counter);
                    }
                } // Ende wenn Nutzer gefunden wurden
                else {
                    $messages[] = rex_i18n::msg('multinewsletter_error_nothingtoimport');
                }
            } // Ende wenn "email"-feld im Import vorhanden
            else {
                $messages[] = rex_i18n::msg('multinewsletter_error_noemailfield');
            }
        } // Ende wenn CSV Datei keine Benutzer beinhaltete
        else {
            $messages[] = rex_i18n::msg('multinewsletter_error_nothingtoimport');
        }
    } else {
        $messages[] = rex_i18n::msg('multinewsletter_error_nothingtoimport');
    }
}

// Meldungen ausgeben
foreach ($messages as $message) {
    echo rex_view::success($message);
}
?>

<form action="<?= rex_url::currentBackendPage() ?>" method="post" name="MULTINEWSLETTER" enctype="multipart/form-data">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('multinewsletter_menu_import') ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><?= rex_i18n::msg('multinewsletter_menu_import') ?></legend>
				<dl class="rex-form-group form-group">
					<a href="<?= rex_url::backendPage('multinewsletter/help', ['chapter' => 'import']) ?>">
								<?= rex_i18n::msg('multinewsletter_expl_import') ?></a>
				</dl>
                <dl class="rex-form-group form-group">
                    <dt><label for="newsletter_file"><?= rex_i18n::msg('multinewsletter_import_csvfile') ?></label></dt>
                    <dd><input class="form-control" type="file" name="newsletter_file" id="newsletter_file"/></dd>
                </dl>
                <dl class="rex-form-group form-group">
                    <dt><label for="import_action"></label></dt>
                    <dd><input type="radio" value="overwrite" name="import_action"
                            <?php if ('' === $import_action || 'overwrite' === $import_action) {
                            echo 'checked="checked"';
                            } ?> />
                        <?= rex_i18n::msg('multinewsletter_import_overwrite')?>
                    </dd>
                </dl>
                <dl class="rex-form-group form-group">
                    <dt><label for="import_action"></label></dt>
                    <dd><input type="radio" value="delete" name="import_action"
                            <?php if ('delete' === $import_action) {
                            echo 'checked="checked"';
                            } ?> />
                        <?= rex_i18n::msg('multinewsletter_import_delete')?>
                    </dd>
                </dl>
                <dl class="rex-form-group form-group">
                    <dt><label for="import_action"></label></dt>
                    <dd><input  type="radio" value="add_new" name="import_action"
                            <?php if ('add_new' === $import_action) {
                            echo 'checked="checked"';
                            } ?> />
                        <?= rex_i18n::msg('multinewsletter_import_add_new')?>
                    </dd>
                </dl>
			</fieldset>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="Speichern" onclick="return myrex_confirm('<?= rex_i18n::msg('multinewsletter_confirm_import')?>', this.form)"><?= rex_i18n::msg('multinewsletter_button_submit_import') ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>