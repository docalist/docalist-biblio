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
 */
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference;

/**
 * Classe de base pour les convertisseurs.
 *
 * Un convertisseur se charge de transformer une Reference Docalist dans
 * un autre format.
 */
class Converter extends BaseExport
{
    /**
     * Convertit une notice docalist.
     *
     * @param Reference $ref La notice à convertir.
     *
     * @return array Un tableau contenant les données à exporter.
     */
    public function convert(Reference $reference)
    {
        return $reference->getPhpValue();
    }
}
