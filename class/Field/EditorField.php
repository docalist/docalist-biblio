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

use Docalist\Biblio\Field\CorporationField;

/**
 * Champ "editor" : organismes éditeur.
 *
 * Ce champ répétable permet de saisir les organismes en charge de l'édition, de la diffusion et de la distribution
 * du document
 *
 * On peut également indiquer pour chaque organisme une étiquette qui précise son rôle (éditeur, diffuseur...)
 *
 * Chaque occurence du champ editor comporte cinq sous-champs :
 * - `name` : nom de l'organisme,
 * - `acronym` : sigle ou acronym éventuel,
 * - `city` : ville,
 * - `country` : pays,
 * - `role` : étiquette de rôle éventuelle.
 *
 * Le sous-champ `country` est associé à une table d'autorité qui contient les codes pays disponibles
 * (par défaut, il s'agit de la table des codes ISO à trois lettres).
 *
 * Le sous-champ `role` est associé à une table d'autorité qui contient les étiquettes de rôles disponibles
 * (par défaut, il s'agit de la table "marc21 relators").
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class EditorField extends CorporationField
{
    public static function loadSchema()
    {
        return [
            'name' => 'editor',
            // repeatable : oui, déjà dans dit dans Corporation
            'label' => __('Editeurs', 'docalist-biblio'),
            'description' => __(
                "Organismes délégués par l'auteur pour assurer la diffusion et la distribution du document.",
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'description' => __("Nom de l'éditeur", 'docalist-biblio'),
                ],
            ]
        ];
    }
}
