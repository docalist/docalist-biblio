<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Export;

use Docalist\Forms\Fragment;
use Docalist\Biblio\Reference;

/**
 * Classe de base pour les convertisseurs.
 *
 * Un convertisseur se charge de transformer une Reference Docalist dans
 * un autre format.
 */
class Converter extends BaseExport {
    /**
     * Convertit une notice docalist.
     *
     * @param Reference $ref La notice à convertir.
     *
     * @return array Un tableau contenant les données à exporter.
     */
    public function convert(Reference $reference) {
        return $reference->value();
    }

    public static function label() {
        return get_called_class() === __CLASS__ ? 'docalist' : parent::label();
    }
}