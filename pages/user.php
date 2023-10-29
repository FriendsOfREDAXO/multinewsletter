<?php
$func = rex_request('func', 'string');
$entry_id = (int) rex_request('entry_id', 'int');

if ('' != filter_input(INPUT_POST, 'newsletter_exportusers')) {
    $func = 'export';
}

$newsletter_groups = MultinewsletterGroup::getAll();

// Übersichtsliste
if ('' === $func) {
    // Anzuzeigende Nachrichten
    $messages = [];

    // Suchkriterien in Session schreiben
    if (!isset($_SESSION['multinewsletter'])) {
        $_SESSION['multinewsletter'] = [];
    }
    if (!isset($_SESSION['multinewsletter']['user'])) {
        $_SESSION['multinewsletter']['user'] = [];
    }
    // Suchbegriff
    if ('' != filter_input(INPUT_POST, 'search_query')) {
        $_SESSION['multinewsletter']['user']['search_query'] = filter_input(INPUT_POST, 'search_query');
        $_SESSION['multinewsletter']['user']['pagenumber'] = 1;
    } elseif (!isset($_SESSION['multinewsletter']['user']['search_query'])) {
        $_SESSION['multinewsletter']['user']['search_query'] = '';
    }

    // Sortierung
    if ('' != filter_input(INPUT_GET, 'orderby')) {
        $_SESSION['multinewsletter']['user']['orderby'] = filter_input(INPUT_GET, 'orderby');
        $_SESSION['multinewsletter']['user']['direction'] = filter_input(INPUT_GET, 'direction');
    } elseif (!isset($_SESSION['multinewsletter']['user']['orderby']) || '' == $_SESSION['multinewsletter']['user']['orderby']) {
        $_SESSION['multinewsletter']['user']['orderby'] = 'email';
    }
    if (!isset($_SESSION['multinewsletter']['user']['direction'])) {
        $_SESSION['multinewsletter']['user']['direction'] = 'ASC';
    }

    // Anzahl anzuzeigender User
    if (0 != filter_input(INPUT_POST, 'itemsperpage') && filter_input(INPUT_POST, 'itemsperpage', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) > 0) {
        $_SESSION['multinewsletter']['user']['itemsperpage'] = filter_input(INPUT_POST, 'itemsperpage', FILTER_VALIDATE_INT);
    } elseif (!isset($_SESSION['multinewsletter']['user']['itemsperpage']) || $_SESSION['multinewsletter']['user']['itemsperpage'] < 25) {
        $_SESSION['multinewsletter']['user']['itemsperpage'] = 25;
    }

    // Seitennummer
    if (0 != filter_input(INPUT_POST, 'pagenumber') && filter_input(INPUT_POST, 'pagenumber', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) > 0) {
        $_SESSION['multinewsletter']['user']['pagenumber'] = (filter_input(INPUT_POST, 'pagenumber', FILTER_VALIDATE_INT) - 1);
    } elseif (!isset($_SESSION['multinewsletter']['user']['pagenumber']) || $_SESSION['multinewsletter']['user']['pagenumber'] < 0) {
        $_SESSION['multinewsletter']['user']['pagenumber'] = 0;
    }

    // Gewählte Gruppe
    if ('' != filter_input(INPUT_POST, 'showgroup')) {
        $_SESSION['multinewsletter']['user']['showgroup'] = filter_input(INPUT_POST, 'showgroup');
    } elseif (!isset($_SESSION['multinewsletter']['user']['showgroup']) || '' == $_SESSION['multinewsletter']['user']['showgroup']) {
        $_SESSION['multinewsletter']['user']['showgroup'] = 'all';
    }

    // Gewählter Status
    if (filter_input(INPUT_POST, 'showstatus') >= -1 && filter_input(INPUT_POST, 'showstatus', FILTER_VALIDATE_INT) >= -1) {
        $_SESSION['multinewsletter']['user']['showstatus'] = filter_input(INPUT_POST, 'showstatus', FILTER_VALIDATE_INT);
    } elseif (!isset($_SESSION['multinewsletter']['user']['showstatus']) || $_SESSION['multinewsletter']['user']['showstatus'] < 0) {
        $_SESSION['multinewsletter']['user']['showstatus'] = -1;
    }

    // Gewählte Sprache
    if (filter_input(INPUT_POST, 'showclang') >= -1 && filter_input(INPUT_POST, 'showclang', FILTER_VALIDATE_INT) >= -1) {
        $_SESSION['multinewsletter']['user']['showclang'] = filter_input(INPUT_POST, 'showclang', FILTER_VALIDATE_INT);
    } elseif (!isset($_SESSION['multinewsletter']['user']['showclang']) || $_SESSION['multinewsletter']['user']['showclang'] < 0) {
        $_SESSION['multinewsletter']['user']['showclang'] = -1;
    }

    // Wenn Filter zurückgesetzt wurde
    if ('' != filter_input(INPUT_POST, 'newsletter_showall')) {
        $_SESSION['multinewsletter']['user']['search_query'] = '';
        $_SESSION['multinewsletter']['user']['showgroup'] = -1;
        $_SESSION['multinewsletter']['user']['showstatus'] = -1;
        $_SESSION['multinewsletter']['user']['showclang'] = -1;
    }

    // Aktionen gewählter oder einzelner Benutzer
    $multidelete = false;
    if ('X' == filter_input(INPUT_POST, 'newsletter_delete_items')) {
        $multidelete = true;
    }
    $multistatus = filter_input(INPUT_POST, 'newsletter_item_status_all');
    $multiclang = filter_input(INPUT_POST, 'newsletter_item_clang_all');
    $multigroup = filter_input(INPUT_POST, 'addtogroup');

    $post = filter_input_array(INPUT_POST);
    $selected_users = [];
    if (isset($post['newsletter_select_item'])) {
        $selected_users = array_keys($post['newsletter_select_item']);
    }
    $form_users = [];
    if (isset($post['newsletter_item'])) {
        $form_users = $post['newsletter_item'];
    }

    if (is_array($form_users)) {
        $aktion = false;
        foreach ($form_users as $user_id => $fields) {
            $user = new MultinewsletterUser($user_id);

            // Einzelaktionen
            foreach ($fields as $group_ids => $value) {
                // Gewählten Benutzer löschen
                if ('deleteme' == $group_ids) {
                    $user->delete();
                    $aktion = true;
                }
            }

            // Multiselect Aktionen
            if (in_array($user_id, $selected_users)) {
                // Gewählten Benutzer löschen
                if ($multidelete) {
                    $user->delete();
                    $aktion = true;
                } else {
                    // Status des gewählten Benutzers aktualisieren
                    if ($multistatus > -1) {
                        $user->status = $multistatus;
                    } else {
                        $user->status = $fields['status'];
                    }
                    // Sprache des gewählten Benutzers aktualisieren
                    if ($multiclang > -1) {
                        $user->clang_id = $multiclang;
                    }
                    // Gruppe des gewählten Benutzers aktualisieren
                    if ('none' == $multigroup) {
                        $user->group_ids = [];
                    } elseif ('all' == $multigroup) {
                        $all_group_ids = [];
                        foreach ($newsletter_groups as $group) {
                            $all_group_ids[] = $group->id;
                        }
                        $user->group_ids = $all_group_ids;
                    } elseif ((int) $multigroup > 0) {
                        if (in_array($multigroup, $user->group_ids)) {
                            continue;
                        }

                            $user->group_ids[] = $multigroup;

                    }
                    $user->save();
                    $aktion = true;
                }
            }
        }
        if ($aktion) {
            echo rex_view::success(rex_i18n::msg('multinewsletter_changes_saved'));
        }
    }

    // Liste anzuzeigender User holen
    $result_list = rex_sql::factory();
    $query_where = '';
    $where = [];
    if ('' != $_SESSION['multinewsletter']['user']['search_query']) {
        $where[] = "(email LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
            ."OR firstname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
            ."OR lastname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%')";
    }
    if ((int) $_SESSION['multinewsletter']['user']['showgroup'] > 0) {
        $where[] = "
            group_ids = '" . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '" . $_SESSION['multinewsletter']['user']['showgroup'] . "|%' OR
            group_ids LIKE '%|" . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '%|" . $_SESSION['multinewsletter']['user']['showgroup'] . "|%' OR
            group_ids LIKE '" . $_SESSION['multinewsletter']['user']['showgroup'] . ",%' OR
            group_ids LIKE '%," . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '%," . $_SESSION['multinewsletter']['user']['showgroup'] . ",%'
        ";
    } elseif ('no' == $_SESSION['multinewsletter']['user']['showgroup']) {
        $where[] = "(group_ids = '' OR group_ids IS NULL)";
    }
    if ($_SESSION['multinewsletter']['user']['showstatus'] >= 0) {
        $where[] = 'status = '. $_SESSION['multinewsletter']['user']['showstatus'];
    }
    if ($_SESSION['multinewsletter']['user']['showclang'] >= 0) {
        $where[] = 'clang_id = '. $_SESSION['multinewsletter']['user']['showclang'];
    }
    if (count($where) > 0) {
        $query_where .= ' WHERE '. implode(' AND ', $where) .' ';
    }
    if ($_SESSION['multinewsletter']['user']['orderby']) {
        $query_where .= 'ORDER BY '. $_SESSION['multinewsletter']['user']['orderby'] .' '. $_SESSION['multinewsletter']['user']['direction'];
    }
    $query_count = 'SELECT COUNT(*) as counter FROM '. rex::getTablePrefix() .'375_user '. $query_where;
    $result_list->setQuery($query_count);
    $count_users = $result_list->getValue('counter');

    $start = $_SESSION['multinewsletter']['user']['pagenumber'] * $_SESSION['multinewsletter']['user']['itemsperpage'];
    if ($start > $count_users) {
        // Wenn die Seitenanzahl über den möglichen Seiten liegt
        $start = 0;
        $_SESSION['multinewsletter']['user']['pagenumber'] = 0;
    }
    $query_list = 'SELECT id FROM '. rex::getTablePrefix() .'375_user '. $query_where. ' LIMIT '. $start .','. $_SESSION['multinewsletter']['user']['itemsperpage'];
    $result_list->setQuery($query_list);
    $num_rows_list = $result_list->getRows();

    $user_ids = [];
    for ($i = 0; $i < $num_rows_list; ++$i) {
        $user_ids[] = $result_list->getValue('id');
        $result_list->next();
    }

    $users = new MultinewsletterUserList($user_ids);

    // Ausgabe der Meldung vom Speichern eines Datensatzes
    if ('' != filter_input(INPUT_GET, '_msg')) {
        echo rex_view::success(filter_input(INPUT_GET, '_msg'));
    }
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post" name="MULTINEWSLETTER">
		<table class="table table-striped table-hover">
			<tbody>
				<tr>
					<td>
						<label><?= rex_i18n::msg('multinewsletter_filter_itemsperpage') ?></label>
					</td>
					<td>
						<?php
                            $select = new rex_select();
                            $select->setSize(1);
                            $select->setAttribute('class', 'form-control');
                            $select->setName('itemsperpage');
                            $numbers_per_page = [25, 50, 100, 200, 450];
                            foreach ($numbers_per_page as $number) {
                                $select->addOption($number .' '. rex_i18n::msg('multinewsletter_filter_pro_seite'), $number);
                            }
                            $select->setSelected($_SESSION['multinewsletter']['user']['itemsperpage']);
                            $select->setAttribute('onchange', 'this.form.submit()');
                            echo $select->get();
                        ?>
					</td>
					<td> </td>
					<td>
						<label><?= rex_i18n::msg('multinewsletter_filter_status') ?></label>
					</td>
					<td>
						<?php
                            $select = new rex_select();
                            $select->setSize(1);
                            $select->setName('showstatus');
                            $select->setAttribute('class', 'form-control');
                            $select->addOption(rex_i18n::msg('multinewsletter_status_online'), 1);
                            $select->addOption(rex_i18n::msg('multinewsletter_status_offline'), 0);
                            $select->addOption(rex_i18n::msg('multinewsletter_status_all'), -1);
                            $select->setSelected($_SESSION['multinewsletter']['user']['showstatus']);
                            $select->setAttribute('onchange', 'this.form.submit()');
                            echo $select->get();
                        ?>
					</td>
				</tr>
				<tr>
					<td>
						<label><?= rex_i18n::msg('multinewsletter_filter_groups') ?></label>
					</td>
					<td>
						<?php
                            if (!empty($newsletter_groups)) {
                                $group_ids = new rex_select();
                                $group_ids->setSize(1);
                                $group_ids->setAttribute('class', 'form-control');
                                $group_ids->addOption(rex_i18n::msg('multinewsletter_all_groups'), 'all');
                                foreach ($newsletter_groups as $group) {
                                    $group_ids->addOption($group->name, $group->id);
                                }
                                $group_ids->addOption(rex_i18n::msg('multinewsletter_no_groups'), 'no');
                                $group_ids->setSelected($_SESSION['multinewsletter']['user']['showgroup']);
                                $group_ids->setAttribute('onchange', 'this.form.submit()');
                                $group_ids->setName('showgroup');
                                echo $group_ids->get();
                            }
                        ?>
					</td>
					<td> </td>
					<td>
						<label><?= rex_i18n::msg('multinewsletter_filter_clang') ?></label>
					</td>
					<td>
						<?php
                            $select = new rex_select();
                            $select->setSize(1);
                            $select->setAttribute('class', 'form-control');
                            $select->setAttribute('onchange', 'this.form.submit()');
                            $select->setName('showclang');
                            $select->addOption(rex_i18n::msg('multinewsletter_clang_all'), -1);
                            foreach (rex_clang::getAll() as $rex_clang) {
                                $select->addOption($rex_clang->getName(), $rex_clang->getId());
                            }
                            $select->setSelected($_SESSION['multinewsletter']['user']['showclang']);
                            echo $select->get();
                        ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="text" class="form-control" name="search_query" value="<?= htmlspecialchars(stripslashes($_SESSION['multinewsletter']['user']['search_query']), ENT_QUOTES)?>" />
					</td>
					<td align="center">
						<button type="submit" name="search" class="btn btn-save"><img src="<?= $this->getAssetsUrl('lupe.png') ?>"></button>
					</td>
					<td>
						<label><?= $count_users .' '. rex_i18n::msg('multinewsletter_users_found') ?></label>
					</td>
					<td>
						<input class="btn btn-abort" type="submit" name="newsletter_showall" id="newsletter_showall" value="<?= rex_i18n::msg('multinewsletter_button_submit_showall') ?>" />
					</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th class="rex-table-icon"><a href="<?= rex_url::currentBackendPage() ?>&amp;func=add"><i class="rex-icon rex-icon-add-module"></i></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=email<?= ('email' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_email')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=firstname<?= ('firstname' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_firstname')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=lastname<?= ('lastname' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_lastname')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=clang_id<?= ('clang_id' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_clang')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=createdate<?= ('createdate' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_create')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=updatedate<?= ('updatedate' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_update')?></a></th>
					<th><a href="<?= rex_url::currentBackendPage() ?>&orderby=status<?= ('status' == $_SESSION['multinewsletter']['user']['orderby'] && 'ASC' == $_SESSION['multinewsletter']['user']['direction']) ? '&direction=DESC' : '&direction=ASC'?>"><?= rex_i18n::msg('multinewsletter_newsletter_status')?></a></th>
					<th align="center"><?= rex_i18n::msg('delete')?></th>
				</tr>
			</thead>
			<tbody>
			<?php
                if (!empty($users->users)) {
                    $status = new rex_select();
                    $status->setSize(1);
                    $status->addOption(rex_i18n::msg('multinewsletter_status_online'), 1);
                    $status->addOption(rex_i18n::msg('multinewsletter_status_offline'), 0);

                    foreach ($users->users as $user) {
                        $user_id = $user->id;
                        $user_lid = $user->clang_id;
                        // Status je nach Nutzer setzen
                        $status->resetSelected();
                        $status->setAttribute('class', 'form-control');
                        $status->setName('newsletter_item['. $user_id .'][status]');
                        $status->setSelected($user->status);
                        $status->setAttribute('onchange', "this.form['newsletter_select_item[". $user_id ."]'].checked=true");

                        echo '<tr>';
                        echo '<td><input type="checkbox" name="newsletter_select_item['. $user_id .']" value="true" style="width:auto" onclick="myrex_selectallitems(\'newsletter_select_item\',this)" /></td>';
                        echo '<td><a href="'. rex_url::currentBackendPage() .'&func=edit&entry_id='.$user_id.'">'. htmlspecialchars($user->email).'</a></td>';
                        echo '<td>'. htmlspecialchars($user->firstname) .'</td>';
                        echo '<td>'. htmlspecialchars($user->lastname) .'</td>';
                        if (rex_clang::exists($user_lid)) {
                            echo '<td>'. rex_clang::get($user_lid)->getName() .'</td>';
                        } else {
                            echo '<td></td>';
                        }
                        if ($user->createdate > 0) {
                            echo '<td>'. $user->createdate .'</td>';
                        } else {
                            echo '<td>&nbsp;</td>';
                        }
                        if ($user->updatedate > 0) {
                            echo '<td>'. $user->updatedate .'</td>';
                        } else {
                            echo '<td>&nbsp;</td>';
                        }
                        echo '<td>'. $status->get() .'</td>';
                        echo '<td align="center"><input type="submit" class="btn btn-delete" name="newsletter_item['. $user_id .'][deleteme]" onclick="return myrex_confirm(\''. rex_i18n::msg('multinewsletter_confirm_deletethis') .'\',this.form)" value="X" /></td>';
                        echo '</tr>';
                    }

                    $status->setName('newsletter_item_status_all');
                    $status->addOption(rex_i18n::msg('multinewsletter_get_each_status'), '-1');
                    $status->resetSelected();
                    $status->setSelected('-1');
            ?>
				<tr>
					<td valign="middle"><input type="checkbox" name="newsletter_select_item_all" value="true" style="width:auto" onclick="myrex_selectallitems('newsletter_select_item', this)" /></td>
					<td valign="middle"><strong><?= rex_i18n::msg('multinewsletter_edit_all_selected') ?></strong></td>
					<td colspan="2">
					<?php
                        if (!empty($newsletter_groups)) {
                            $group_ids = new rex_select();
                            $group_ids->setSize(1);
                            $group_ids->setAttribute('class', 'form-control');
                            $group_ids->setAttribute('style', 'width:100%');

                            $group_ids->addOption(rex_i18n::msg('multinewsletter_button_addtogroup'), 'empty');
                            $group_ids->addOption(rex_i18n::msg('multinewsletter_remove_from_all_groups'), 'none');
                            foreach ($newsletter_groups as $group) {
                                $group_ids->addOption(rex_i18n::msg('multinewsletter_add_to_group', $group->name), $group->id);
                            }
                            $group_ids->addOption(rex_i18n::msg('multinewsletter_add_to_all_groups'), 'all');
                            $group_ids->setName('addtogroup');
                            $group_ids->show();
                        }
                    ?>
					</td>
					<td valign="middle">
					<?php
                        $select = new rex_select();
                        $select->setSize(1);
                        $select->setAttribute('class', 'form-control');
                        $select->setName('newsletter_item_clang_all');
                        $select->addOption(rex_i18n::msg('multinewsletter_get_each_clang'), '-1');
                        foreach (rex_clang::getAll() as $rex_clang) {
                            $select->addOption($rex_clang->getName(), $rex_clang->getId());
                        }
                        $select->resetSelected();
                        $select->setSelected('-1');
                        $select->show();
                    ?>
					</td>
					<td valign="middle"></td>
					<td valign="middle"></td>
					<td valign="middle"><?= $status->get()?></td>
					<td valign="middle" align="center"><input type="submit" class="btn btn-delete" name="newsletter_delete_items" onclick="return myrex_confirm('<?= rex_i18n::msg('multinewsletter_confirm_deleteselected') ?>',this.form)" title="<?= rex_i18n::msg('multinewsletter_button_submit_delete') ?>" value="X" /></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td>&nbsp;</td>
					<td colspan="3"><input type="submit" style="width:100%" class="btn btn-save" name="newsletter_save_all_items" onclick="return myrex_confirm('<?= rex_i18n::msg('multinewsletter_confirm_save_all_items') ?>',this.form)" value="<?= rex_i18n::msg('multinewsletter_button_save_all_items') ?>" /><br clear="all"><br></td>
					<td colspan="5"> </td>
				</tr>
				<?php
                    // check, if there are more items to show
                    if ($count_users > $_SESSION['multinewsletter']['user']['itemsperpage']) {
                ?>
				<tr>
					<td>&nbsp;</td>
					<td colspan="8">
					<?php
                        // show the pagination
                        $temp = ceil($count_users / $_SESSION['multinewsletter']['user']['itemsperpage']);
                        for ($i = 0; $i < $temp; ++$i) {
                            if ($i != $_SESSION['multinewsletter']['user']['pagenumber']) {
                                echo '<input type="submit" class="btn btn-abort" name="pagenumber" value="'. (string) ($i + 1) .'" style="margin: 0 5px 5px 0px; width:50px;" />';
                            } else {
                                echo '<input type="submit" class="btn btn-save" name="pagenumber" value="'. (string) ($i + 1) .'" style="margin: 0 5px 5px 0px; width:50px;" onClick="return false;"/>';
                            }
                        }
                    ?>
					</td>
				</tr>
				<?php
                    }
                ?>
				<tr>
					<td>&nbsp;</td>
					<td colspan="3"><input style="width:100%;" class="btn btn-save" type="submit" name="newsletter_exportusers" id="newsletter_exportusers" value="<?= rex_i18n::msg('multinewsletter_button_submit_exportusers') ?>" /></td>
					<td colspan="5"> </td>
				</tr>
			<?php
                } // ENDE Wenn Benutzer vorhanden sind
                else {
            ?>
				<tr>
					<td>&nbsp;</td>
					<td colspan="8"><?= rex_i18n::msg('multinewsletter_no_items_found')?></td>
				</tr>
			<?php
                }
            ?>
			</tfoot>
		</table>
	</form>
<?php
}
// Eingabeformular
elseif ('edit' === $func || 'add' === $func) {
    $form = rex_form::factory(rex::getTablePrefix() .'375_user', rex_i18n::msg('multinewsletter_newsletter_userdata'), 'id = '. $entry_id, 'post', false);

    // E-Mail
    $field = $form->addTextField('email');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_email'));

    // Akademischer Titel
    $field = $form->addTextField('grad');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_grad'));

    // Anrede
    $field = $form->addSelectField('title');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_title'));
    $select = $field->getSelect();
    $select->setSize(1);
    $select->addOption(rex_i18n::msg('multinewsletter_newsletter_title-1'), -1);
    $select->addOption(rex_i18n::msg('multinewsletter_newsletter_title0'), 0);
    $select->addOption(rex_i18n::msg('multinewsletter_newsletter_title1'), 1);
    $select->addOption(rex_i18n::msg('multinewsletter_newsletter_title2'), 2);
    $field->setAttribute('style', 'width: 25%');

    // Vorname
    $field = $form->addTextField('firstname');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_firstname'));

    // Nachname
    $field = $form->addTextField('lastname');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_lastname'));

    // Sprache
    $field = $form->addSelectField('clang_id');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_clang'));
    $select = $field->getSelect();
    $select->setSize(1);
    foreach (rex_clang::getAll() as $rex_clang) {
        $select->addOption($rex_clang->getName(), $rex_clang->getId());
    }
    $field->setAttribute('style', 'width: 25%');

    // Status
    $field = $form->addSelectField('status');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_status'));
    $select = $field->getSelect();
    $select->setSize(1);
    $select->addOption(rex_i18n::msg('multinewsletter_status_offline'), 0);
    $select->addOption(rex_i18n::msg('multinewsletter_status_online'), 1);
    $field->setAttribute('style', 'width: 25%');

    // Auswahlfeld Gruppen
    $field = $form->addSelectField('group_ids');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_group'));
    $select = $field->getSelect();
    $select->setSize(5);
    $select->setMultiple(1);
    $query = 'SELECT name, id FROM '. rex::getTablePrefix() .'375_group ORDER BY name';
    $select->addSqlOptions($query);
    $field->setAttribute('required', 'required');

    if ('edit' == $func) {
        // Erstellt und Aktualisiert
        $query_user = 'SELECT * FROM '. rex::getTablePrefix() .'375_user WHERE id = '. $entry_id;
        $result_user = rex_sql::factory();
        $result_user->setQuery($query_user);
        $rows_counter = $result_user->getRows();
        if ($rows_counter > 0) {
            $createdate = date('Y-m-d H:i:s');
            if ('' != $result_user->getValue('createdate')) {
                $createdate = $result_user->getValue('createdate');
            }
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_createdate'), $createdate));
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_createip'),
                    $result_user->getValue('createip')));

            $activationdate = '-';
            if ('' != $result_user->getValue('activationdate')) {
                $activationdate = $result_user->getValue('activationdate');
            }
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_activationdate'), $activationdate));
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_activationip'),
                    $result_user->getValue('activationip')));

            $updatedate = '-';
            if ('' != $result_user->getValue('updatedate')) {
                $updatedate = $result_user->getValue('updatedate');
            }
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_updatedate'), $updatedate));
            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_updateip'),
                    $result_user->getValue('updateip')));

            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_subscriptiontype'),
                    $result_user->getValue('subscriptiontype')));

            $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_privacy_policy'),
                    1 == $result_user->getValue('privacy_policy_accepted') ? rex_i18n::msg('multinewsletter_newsletter_privacy_policy_accepted') : rex_i18n::msg('multinewsletter_newsletter_privacy_policy_not_accepted')));
        }

        $field = $form->addHiddenField('updatedate');
        $field->setValue(date('Y-m-d H:i:s'));

        $field = $form->addHiddenField('updateip');
        $field->setValue(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP));

        $form->addParam('entry_id', $entry_id);
    } elseif ('add' == $func) {
        $field = $form->addHiddenField('createip');
        $field->setValue(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP));

        $field = $form->addHiddenField('subscriptiontype');
        $field->setValue('backend');
    }

    // Aktivierungsschlüssel
    $field = $form->addTextField('activationkey');
    $field->setLabel(rex_i18n::msg('multinewsletter_newsletter_key'));

    $form->show();
} elseif ('export' == $func) {
    // Bisherige Ausgabe von Redaxo löschen
    ob_end_clean();

    $result_list = rex_sql::factory();
    $query_where = '';
    $where = [];
    if ('' != $_SESSION['multinewsletter']['user']['search_query']) {
        $where[] = "(email LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
            ."OR firstname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
            ."OR lastname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%')";
    }
    if (false !== filter_var($_SESSION['multinewsletter']['user']['showgroup'], FILTER_VALIDATE_INT)) {
        $where[] = "
            group_ids = '" . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '" . $_SESSION['multinewsletter']['user']['showgroup'] . "|%' OR
            group_ids LIKE '%|" . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '%|" . $_SESSION['multinewsletter']['user']['showgroup'] . "|%' OR
            group_ids LIKE '" . $_SESSION['multinewsletter']['user']['showgroup'] . ",%' OR
            group_ids LIKE '%," . $_SESSION['multinewsletter']['user']['showgroup'] . "' OR
            group_ids LIKE '%," . $_SESSION['multinewsletter']['user']['showgroup'] . ",%'
        ";
    }
    if ($_SESSION['multinewsletter']['user']['showstatus'] >= 0) {
        $where[] = 'status = '. $_SESSION['multinewsletter']['user']['showstatus'];
    }
    if ($_SESSION['multinewsletter']['user']['showclang'] >= 0) {
        $where[] = 'clang_id = '. $_SESSION['multinewsletter']['user']['showclang'];
    }
    if (count($where) > 0) {
        $query_where .= ' WHERE '. implode(' AND ', $where) .' ';
    }
    if ($_SESSION['multinewsletter']['user']['orderby']) {
        $query_where .= 'ORDER BY '. $_SESSION['multinewsletter']['user']['orderby'] .' '. $_SESSION['multinewsletter']['user']['direction'];
    }
    $start = $_SESSION['multinewsletter']['user']['pagenumber'] * $_SESSION['multinewsletter']['user']['itemsperpage'];
    $query_list = 'SELECT id FROM '. rex::getTablePrefix() .'375_user '. $query_where;

    $result_list->setQuery($query_list);
    $num_rows_list = $result_list->getRows();
    $user_ids = [];
    for ($i = 0; $i < $num_rows_list; ++$i) {
        $user_ids[] = $result_list->getValue('id');
        $result_list->next();
    }

    $users = new MultinewsletterUserList($user_ids);
    $users->exportCSV();
}
