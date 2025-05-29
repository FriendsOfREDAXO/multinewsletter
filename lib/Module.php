<?php

namespace FriendsOfRedaxo\MultiNewsletter;

/**
 * Class managing modules published by www.design-to-use.de.
 *
 * @author Tobias Krais
 */
class Module
{
    /**
     * Get modules offered by D2U Helper addon.
     * @return \TobiasKrais\D2UHelper\Module[] Modules offered by D2U Helper addon
     */
    public static function getModules()
    {
        $d2u_multinewsletter_modules = [];
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-1',
            'MultiNewsletter Anmeldung mit Name und Anrede',
            10);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-2',
            'MultiNewsletter Abmeldung',
            10);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-3',
            'MultiNewsletter Anmeldung nur mit Mail',
            10);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-4',
            'MultiNewsletter YForm Anmeldung',
            11);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-5',
            'MultiNewsletter YForm Abmeldung',
            6);
        return $d2u_multinewsletter_modules;
    }
}
