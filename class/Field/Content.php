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
 * Content.
 *
 * @property String $type
 * @property String $value
 */
class Content extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
    //                 'description' => __('Nature de la note', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Contenu', 'docalist-biblio'),
                    'description' => __('Résumé, notes et remarques sur le contenu.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function map(array & $doc) {
        $doc['content'][] = $this->__get('value')->value();
        // if (type === private) $doc['content.private'] = value;
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['properties']['content'] = self::stdIndex(true);
        // $mappings['properties']['content.private'] = self::stdIndex(true);
    }
}