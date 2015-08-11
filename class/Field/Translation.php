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
 * Une traduction du titre original du document.
 *
 * @property String $language
 * @property String $title
 */
class Translation extends MultiField {
    static protected $groupkey = 'language';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'language' => [
                    'label' => __('Langue', 'docalist-biblio'),
                ],
                'title' => [
                    'label' => __('Titre traduit', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('translation')->text();
    }

    public function map(array & $document) {
        $document['translation'][] = $this->title();
    }

    protected static function initFormats() {
        self::registerFormat('t', 'Traduction', function(Translation $title) {
            return $title->title();
        });

        self::registerFormat('l : t', 'langue : Traduction', function(Translation $title, Translations $parent) {
            return $parent->lookup($title->language()) . ' : ' . $title->title();
            // espace insécable avant le ':'
        });

        self::registerFormat('l: t', 'langue : Traduction', function(Translation $title, Translations $parent) {
            return $parent->lookup($title->language()) . ': ' . $title->title();
        });

        self::registerFormat('t (l)', 'Traduction (langue)', function(Translation $title, Translations $parent) {
            $result = $title->title();
            isset($title->language) && $result .= ' (' . $parent->lookup($title->language()) . ')';
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

        // Retourne true si on a la langue mais pas le titre traduit
        return $this->filterEmptyProperty('title');
    }
}