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
use Docalist\Biblio\Type\Repeatable;

/**
 * Content.
 *
 * @property String $type
 * @property String $value
 */
class Content extends MultiField {
    static protected $groupkey = 'type';

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

    protected static function shortenText($text, $maxlen = 240, $ellipsis = '…') {
        if (strlen($text) > $maxlen) {
            // Tronque le texte
            $text = wp_html_excerpt($text, $maxlen, '');

            // Supprime le dernier mot (coupé) et la ponctuation de fin
            $text = preg_replace('~\W+\w*$~u', '', $text);

            // Ajoute l'ellipse
            $text .= $ellipsis;
        }

        return $text;
    }

    protected static function prepareText($content, Contents $parent) {
        if ($maxlen = $parent->schema()->maxlen()) {
            $maxlen && $content = self::shortenText($content, $maxlen);
        }

        if ($replace = $parent->schema()->newlines()) {
            $content = str_replace( ["\r\n", "\r", "\n"], $replace, $content);
        }

        return $content;
    }

    protected static function initFormats() {
        self::registerFormat('v', 'Contenu', function(Content $content, Contents $parent) {
            $text = $content->__get('value')->value();
            return self::prepareText($text, $parent);
        });

        self::registerFormat('t : v', 'Type : Contenu', function(Content $content, Contents $parent) {
            $text = self::callFormat('v', $content, $parent);
            return $parent->lookup($content->type()) . ' : ' . $text;
            // espace insécable avant le ':'
        });

        self::registerFormat('t: v', 'Type: Contenu', function(Content $content, Contents $parent) {
            $text = self::callFormat('v', $content, $parent);
            return $parent->lookup($content->type()) . ': ' . $text;
        });
    }
/*
    public function format(Repeatable $parent = null) {
        $content = parent::format($parent);
        if ($replace = $parent->schema()->newlines()) {
            $content = str_replace( ["\r\n", "\r", "\n"], $replace, $content);
        }
        return $content;
    }
*/
    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a que le type et pas de contenu
        return $this->filterEmptyProperty('value');
    }
}