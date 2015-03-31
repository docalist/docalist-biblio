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
 * Editeur
 *
 * @property String $name
 * @property String $city
 * @property String $country
 * @property String $role
 */
class Editor extends MultiField {
    static protected $groupkey = 'role';
    static protected $table2ForGroupkey = true;

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'name' => [
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de l'éditeur", 'docalist-biblio'),
                ],
                'city' => [
                    'label' => __('Ville', 'docalist-biblio'),
                    'description' => __("Ville de l'éditeur", 'docalist-biblio'),
                ],
                'country' => [
                    'label' => __('Pays', 'docalist-biblio'),
                    'description' => __("Pays d'édition", 'docalist-biblio'),
                ],
                'role' => [
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function __toString() {
        $result = $this->name();

        if (isset($this->city) || isset($this->country)) {
            $result .= ' (';
            isset($this->city) && $result .= $this->city();
            if (isset($this->country)) {
                isset($this->city) && $result .= ', ';
                $result .= $this->country();
            }
            $result .= ')';
        }

        return $result;
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('editor')->text()->filter()->suggest(); // stemming sur les noms d'organismes
    }

    public function map(array & $document) {
        $document['editor'][] = $this->name() . '¤' . $this->city() . '¤' . $this->country();
    }

    protected static function initFormats() {
        self::registerFormat('n, t, c, r', "Nom de l'éditeur, ville, pays, rôle", function(Editor $ed, Editors $parent) {
            $h = $ed->name();

            if (isset($ed->city)) {
                $h && $h .= ', ';
                $h .= $ed->city();
            }

            if (isset($ed->country)) {
                $h && $h .= ', ';
                //$h .= $ed->country();
                $h .= $parent->lookup($ed->country()); // table1
            }

            if (isset($ed->role)) {
                $h && $h .= ' / '; // espaces insécables
                $h .= $parent->lookup($ed->role(), true); // table2
            }
            return $h;
        });
        self::registerFormat('name', 'Nom uniquement', function(Editor $ed) {
            return $ed->name();
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de nom
        return $this->filterEmptyProperty('name');
    }
}