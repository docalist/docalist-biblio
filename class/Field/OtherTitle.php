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

use Docalist\Biblio\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Type\Text;

/**
 * Autre titre du document.
 *
 * Ce champ permet de cataloguer d'autres titres associés au document (différents du titre exact catalogué dans le
 * champ title) : un complément de titre, un sous-titre, le titre de la série, un sigle ou un titre abrégé, l'ancien
 * titre, etc.
 *
 * Chaque occurence comporte deux sous-champs :
 * - `type` : type de titre,
 * - `value` : titre.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les valeurs possibles ("table:titles" par défaut).
 *
 * @property TableEntry $type   Type de titre.
 * @property Text       $value  Autre titre.
 */
class OtherTitle extends TypedText
{
    public static function loadSchema()
    {
        return [
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
