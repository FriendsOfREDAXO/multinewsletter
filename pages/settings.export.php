<?php
// export settings
if ('export' === rex_request::request('btn_export', 'string')) {
    $export_config = json_encode(rex_config::get('multinewsletter'));
    if (false !== $export_config) {
        $filename = 'export_multinewsletter_config_' . date('YmdHis') . '.json';
        header('Content-Disposition: attachment; filename="' . $filename . '"; charset=utf-8');
        rex_response::sendContent($export_config, 'application/octetstream');
        exit;
    }
    echo rex_view::error(rex_i18n::msg('multinewsletter_config_settings_export_error'));
}
?>
<form action="<?= rex_url::currentBackendPage() ?>" data-pjax="false" method="post">
    <div class="panel panel-edit">
        <header class="panel-heading">
            <div class="panel-title"><?= rex_i18n::msg('multinewsletter_config_settings_export_long') ?></div>
        </header>
        <div class="panel-body">
            <fieldset>
                <dl class="m-0">
                    <?= rex_i18n::msg('multinewsletter_config_settings_export_advice') ?>
                </dl>
            </fieldset>
        </div>
        <footer class="panel-footer">
            <div class="rex-form-panel-footer">
                <div class="btn-toolbar">
                    <button class="btn btn-save" type="submit" name="btn_export" id="btn_export" value="export"><?= rex_i18n::msg('multinewsletter_config_settings_export_btn') ?></button>
                </div>
            </div>
        </footer>
    </div>
</form>