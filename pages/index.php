<?php

/**
 * Formatiert zwei Strings so, das sie in ein rex_form passen.
 * @param string $label Label
 * @param string $content Inhalt
 * @return string raw field output
 */
function raw_field($label, $content)
{
    $formated_content = '<dl class="rex-form-group form-group">';
    $formated_content .= '<dt><label class="control-label" for="rex-375-group-gruppen-default-sender-name">'. $label .'</label></dt>';
    $formated_content .= '<dd style="padding-top: 7px;">'. $content .'</dd>';
    $formated_content .= '</dl>';

    return $formated_content;
}

echo rex_view::title(rex_i18n::msg('multinewsletter_addon_short_title'));

// Forward to settings page, if no settings are available
if (!str_contains(rex_be_controller::getCurrentPage(), 'multinewsletter/settings') &&
        (0 === (int) rex_config::get('multinewsletter', 'link', 0) || '' === rex_config::get('multinewsletter', 'sender', '') || 0 === (int) rex_config::get('multinewsletter', 'link_abmeldung', 0) || '' === rex_config::get('multinewsletter', 'lang_'. rex_clang::getStartId() .'_subscribe', ''))) {
    header('Location: '. rex_url::backendPage('multinewsletter/settings/settings'));
    exit;
}

// Show settings warning
if (!str_contains(rex_be_controller::getCurrentPage(), 'multinewsletter/settings/import') &&
    (0 === (int) rex_config::get('multinewsletter', 'link', 0) || '' === rex_config::get('multinewsletter', 'sender', '') || 0 === (int) rex_config::get('multinewsletter', 'link_abmeldung', 0) || '' === rex_config::get('multinewsletter', 'lang_'. rex_clang::getStartId() .'_subscribe', ''))) {
    echo rex_view::error(rex_i18n::msg('multinewsletter_config_warning'));
}

// Show d2u_helper addon settings warning
if (0 === (int) rex_config::get('d2u_helper', 'article_id_privacy_policy', 0) || 0 === (int) rex_config::get('d2u_helper', 'article_id_impress', 0)) {
    echo rex_view::warning(rex_i18n::msg('d2u_helper_gdpr_warning'));
}

rex_be_controller::includeCurrentPageSubPath();
