<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Object;

/**
 * Un numéro propre au document (ISSN, ISBN, Volume, Fascicule...)
 *
 * @property String $type
 * @property String $value
 */
class Number extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de numéro', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __('Numéro dans le format indiqué par le type.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['number.' . $this->type()][] = $this->__get('value')->value();
    }

    public static function ESmapping(array & $mappings) {
        $mappings['dynamic_templates'][] = [
            'number.*' => [
                'path_match' => 'number.*',
                'mapping' => self::stdIndex(false, 'text') + [
                    'copy_to' => 'number',
                ]
            ]
        ];

        $mappings['properties']['number'] = self::stdIndex(false, 'text');
    }
}