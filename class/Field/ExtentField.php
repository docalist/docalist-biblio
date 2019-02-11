<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Field;

use Docalist\Type\TypedNumber;

/**
 * Champ "extent" : étendue du document catalogué (durée, nombre de pages, poids...)
 *
 * Ce champ répétable permet de préciser la taille, les dimensions ou la pagination du document catalogué.
 *
 * Chaque occurence du champ extent comporte deux sous-champs :
 * - `type` : type d'étendue,
 * - `value` : valeur.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les types d'étendues autorisées
 * ("table:extent" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ExtentField extends TypedNumber
{
    /*
     * Remarque : ce champ est très similaire au champ standard "figure", mais la table d'autorité associée
     * peut contenir autre chose que des nombres (non paginé, pagination en chiffre romains, etc.)
     * Du coup, le sous-champ "value" est de type "number" et non pas de type "decimal".
     */

    public static function loadSchema()
    {
        return [
            'name' => 'extent',
            'repeatable' => true,
            'label' => __('Etendue', 'docalist-biblio'),
            'description' => __('Pagination, nombre de pages, durée, dimensions...', 'docalist-biblio' ),
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
