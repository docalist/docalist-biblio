<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

class TypedRelation extends TypedText
{
    static public function loadSchema() {
        return [
            'label' => __('Relation', 'docalist-core'),
            'description' => __('Relation vers une autre fiche et type de relation.', 'docalist-core'),
            'editor' => 'table',
            'fields' => [
                'value' => [
                    'type' => 'Docalist\Type\Relation',
                    'label' => __('Fiche', 'docalist-core'),
                ],
            ],
        ];
    }
}
