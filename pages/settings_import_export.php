<?php

// export settings
if ('Export' == filter_input(INPUT_POST, 'btn_export')) {
  if (rex_config::get('multinewsletter')) {
    $export_config = json_encode(rex_config::get('multinewsletter'));
    $fileName = 'export_multinewsletter_config_' . date('YmdHis') . '.json';
    header('Content-Disposition: attachment; filename="' . $fileName . '"; charset=utf-8');
    rex_response::sendContent($export_config, 'application/octetstream');
    exit;
  } else {
    echo rex_view::error(rex_i18n::msg('multinewsletter_config_settings_export_error'));
  }
}
// import settings
if ('Import' == filter_input(INPUT_POST, 'btn_import')) {
  $sql_db = rex_sql::factory();
  $sql_db->beginTransaction();

  try {
    ### proof on empty
    if ($_FILES['config_import_file']['tmp_name'] === '') {
      throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_empty'));
    }
    ### proof on JSON file
    if ($_FILES['config_import_file']['type'] !== 'application/json') {
      throw new Exception(rex_i18n::msg('multinewsletter_config_settings_import_filetype'));
    }
    ### delete existing settings
    if (rex_config::get('multinewsletter')) {
      rex_config::removeNamespace('multinewsletter');
      $sql_db->setTable('rex_config')->setWhere(['namespace' => 'multinewsletter'])->delete();
    }
    $data = file_get_contents($_FILES['config_import_file']['tmp_name']); //data read from json file
    $import_data = json_decode($data);  //decode a data
    $blacklist = ['default_test_article', 'link', 'link_abmeldung']; // don't add quotes for link-fields
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
      <div class="panel-title"><?= rex_i18n::msg('multinewsletter_config_settings_import_export') ?></div>
    </header>
    <div class="panel-body">
      <fieldset>
        <dl class="rex-form-group form-group">
          <?= rex_view::warning(rex_i18n::msg('multinewsletter_config_settings_import_advice')); ?>
        </dl>
        <dl class="rex-form-group form-group">
          <dt><label for="config_import_file"><?= rex_i18n::msg('multinewsletter_config_settings_import_file') ?></label></dt>
          <dd><input class="form-control" type="file" name="config_import_file" id="config_import_file" style="width: 30%; display: inline-block; margin-right: 10px;" /><button class="btn btn-info" type="submit" name="btn_import" id="btn_import" value="Import"><?= rex_i18n::msg('multinewsletter_config_settings_import_btn') ?></button></dd>
        </dl>
        <dl class="rex-form-group form-group">
          <dt><label for="newsletter_file"><?= rex_i18n::msg('multinewsletter_config_settings_export_file') ?></label></dt>
          <dd><button class="btn btn-info" type="submit" id="btn_export" name="btn_export" value="Export"><?= rex_i18n::msg('multinewsletter_config_settings_export_btn') ?></button></dd>
        </dl>
      </fieldset>
    </div>
  </div>
</form>
