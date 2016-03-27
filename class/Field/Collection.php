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
namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;

/**
 * Collection et numéro au sein de la collection.
 *
 * @property Docalist\Type\Text $name Name of the collection.
 * @property Docalist\Type\Text $number Number in the collection.
 */
class Collection extends Composite {
    static public function loadSchema() {
        // @formatter:off
        return [
            'editor' => 'table',
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __("Nom", 'docalist-biblio'),
                    'description' => __('Nom de la collection ou de la sous-collection.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __('Numéro au sein de la collection ou de la sous-collection.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('collection')->text();
    }

    public function mapData(array & $document) {
        $document['collection'][] = $this->name();
    }
*/
    public function format() {
        $h = $this->name();
        isset($this->number) && $h .= ' (' . $this->number() . ')';

        return $h;
    }
}