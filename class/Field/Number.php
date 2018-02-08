<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Data\Field\NumberField as BaseNumberField;

/**
 * Champ "number" : numéros du document.
 *
 * Ce champ répétable permet de cataloguer les numéros (officiels ou non) associés au document :
 * DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...
 *
 * Chaque numéro comporte deux sous-champs :
 * - `type` : type de numéro,
 * - `value` : numéro.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de numéros qui peuvent être
 * catalogués ("table:numbers" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Number extends BaseNumberField
{
    public static function loadSchema()
    {
        return [
            'description' => __(
                'Numéros du document (DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...)',
                'docalist-biblio'
            ),
            'fields' => [
                'type' => [
                    'table' => 'table:numbers',
                ],
            ]
        ];
    }
}
