<?php
/**
 * Class managing modules published by www.design-to-use.de.
 *
 * @author Tobias Krais
 */
class D2UMultiNewsletterModules
{
    /**
     * Get modules offered by D2U Helper addon.
     * @return \TobiasKrais\D2UHelper\Module[] Modules offered by D2U Helper addon
     */
    public static function getD2UMultiNewsletterModules()
    {
        $d2u_multinewsletter_modules = [];
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-1',
            'MultiNewsletter Anmeldung mit Name und Anrede',
            7);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-2',
            'MultiNewsletter Abmeldung',
            7);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-3',
            'MultiNewsletter Anmeldung nur mit Mail',
            7);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-4',
            'MultiNewsletter YForm Anmeldung',
            9);
        $d2u_multinewsletter_modules[] = new \TobiasKrais\D2UHelper\Module('80-5',
            'MultiNewsletter YForm Abmeldung',
            4);
        return $d2u_multinewsletter_modules;
    }
}
