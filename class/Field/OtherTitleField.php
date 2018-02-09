<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Type\Text;

/**
 * Champ "othertitle" : autres titres du document.
 *
 * Ce champ répétable permet d'indiquer d'autres titres associés au document catalogué mais différents du titre
 * exact indiqué dans le champ title) : un complément de titre, un sous-titre, le titre de la série, un sigle ou
 * un titre abrégé, l'ancien titre du document, etc.
 *
 * Chaque occurence du champ othertitle comporte deux sous-champs :
 * - `type` : type de titre,
 * - `value` : titre.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les valeurs possibles ("table:titles" par défaut).
 *
 * @property TableEntry $type   Type de titre.
 * @property Text       $value  Autre titre.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class OtherTitleField extends TypedText
{
    public static function loadSchema()
    {
        return [
            'name' => 'othertitle',
            'repeatable' => true,
            'label' => __('Autre titre', 'docalist-biblio'),
            'description' => __('Autre titre du document : titre du dossier, ancien titre...', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'table' => 'table:titles',
                    'label' => __('Type de titre', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Autre titre', 'docalist-biblio'),
                ]
            ]
        ];
    }
}
