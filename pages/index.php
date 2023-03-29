<?php
/**
 * Formatiert zwei Strings so, das sie in ein rex_form passen.
 * @param type $label Label
 * @param type $content Inhalt
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

if (!str_contains(rex_be_controller::getCurrentPage(), 'multinewsletter/settings') &&
        (!rex_config::get('multinewsletter', 'link', 0) || !rex_config::get('multinewsletter', 'sender', 0) || !rex_config::get('multinewsletter', 'link_abmeldung', 0) || !rex_config::get('multinewsletter', 'lang_'. rex_clang::getStartId() .'_subscribe', 0))) {
    echo rex_view::error(rex_i18n::msg('multinewsletter_config_warning'));
    rex_be_controller::setCurrentPage('multinewsletter/settings');
} else {
    if (0 == rex_config::get('d2u_helper', 'article_id_privacy_policy', 0) || 0 == rex_config::get('d2u_helper', 'article_id_impress', 0)) {
        echo rex_view::warning(rex_i18n::msg('d2u_helper_gdpr_warning'));
    }
}

rex_be_controller::includeCurrentPageSubPath();
