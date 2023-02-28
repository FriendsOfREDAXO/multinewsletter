<?php

$return = true;
if (!rex_addon::get('yform')->isAvailable()) {
    echo rex_view::error(rex_i18n::msg('d2u_helper_modules_error_yform'));
    $return = false;
}
return $return;
