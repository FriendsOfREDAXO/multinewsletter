<?php

// import settings
if ('Import' === rex_request::request('btn_import', 'string')) {

    $sql_db = rex_sql::factory();
    try {
        $sql_db->transactional(static function () {
            $sql_db = rex_sql::factory();
            // proof on null
            $import_file_raw = rex_request::files('config_import_file');
            $import_filename = is_array($import_file_raw) && array_key_exists('tmp_name', $import_file_raw) ? (string) $import_file_raw['tmp_name'] : '';
            $import_filetype = is_array($import_file_raw) && array_key_exists('type', $import_file_raw) ? (string) $import_file_raw['type'] : '';

            if (0 === strlen($import_filename)) {
                throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_null'));
            }
            // proof on JSON file
            if ('application/json' !== $import_filetype) {
                throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_type'));
            }

            $data = file_get_contents($import_filename); // data read from json file
            if (false !== $data) {
                $import_data = json_decode($data, true);  // decode a data
                $blacklist = ['default_test_article', 'link', 'link_abmeldung']; // don't add quotes for link-fields
                if(is_array($import_data) && count($import_data) > 0) {
                    // delete existing settings
                    rex_config::removeNamespace('multinewsletter');
                    $sql_db->setTable('rex_config')->setWhere(['namespace' => 'multinewsletter'])->delete();

                    // insert new settings
                    foreach ($import_data as $key => $value) {
                        $import_value = (in_array($key, $blacklist, true)) ? (string) $value : '"' . $value . '"';
                        $sql_db->setTable('rex_config')->setValue('namespace', 'multinewsletter')->setValue('key', (string) $key)->setValue('value', $import_value)->insert();
                    }
                }
            }
        });
        rex_package::require('multinewsletter')->clearCache();
        rex_config::refresh();
        echo rex_view::success(rex_i18n::msg('multinewsletter_config_settings_import_success'));
        echo rex_view::info(rex_i18n::msg('multinewsletter_config_settings_import_success_hints'));
    }
    catch (Exception $e) {
        echo rex_view::error($e->getMessage());
    }
}

?>
<form action="<?= rex_url::currentBackendPage() ?>" data-pjax="false" method="post" enctype="multipart/form-data">
    <div class="panel panel-edit">
        <header class="panel-heading">
            <div class="panel-title"><?= rex_i18n::msg('multinewsletter_config_settings_import_long') ?></div>
        </header>
        <div class="panel-body">
            <fieldset>
                <dl class="rex-form-group form-group">
                    <?= rex_view::warning(rex_i18n::msg('multinewsletter_config_settings_import_advice')) ?>
                </dl>
                <dl class="rex-form-group form-group">
                    <dt><label for="config_import_file"><?= rex_i18n::msg('multinewsletter_config_settings_import_file') ?></label></dt>
                    <dd><input class="form-control" type="file" name="config_import_file" id="config_import_file" /></dd>
                </dl>
            </fieldset>
        </div>
        <footer class="panel-footer">
            <div class="rex-form-panel-footer">
                <div class="btn-toolbar">
                    <button class="btn btn-save" type="submit" name="btn_import" id="btn_import" value="Import"><?= rex_i18n::msg('multinewsletter_config_settings_import_btn') ?></button>
                </div>
            </div>
        </footer>
    </div>
</form>