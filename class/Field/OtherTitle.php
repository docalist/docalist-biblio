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

use Docalist\Biblio\Type\MultiField;
use Docalist\Search\MappingBuilder;

/**
 * Autre titre.
 *
 * @property String $type
 * @property String $value
 *
 */
class OtherTitle extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type de titre', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Autre titre', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('othertitle')->text();
    }

    public function map(array & $document) {
        $document['othertitle'][] = $this->__get('value')->value();
    }

    protected static function initFormats() {
        self::registerFormat('v', 'Titre', function(OtherTitle $title) {
            return $title->__get('value')->value();
        });

        self::registerFormat('t : v', 'Type : Titre', function(OtherTitle $title, OtherTitles $parent) {
            return $parent->lookup($title->type()) . ' : ' . $title->__get('value')->value();
            // espace insécable avant le ':'
        });

        self::registerFormat('t: v', 'Type: Titre', function(OtherTitle $title, OtherTitles $parent) {
            return $parent->lookup($title->type()) . ': ' . $title->__get('value')->value();
        });

        self::registerFormat('v (t)', 'Titre (Type)', function(OtherTitle $title, OtherTitles $parent) {
            $result = $title->__get('value')->value();
            isset($title->type) && $result .= ' (' . $parent->lookup($title->type()) . ')';
            // espace insécable avant '('

            return $result;
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on a que le type de titre et pas de valeur
        return $this->filterEmptyProperty('value');
    }
}