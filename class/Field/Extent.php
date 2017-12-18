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

use Docalist\Type\TypedNumber;

/**
 * Étendue du document catalogué : pagination, nombre de pages, durée, dimensions...
 *
 * Le champ comporte deux sous-champs :
 * - `type` : type d'étendue,
 * - `value` : valeur.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types d'étendues autorisées
 * ("table:extent" par défaut).
 */
class Extent extends TypedNumber
{
    public static function loadSchema()
    {
        return [
            'label' => __('Etendue', 'docalist-biblio'),
            'description' => __(
                'Pagination, nombre de pages, durée, dimensions...',
                'docalist-biblio'
            ),
            'fields' => [
                'type' => [
                    'table' => 'table:extent',
                    'description' => __("Type d'étendue", 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Valeur', 'docalist-biblio'),
                    'description' => __('Etendue dans le format indiqué par le type', 'docalist-biblio'),
                ]
            ]
        ];
    }
}
