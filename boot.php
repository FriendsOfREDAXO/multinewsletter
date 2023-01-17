<?php
if (!rex::isBackend() && rex_get('replace_vars', 'boolean', false)) {
    // Web frontend
	rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
		$multinewsletter_user = rex_get('email', 'string') == "" ? new MultinewsletterUser(0) : MultinewsletterUser::initByMail(rex_get('email', 'string'));
		return MultinewsletterNewsletter::replaceVars($ep->getSubject(), $multinewsletter_user, rex_article::getCurrent());
	});
}
else if (rex::isBackend() && rex::getUser()) {

    rex_view::addJsFile($this->getAssetsUrl('multinewsletter.js'));
    rex_view::addCssFile($this->getAssetsUrl('general.css'));
    rex_perm::register('multinewsletter[]', rex_i18n::msg('multinewsletter_addon_short_title'));

    if ($this->getConfig('use_yform')) {
        $page = $this->getProperty('page');
        unset($page['subpages']['user']);

        $this->setProperty('page', $page);
    }

    if (rex_get('page', 'string') == 'multinewsletter/user') {
        rex_extension::register('REX_FORM_SAVED', function ($ep) {

            if (MultinewsletterMailchimp::isActive()) {
                $user_id = rex_get('entry_id', 'int');
                $User    = new MultinewsletterUser($user_id);
                $User->save();
            }
            return $ep->getSubject();
        });
    }

    rex_extension::register('PACKAGES_INCLUDED', function ($ep) {
    });

    /**
     * Deletes language specific configurations and objects
     * @param rex_extension_point<string> $ep Redaxo extension point
     * @return array<string> Warning message as array
     */
    rex_extension::register('CLANG_DELETED', function (rex_extension_point $ep) {
        $warning  = $ep->getSubject();
        $params   = $ep->getParams();
        $clang_id = $params['id'];

        // Update users
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_user');
        $sql->setValue('clang_id', rex_clang::getStartId());
        $sql->setWhere('clang_id = :clang_id', ['clang_id' => $clang_id]);
        $sql->update();

        // Delete Archives
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_archive');
        $sql->setWhere('clang_id = :clang_id', ['clang_id' => $clang_id]);
        $sql->delete();

        // Delete language settings
        if (rex_config::get('multinewsletter', 'default_test_sprache') == $clang_id) {
            rex_config::set('multinewsletter', 'default_test_sprache', rex_clang::getStartId());
        }
        return $warning;
    });

    rex_extension::register('REX_YFORM_SAVED', function ($ep) {
        $sql = $ep->getSubject();

        if (!($sql instanceof Exception)) {
            $action = $ep->getParam('action');

            if ($ep->getParam('table') == rex::getTablePrefix() . '375_user') {
                $user = new MultinewsletterUser($ep->getParam('id'));

                if ($action != 'update') {
                    $user->subscriptiontype = 'backend';
                }
                $user->save();
            }
        }
        return $sql;
    });
	
	/**
	 * Checks if article is used by this addon
	 * @param rex_extension_point<string> $ep Redaxo extension point
	 * @return string Warning message as array
	 * @throws rex_api_exception If article is used
	 */
	rex_extension::register('ART_PRE_DELETED', function($ep) {
		$warning = [];
		$params = $ep->getParams();
		$article_id = $params['id'];

		// Groups
		$sql_groups = \rex_sql::factory();
		$sql_groups->setQuery('SELECT id, name FROM `' . \rex::getTablePrefix() . '375_group` '
			.'WHERE default_article_id = '. $article_id .' '
			.'GROUP BY id');


		// Prepare warnings
		// Groups
		for($i = 0; $i < $sql_groups->getRows(); $i++) {
			$message = '<a href="javascript:openPage(\'index.php?page=multinewsletter/groups&func=edit&entry_id='.
				$sql_groups->getValue('id') .'\')">'. rex_i18n::msg('multinewsletter_addon_short_title') ." - ". rex_i18n::msg('multinewsletter_menu_groups') .': '. $sql_groups->getValue('name') .'</a>';
			if(!in_array($message, $warning)) {
				$warning[] = $message;
			}
		}

		// Settings
		$addon = rex_addon::get("multinewsletter");
		if($addon->hasConfig("default_test_article") && $addon->getConfig("default_test_article") == $article_id) {
			$message = '<a href="index.php?page=multinewsletter/settings">'.
				 rex_i18n::msg('multinewsletter_addon_short_title') ." - ". rex_i18n::msg('multinewsletter_menu_config') . '</a>';
			if(!in_array($message, $warning)) {
				$warning[] = $message;
			}
		}

		if(count($warning) > 0) {
			throw new rex_api_exception(rex_i18n::msg('d2u_helper_rex_article_cannot_delete')."<ul><li>". implode("</li><li>", $warning) ."</li></ul>");
		}
		else {
			return "";
		}
	});
}