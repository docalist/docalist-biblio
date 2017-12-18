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
namespace Docalist\Biblio\Type;

use Docalist\Type\TypedText;
use Docalist\Type\TableEntry;
use Docalist\Biblio\Type\Relation;

/**
 * Une relation typée : un type composite associant un type provenant d'une table d'autorité à un champ de type
 * Relation.
 *
 * @property TableEntry $type   Type    Type de relation.
 * @property Relation   $value  Value   Post ID de la fiche liée.
 */
class TypedRelation extends TypedText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Relation', 'docalist-biblio'),
            'description' => __('Relation vers une autre fiche et type de relation.', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'table' => 'table:relations',
                    'description' => __('Type de relation', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Biblio\Type\Relation',
                    'label' => __('Fiche liée', 'docalist-biblio'),
                    'description' => __('Post ID de la fiche liée', 'docalist-biblio'),
                ],
            ],
        ];
    }
}
