<?php
/**
 * This file is part of the 'Docalist Biblio Export' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id: Converter.php 1916 2015-01-05 10:25:19Z daniel.menard.35@gmail.com $
 */
namespace Docalist\Biblio\Export;

use Docalist\Forms\Fragment;
use Docalist\Biblio\Reference;

/**
 * Convertisseur "Docalist"
 *
 * Ne fait rien, retourne les notices au format natif de docalist.
 */
class Docalist extends Converter {
    public function label() {
        return __('Format docalist', 'docalist-biblio-export');
    }

    public function description() {
        return __('Notices au format natif de Docalist-Biblio.', 'docalist-biblio-export');
    }
}