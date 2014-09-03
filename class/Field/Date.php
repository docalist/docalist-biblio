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
 * Date.
 *
 * @property String $type
 * @property String $value
 */
class Date extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type de date', 'docalist-biblio'),
    //                 'description' => __('Date', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Date', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['date.' . $this->type()][] = $this->__get('value')->value();
    }

    public static function ESmapping(array & $mappings) {
        $mappings['dynamic_templates'][] = [
            'date.*' => [
                'path_match' => 'date.*',
                'mapping' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd||yyyy-MM||yyyyMMdd||yyyyMM||yyyy',
                    'ignore_malformed' => true,
                    'copy_to' => 'date',
                ],
            ]
        ];

        $mappings['properties']['date'] = [
            'type' => 'date',
            'format' => 'yyyy-MM-dd||yyyy-MM||yyyyMMdd||yyyyMM||yyyy',
            'ignore_malformed' => true
        ];
    }
}