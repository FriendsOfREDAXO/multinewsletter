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
            'MultiNewsletter Anmeldung mit Name und Anrede (BS4, deprecated)',
            11);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-2',
            'MultiNewsletter Abmeldung (BS4, deprecated)',
            10);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-3',
            'MultiNewsletter Anmeldung nur mit Mail (BS4, deprecated)',
            12);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-4',
            'MultiNewsletter YForm Anmeldung (BS4, deprecated)',
            12);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-5',
            'MultiNewsletter YForm Abmeldung (BS4, deprecated)',
            6);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-6',
            'MultiNewsletter Anmeldung mit Name und Anrede (BS5)',
            2);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-7',
            'MultiNewsletter Abmeldung (BS5)',
            2);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-8',
            'MultiNewsletter Anmeldung nur mit Mail (BS5)',
            2);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-9',
            'MultiNewsletter YForm Anmeldung (BS5)',
            2);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-10',
            'MultiNewsletter YForm Abmeldung (BS5)',
            2);
        return $d2u_multinewsletter_modules;
    }
}
