<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Data\Field\LinkField as BaseLinkField;

/**
 * Champ standard "link" : un lien internet (url, uri, e-mail, hashtag...)
 *
 * Ce champ permet de saisir les liens d'une entité..
 *
 * Chaque lien comporte quatre sous-champs :
 * - `type` : type de lien
 * - `url` : uri,
 * - `label` : libellé à afficher pour ce lien.
 * - `date` : date à laquelle le lien a été consulté/vérifié.
 *
 * Le sous-champ type est associé à une table d'autorité qui contient les types de liens disponibles
 * ('table:links'par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LinkField extends BaseLinkField
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'type' => [
                    'table' => 'table:links',
                ],
            ]
        ];
    }

}
