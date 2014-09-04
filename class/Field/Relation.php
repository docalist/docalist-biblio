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
use Docalist\Schema\Field;

/**
 * Relation
 *
 * @property String $type
 * @property Integer[] $ref
 */
class Relation extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de relation', 'docalist-biblio'),
                ],
                'ref' => [
                    'type' => 'int*',
                    'label' => __('Notices liées', 'docalist-biblio'),
                    'description' => __('Numéro de référence des notices (Ref)', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['relation.' . $this->type()][] = $this->ref();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['dynamic_templates'][] = [
            'relation.*' => [
                'path_match' => 'relation.*',
                'mapping' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'copy_to' => 'relation',
                ]
            ]
        ];

        $mappings['properties']['number'] = [
            'type' => 'string',
            'index' => 'not_analyzed',
        ];
    }
}