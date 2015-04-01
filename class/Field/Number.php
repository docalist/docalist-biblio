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
 * @version     $Id$
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\MultiField;
use Docalist\Search\MappingBuilder;

/**
 * Un numéro propre au document (ISSN, ISBN, Volume, Fascicule...)
 *
 * @property String $type
 * @property String $value
 */
class Number extends MultiField {
    static protected $groupkey = 'type';

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

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('number')->string();
        $mapping->template('number.*')->idem('number')->copyTo('number');
    }

    public function map(array & $document) {
        $document['number.' . $this->type()][] = $this->__get('value')->value();
    }

    protected static function initFormats() {
        self::registerFormat('format', "Format indiqué dans la table d'autorité", function(Number $number, Numbers $parent) {
            $format = $parent->lookup($number->type(), false, 'code', 'format');
            return trim(sprintf($format, $number->__get('value')->value()));
        });

        self::registerFormat('label', "Libellé indiqué dans la table suivi du numéro", function(Number $number, Numbers $parent) {
            $label = $parent->lookup($number->type());
            return trim($label . ' ' . $number->__get('value')->value());
        });

        self::registerFormat('v', 'Numéro uniquement, sans aucune mention', function(Number $number) {
            return $number->__get('value')->value();
        });

        self::registerFormat('v (t)', 'Numéro suivi du type entre parenthèses', function(Number $number, Numbers $parent) {
            $result = $number->__get('value')->value();
            if (isset($number->type)) {
                $result && $result .= ' '; // espace insécable avant '('
                $result .= '(' . $parent->lookup($number->type()) . ')';
            }

            return $result;
        });

        // TODO : return Number exemple ou array(Number, Number...)
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de valeur
        return $this->filterEmptyProperty('value');
    }
}