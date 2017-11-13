<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

/**
 * Convertisseur "Docalist".
 *
 * Ne fait rien, retourne les notices au format natif de docalist.
 */
class Docalist extends Converter
{
    public function getLabel()
    {
        return __('Format docalist', 'docalist-biblio');
    }

    public function getDescription()
    {
        return __('Notices au format natif de Docalist-Biblio.', 'docalist-biblio');
    }
}
