<?php
$messages = [];

// Wenn Formular schon ausgefüllt wurde
if ('' != filter_input(INPUT_POST, 'import_action')) {
    if (empty($_FILES['newsletter_file'])) {
        $messages[] = rex_i18n::msg('multinewsletter_error_nofile');
    } elseif (file_exists($_FILES['newsletter_file']['tmp_name'])) {
        $CSV = new CSV();
        $CSV->fImport($_FILES['newsletter_file']['tmp_name'], filesize($_FILES['newsletter_file']['tmp_name']));
        $csv_users = $CSV->data;

        if (!empty($csv_users) && is_array($csv_users)) {
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
            foreach ($csv_users[0] as $id => $name) {
                $fields[$name] = $id;
            }
            // Spalte "email" muss existieren
            if ($fields['email'] > -1) {
                $multinewsletter_list = new MultinewsletterUserList([]);
                foreach ($csv_users as $csv_user) {
                    if (false !== filter_var(trim($csv_user[$fields['email']]), FILTER_VALIDATE_EMAIL)) {
                        $multinewsletter_user = MultinewsletterUser::initByMail(strtolower($csv_user[$fields['email']]));
                        if (false === $multinewsletter_user) {
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
                                if ($clang_name == $user_clang_id) {
                                    $multinewsletter_user->clang_id = $clang_id;
                                    break;
                                }
                            }
                        }

                        // Akademischer Grad
                        if ($fields['grad'] > -1 && '' != $csv_user[$fields['grad']]) {
                            $multinewsletter_user->grad = $csv_user[$fields['grad']];
                        }
                        // Vorname
                        if ($fields['firstname'] > -1 && '' != $csv_user[$fields['firstname']]) {
                            $multinewsletter_user->firstname = trim($csv_user[$fields['firstname']]);
                        }
                        // Nachname
                        if ($fields['lastname'] > -1 && '' != $csv_user[$fields['lastname']]) {
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
                            $multinewsletter_user->createip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
                        }
                        // Erstellungsdatum
                        if (0 == $multinewsletter_user->createdate) {
                            $multinewsletter_user->createdate = time();
                        }
                        // IP Adresse (update)
                        $multinewsletter_user->updateip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
                        // Updatedatum
                        $multinewsletter_user->updatedate = time();
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
                            if (!in_array($gruppen_id, $orig_group_ids)) {
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
                        if ('delete' == filter_input(INPUT_POST, 'import_action')) {
                            if ($user->id) {
                                $user->delete();
                                ++$counter;
                            }
                        } elseif ('add_new' == filter_input(INPUT_POST, 'import_action')) {
                            if (!$user->id) {
                                $user->save();
                                ++$counter;
                            }
                        } else { // import_action == overwrite
                            $user->save();
                            ++$counter;
                        }
                    }

                    // Ergebnis ausgeben
                    if ('delete' == filter_input(INPUT_POST, 'import_action')) {
                        $messages[] = rex_i18n::msg('multinewsletter_import_success_delete', $counter);
                    } elseif ('add_new' == filter_input(INPUT_POST, 'import_action')) {
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
				<?php
                    if (!empty($_FILES['ec_icalfile'])) {
                    } else {
                        if (!empty($_FILES['ec_icalfile']) && empty($EC_VARS['events'])) {
                            echo '<p>'.rex_i18n::msg('multinewsletter_import_nousersfound').'</p>';
                        }
                ?>
						<dl class="rex-form-group form-group">
							<dt><label for="newsletter_file"><?= rex_i18n::msg('multinewsletter_import_csvfile') ?></label></dt>
							<dd><input class="form-control" type="file" name="newsletter_file" id="newsletter_file"/></dd>
						</dl>
						<dl class="rex-form-group form-group">
							<dt><label for="import_action"></label></dt>
							<dd><input type="radio" value="overwrite" name="import_action"
									<?php if ('' == filter_input(INPUT_POST, 'import_action') || 'overwrite' == filter_input(INPUT_POST, 'import_action')) {
									echo 'checked="checked"';
									} ?> />
								<?= rex_i18n::msg('multinewsletter_import_overwrite')?>
							</dd>
						</dl>
						<dl class="rex-form-group form-group">
							<dt><label for="import_action"></label></dt>
							<dd><input type="radio" value="delete" name="import_action"
									<?php if ('delete' == filter_input(INPUT_POST, 'import_action')) {
									echo 'checked="checked"';
									} ?> />
								<?= rex_i18n::msg('multinewsletter_import_delete')?>
							</dd>
						</dl>
						<dl class="rex-form-group form-group">
							<dt><label for="import_action"></label></dt>
							<dd><input  type="radio" value="add_new" name="import_action"
									<?php if ('add_new' == filter_input(INPUT_POST, 'import_action')) {
									echo 'checked="checked"';
									} ?> />
								<?= rex_i18n::msg('multinewsletter_import_add_new')?>
							</dd>
						</dl>
				<?php
                    }
                ?>
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