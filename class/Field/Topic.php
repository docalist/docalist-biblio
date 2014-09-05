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

use Docalist\Biblio\Type\MultiField;
use Docalist\Schema\Field;

/**
 * Une liste de mots-clés d'un certain type.
 *
 * @property String $type
 * @property String[] $terms
 */
class Topic extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
    //                 'description' => __('Type des mots-clés (nom du thesaurus ou de la liste)', 'docalist-biblio'),
                ],
                'term' => [ // @todo : au pluriel ?
                    'repeatable' => true,
                    'label' => __('Termes', 'docalist-biblio'),
    //                 'description' => __('Liste des mots-clés.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function __toString() {
        return $this->type() . ' : ' . implode(', ', $this->term());
    }

    public function map(array & $doc) {
        $doc['topic.' . $this->type()][] = $this->__get('term')->value();
    }

    public static function ESmapping(array & $mappings, Field $schema) {
        $mappings['dynamic_templates'][] = [
            'topic.*' => [
                'path_match' => 'topic.*',
                'mapping' => self::stdIndexFilterAndSuggest(true) + [
                    'copy_to' => 'topic',
                ]
            ]
        ];

        $mappings['properties']['topic'] = self::stdIndexFilterAndSuggest(true);
    }


    protected static function initFormats() {
        self::registerFormat('v', 'Mots-clés', function(Topic $topic) {
            return implode(', ', $topic->term());
        });

        self::registerFormat('t : v', 'Nom du vocabulaire : Mots-clés', function(Topic $topic, Topics $parent) {
            return $parent->lookup($topic->type()) . ' : ' . implode(', ', $topic->term());
            // espace insécable avant le ':'
        });

        self::registerFormat('t: v', 'Nom du vocabulaire: Mots-clés', function(Topic $topic, Topics $parent) {
            return $parent->lookup($topic->type()) . ': ' . implode(', ', $topic->term());
        });

        self::registerFormat('v (t)', 'Mots-clés (Nom du vocabulaire)', function(Topic $topic, Topics $parent) {
            $result = implode(', ', $topic->term());
            isset($topic->type) && $result .= ' (' . $parent->lookup($topic->type()) . ')';
            // espace insécable avant '('

            return $result;
        });
    }
}