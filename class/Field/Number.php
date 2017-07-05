<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\TypedNumber;

/**
 * Un numéro propre au document (DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...)
 *
 * Ce champ permet de cataloguer les numéros associés au document
 *
 * Chaque numéro comporte deux sous-champs :
 * - `type` : type de numéro,
 * - `value` : numéro.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de numéros qui peuvent être
 * catalogués ("table:numbers" par défaut).
 */
class Number extends TypedNumber
{
    public static function loadSchema()
    {
        return [
            'description' => __(
                'Numéros du document (DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...)',
                'docalist-biblio'
            ),
        ];
    }
}
