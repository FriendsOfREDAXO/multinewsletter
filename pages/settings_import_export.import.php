<?php

// import settings
if ('Import' == filter_input(INPUT_POST, 'btn_import')) {

    $sql_db = rex_sql::factory();
    $sql_db->beginTransaction();

    try {
        ### proof on null
        if ($_FILES['config_import_file']['tmp_name'] === '') {
            throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_null'));
        }
        ### proof on JSON file
        if ($_FILES['config_import_file']['type'] !== 'application/json') {
            throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_type'));
        }
        ### delete existing settings
        if (rex_config::get('multinewsletter')) {
            rex_config::removeNamespace('multinewsletter');
            $sql_db->setTable('rex_config')->setWhere(['namespace' => 'multinewsletter'])->delete();
        }
        $data = file_get_contents($_FILES['config_import_file']['tmp_name']); ### data read from json file
        $import_data = json_decode($data);  //decode a data
        $blacklist = ['default_test_article', 'link', 'link_abmeldung']; ### don't add quotes for link-fields
        foreach ($import_data as $key => $value) {
            $import_value = (in_array($key, $blacklist)) ? $value : '"' . $value . '"';
            $sql_db->setTable('rex_config')->setValue('namespace', 'multinewsletter')->setValue('key', $key)->setValue('value', $import_value)->insert();
        }
        $sql_db->commit();
    } catch (\Throwable $e) {
        $sql_db->rollBack();
        $error_message = $e->getMessage();
    }
    if ($error_message) {
        echo rex_view::error($error_message);
    } else {
        rex_package::require('multinewsletter')->clearCache();
        rex_config::refresh();
        echo rex_view::success(rex_i18n::msg('multinewsletter_config_settings_import_success'));
        echo rex_view::info(rex_i18n::msg('multinewsletter_config_settings_import_success_hints'));
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
                    <?= rex_view::warning(rex_i18n::msg('multinewsletter_config_settings_import_advice')); ?>
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