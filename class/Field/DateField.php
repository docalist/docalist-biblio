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

use Docalist\Data\Field\DateField as BaseDateField;

/**
 * Dates du document.
 *
 * Ce champ permet d'indiquer les dates associées au document catalogué (date de publication, date
 * d'enregistrement...)
 *
 * Chaque date comporte deux sous-champs :
 * - `type` : type de date,
 * - `value` : date.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types de dates ("table:dates" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class DateField extends BaseDateField
{
    public static function loadSchema()
    {
        return [
            'description' => __(
                "Dates associées au document catalogué : date de publication, date d'enregistrement...",
                'docalist-biblio'
            ),
            'fields' => [
                'type' => [
                    'table' => 'table:dates',
                ],
            ],
        ];
    }
}
